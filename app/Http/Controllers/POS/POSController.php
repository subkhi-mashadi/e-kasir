<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\ModifierOption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Table;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function index()
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $branch   = Branch::find($branchId);
        $tables   = Table::where('branch_id', $branchId)->where('is_active', true)->orderBy('name')->get(['id', 'name', 'capacity']);
        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']);

        return view('pos.index', compact('branch', 'tables', 'customers'));
    }

    public function products()
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $products = Product::with([
            'category:id,name,icon',
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            'modifierGroups.options' => fn ($q) => $q->where('is_active', true),
            'inventories' => fn ($q) => $q->where('branch_id', $branchId),
        ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                $inv = $p->inventories->first();
                return [
                    'id'             => $p->id,
                    'name'           => $p->name,
                    'price'          => (float) $p->price,
                    'tax_rate'       => (float) $p->tax_rate,
                    'image_url'      => $p->image_url,
                    'category_id'    => $p->category_id,
                    'category_name'  => $p->category?->name ?? '—',
                    'category_icon'  => $p->category?->icon ?? '',
                    'track_stock'    => (bool) $p->track_stock,
                    'stock'          => $inv ? (float) $inv->qty : null,
                    'is_available'   => $inv ? (bool) $inv->is_available : true,
                    'variants'       => $p->variants->map(fn ($v) => [
                        'id'               => $v->id,
                        'name'             => $v->name,
                        'price_adjustment' => (float) $v->price_adjustment,
                    ])->values(),
                    'modifier_groups' => $p->modifierGroups->map(fn ($g) => [
                        'id'          => $g->id,
                        'name'        => $g->name,
                        'is_required' => (bool) $g->is_required,
                        'is_multiple' => (bool) $g->is_multiple,
                        'options'     => $g->options->map(fn ($o) => [
                            'id'    => $o->id,
                            'name'  => $o->name,
                            'price' => (float) $o->price,
                        ])->values(),
                    ])->values(),
                ];
            });

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'name', 'icon', 'color']);

        return response()->json(compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'               => 'required|in:dine_in,takeaway',
            'table_id'           => 'nullable|exists:tables,id',
            'customer_id'        => 'nullable|exists:customers,id',
            'customer_name'      => 'nullable|string|max:100',
            'notes'              => 'nullable|string|max:500',
            'discount_amount'    => 'nullable|numeric|min:0',
            'paid_amount'        => 'required|numeric|min:0',
            'payment_method'     => 'required|string|in:cash,qris,transfer,card,credit',
            'payment_reference'  => 'nullable|string|max:100',
            'items'              => 'required|array|min:1',
            'items.*.product_id'           => 'required|exists:products,id',
            'items.*.product_variant_id'   => 'nullable|exists:product_variants,id',
            'items.*.qty'                  => 'required|integer|min:1',
            'items.*.notes'                => 'nullable|string|max:255',
            'items.*.modifiers'            => 'nullable|array',
            'items.*.modifiers.*.modifier_option_id' => 'required|exists:modifier_options,id',
        ]);

        $branchId    = session('branch_id') ?? auth()->user()->branch_id;
        $companyTax  = (float) (auth()->user()->company?->tax_rate ?? 0);
        $orderId     = null;

        DB::transaction(function () use ($data, $branchId, $companyTax, &$orderId) {
            $subtotal    = 0;
            $taxTotal    = 0;
            $itemsToSave = [];

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $variant = isset($item['product_variant_id']) && $item['product_variant_id']
                    ? ProductVariant::find($item['product_variant_id'])
                    : null;

                $basePrice   = (float) $product->price + (float) ($variant?->price_adjustment ?? 0);
                $modTotal    = 0;
                $modsToSave  = [];

                foreach ($item['modifiers'] ?? [] as $mod) {
                    $option = ModifierOption::with('group')->find($mod['modifier_option_id']);
                    if ($option) {
                        $modTotal += (float) $option->price;
                        $modsToSave[] = [
                            'modifier_option_id' => $option->id,
                            'modifier_name'      => $option->group->name,
                            'option_name'        => $option->name,
                            'price'              => (float) $option->price,
                        ];
                    }
                }

                // Use product tax_rate; fallback to company tax_rate if product has none
                $effectiveTax = (float) $product->tax_rate > 0
                    ? (float) $product->tax_rate
                    : $companyTax;

                $unitPrice  = $basePrice + $modTotal;
                $lineTotal  = $unitPrice * (int) $item['qty'];
                $lineTax    = $lineTotal * ($effectiveTax / 100);

                $subtotal += $lineTotal;
                $taxTotal += $lineTax;

                $itemsToSave[] = [
                    'product'    => $product,
                    'variant'    => $variant,
                    'unitPrice'  => $unitPrice,
                    'qty'        => (int) $item['qty'],
                    'lineTotal'  => $lineTotal,
                    'lineTax'    => $lineTax,
                    'notes'      => $item['notes'] ?? null,
                    'mods'       => $modsToSave,
                ];
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $total          = max(0, $subtotal + $taxTotal - $discountAmount);
            $paidAmount     = (float) $data['paid_amount'];
            $change         = max(0, $paidAmount - $total);

            // Invoice number: INV/YYYYMMDD/BBBB/NNNN
            $today     = now()->format('Ymd');
            $seq       = Order::whereDate('created_at', today())->where('branch_id', $branchId)->whereNotNull('invoice_no')->lockForUpdate()->count() + 1;
            $invoiceNo = 'INV/' . $today . '/' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'branch_id'       => $branchId,
                'user_id'         => auth()->id(),
                'customer_id'     => $data['customer_id'] ?? null,
                'customer_name'   => $data['customer_name'] ?? null,
                'table_id'        => $data['table_id'] ?? null,
                'invoice_no'      => $invoiceNo,
                'type'            => $data['type'],
                'source'          => 'pos',
                'status'          => 'paid',
                'kitchen_status'  => 'pending',
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount'      => $taxTotal,
                'total'           => $total,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $change,
                'notes'           => $data['notes'] ?? null,
                'synced_at'       => now(),
            ]);

            foreach ($itemsToSave as $item) {
                $orderItem = $order->items()->create([
                    'product_id'         => $item['product']->id,
                    'product_variant_id' => $item['variant']?->id,
                    'product_name'       => $item['product']->name,
                    'variant_name'       => $item['variant']?->name,
                    'unit_price'         => $item['unitPrice'],
                    'qty'                => $item['qty'],
                    'tax_amount'         => $item['lineTax'],
                    'subtotal'           => $item['lineTotal'],
                    'notes'              => $item['notes'],
                ]);

                foreach ($item['mods'] as $mod) {
                    $orderItem->modifiers()->create($mod);
                }

                if ($item['product']->track_stock) {
                    $inv = Inventory::where('product_id', $item['product']->id)
                        ->where('branch_id', $branchId)
                        ->lockForUpdate()
                        ->first();

                    if ($inv) {
                        $qtyBefore = (float) $inv->qty;
                        $qtyAfter  = max(0, $qtyBefore - $item['qty']);
                        $inv->update(['qty' => $qtyAfter]);

                        InventoryLog::create([
                            'product_id' => $item['product']->id,
                            'branch_id'  => $branchId,
                            'user_id'    => auth()->id(),
                            'type'       => 'sale',
                            'qty_before' => $qtyBefore,
                            'qty_change' => -$item['qty'],
                            'qty_after'  => $qtyAfter,
                            'notes'      => 'Order ' . $order->invoice_no,
                        ]);
                    }
                }
            }

            $order->payments()->create([
                'method'    => $data['payment_method'],
                'amount'    => $paidAmount,
                'reference' => $data['payment_reference'] ?? null,
            ]);

            $orderId = $order->id;
        });

        $order = Order::find($orderId);
        return response()->json([
            'order_id'   => $orderId,
            'invoice_no' => $order?->invoice_no,
        ]);
    }

    public function receipt(Order $order)
    {
        $order->load(['items.modifiers', 'payments', 'table', 'user', 'customer', 'branch']);
        return view('pos.receipt', compact('order'));
    }

    public function orders(Request $request)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        $orders = Order::with(['items', 'payments', 'table', 'user'])
            ->where('branch_id', $branchId)
            ->latest()
            ->paginate(30);

        return view('pos.orders', compact('orders'));
    }

    public function incomingOrders()
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $orders = Order::with(['items.modifiers', 'table'])
            ->where('branch_id', $branchId)
            ->where('source', 'qr')
            ->where('status', 'open')
            ->where(function ($q) {
                $q->where('preferred_payment', 'cash')
                  ->orWhere(function ($q2) {
                      $q2->where('preferred_payment', 'qris')
                         ->whereNotNull('payment_proof');
                  });
            })
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'                => $o->id,
                'table_name'        => $o->table?->name ?? '—',
                'customer_name'     => $o->customer_name,
                'preferred_payment' => $o->preferred_payment,
                'total'             => (float) $o->total,
                'notes'             => $o->notes,
                'created_at'        => $o->created_at->format('H:i'),
                'payment_proof_url' => $o->payment_proof ? asset('storage/' . $o->payment_proof) : null,
                'items'         => $o->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'variant_name' => $i->variant_name,
                    'qty'          => $i->qty,
                    'notes'        => $i->notes,
                    'modifiers'    => $i->modifiers->pluck('option_name')->join(', '),
                ])->values(),
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function acceptOrder(Request $request, Order $order)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        abort_if($order->branch_id !== (int) $branchId, 403);
        abort_if($order->status !== 'open', 422, 'Pesanan sudah diproses.');

        $data = $request->validate([
            'payment_method' => 'required|in:cash,qris,transfer,card,credit',
            'paid_amount'    => 'required|numeric|min:0',
            'reference'      => 'nullable|string|max:100',
        ]);

        $paidAmount = (float) $data['paid_amount'];
        $change     = max(0, $paidAmount - (float) $order->total);

        DB::transaction(function () use ($order, $data, $paidAmount, $change) {
            $order->update([
                'status'         => 'paid',
                'kitchen_status' => 'pending',
                'paid_amount'    => $paidAmount,
                'change_amount'  => $change,
                'user_id'        => auth()->id(),
                'invoice_no'     => $this->generateInvoice($order->branch_id),
                'synced_at'      => now(),
            ]);

            $order->payments()->create([
                'method'    => $data['payment_method'],
                'amount'    => $paidAmount,
                'reference' => $data['reference'] ?? null,
            ]);
        });

        return response()->json(['ok' => true]);
    }

    private function generateInvoice(int $branchId): string
    {
        $today = now()->format('Ymd');
        $seq   = Order::whereDate('created_at', today())->where('branch_id', $branchId)->whereNotNull('invoice_no')->lockForUpdate()->count() + 1;
        return 'INV/' . $today . '/' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function readyOrders()
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $orders = Order::with(['table'])
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->where('kitchen_status', 'ready')
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'            => $o->id,
                'invoice_no'    => $o->invoice_no,
                'table_name'    => $o->table?->name ?? '—',
                'customer_name' => $o->customer_name,
                'created_at'    => $o->created_at->format('H:i'),
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function rejectOrder(Order $order)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        abort_if($order->branch_id !== (int) $branchId, 403);
        abort_if($order->status !== 'open', 422, 'Pesanan sudah diproses.');

        $order->update(['status' => 'cancelled']);

        return response()->json(['ok' => true]);
    }

    public function validateVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string', 'subtotal' => 'required|numeric|min:0']);

        $voucher = Voucher::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->first();

        if (! $voucher) {
            return response()->json(['valid' => false, 'message' => 'Voucher tidak valid atau sudah habis.'], 422);
        }

        if ((float) $request->subtotal < (float) $voucher->min_order) {
            return response()->json(['valid' => false, 'message' => 'Minimum order Rp ' . number_format($voucher->min_order, 0, ',', '.')], 422);
        }

        $discount = $voucher->type === 'percentage'
            ? (float) $request->subtotal * ((float) $voucher->value / 100)
            : (float) $voucher->value;

        if ($voucher->max_discount) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        return response()->json([
            'valid'    => true,
            'discount' => $discount,
            'label'    => $voucher->name ?? $voucher->code,
        ]);
    }
}
