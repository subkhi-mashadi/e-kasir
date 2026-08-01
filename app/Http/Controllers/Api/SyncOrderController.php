<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemModifier;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ModifierOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncOrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'uuid'               => 'required|string|max:36',
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
            'items.*.product_id'                     => 'required|exists:products,id',
            'items.*.product_variant_id'             => 'nullable|exists:product_variants,id',
            'items.*.qty'                            => 'required|integer|min:1',
            'items.*.notes'                          => 'nullable|string|max:255',
            'items.*.modifiers'                      => 'nullable|array',
            'items.*.modifiers.*.modifier_option_id' => 'required|exists:modifier_options,id',
        ]);

        // Idempotent: return existing if UUID already saved
        $existing = Order::where('sync_uuid', $data['uuid'])->first();
        if ($existing) {
            return response()->json([
                'order_id'   => $existing->id,
                'invoice_no' => $existing->invoice_no,
                'duplicate'  => true,
            ]);
        }

        $branchId = auth()->user()->activeBranchId();
        $orderId  = null;

        DB::transaction(function () use ($data, $branchId, &$orderId) {
            $subtotal    = 0;
            $taxTotal    = 0;
            $itemsToSave = [];

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                $variant = isset($item['product_variant_id']) && $item['product_variant_id']
                    ? ProductVariant::find($item['product_variant_id'])
                    : null;

                $basePrice  = (float) $product->price + (float) ($variant?->price_adjustment ?? 0);
                $modTotal   = 0;
                $modsToSave = [];

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

                $unitPrice = $basePrice + $modTotal;
                $lineTotal = $unitPrice * (int) $item['qty'];
                $lineTax   = $lineTotal * ((float) $product->tax_rate / 100);

                $subtotal += $lineTotal;
                $taxTotal += $lineTax;

                $itemsToSave[] = [
                    'product'     => $product,
                    'variant'     => $variant,
                    'qty'         => (int) $item['qty'],
                    'unit_price'  => $unitPrice,
                    'subtotal'    => $lineTotal,
                    'tax_amount'  => $lineTax,
                    'notes'       => $item['notes'] ?? null,
                    'mods'        => $modsToSave,
                ];
            }

            $discount     = (float) ($data['discount_amount'] ?? 0);
            $total        = $subtotal + $taxTotal - $discount;
            $paidAmount   = (float) $data['paid_amount'];
            $changeAmount = max(0, $paidAmount - $total);

            $today     = now()->format('Ymd');
            $seq       = Order::whereDate('created_at', today())->where('branch_id', $branchId)->lockForUpdate()->count() + 1;
            $invoiceNo = 'INV/' . $today . '/' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'branch_id'        => $branchId,
                'user_id'          => auth()->id(),
                'table_id'         => $data['table_id'] ?? null,
                'customer_id'      => $data['customer_id'] ?? null,
                'customer_name'    => $data['customer_name'] ?? null,
                'type'             => $data['type'],
                'source'           => 'pos',
                'status'           => 'paid',
                'kitchen_status'   => 'pending',
                'invoice_no'       => $invoiceNo,
                'subtotal'         => $subtotal,
                'tax_amount'       => $taxTotal,
                'discount_amount'  => $discount,
                'total'            => $total,
                'paid_amount'      => $paidAmount,
                'change_amount'    => $changeAmount,
                'notes'            => $data['notes'] ?? null,
                'sync_uuid'        => $data['uuid'],
            ]);

            foreach ($itemsToSave as $i) {
                $orderItem = $order->items()->create([
                    'product_id'         => $i['product']->id,
                    'product_variant_id' => $i['variant']?->id,
                    'product_name'       => $i['product']->name,
                    'variant_name'       => $i['variant']?->name,
                    'qty'                => $i['qty'],
                    'unit_price'         => $i['unit_price'],
                    'subtotal'           => $i['subtotal'],
                    'tax_amount'         => $i['tax_amount'],
                    'notes'              => $i['notes'],
                ]);

                foreach ($i['mods'] as $mod) {
                    $orderItem->modifiers()->create($mod);
                }
            }

            $order->payments()->create([
                'method'     => $data['payment_method'],
                'amount'     => $paidAmount,
                'reference'  => $data['payment_reference'] ?? null,
            ]);

            $orderId = $order->id;
        });

        $order = Order::find($orderId);

        return response()->json([
            'order_id'   => $orderId,
            'invoice_no' => $order?->invoice_no,
        ], 201);
    }
}
