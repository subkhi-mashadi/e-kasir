<?php

namespace App\Http\Controllers\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->activeBranchId();
        $branch   = Branch::find($branchId);

        return view('kitchen.display', compact('branch'));
    }

    public function orders()
    {
        $branchId = auth()->user()->activeBranchId();

        $orders = Order::with(['items.modifiers', 'table'])
            ->where('branch_id', $branchId)
            ->where('status', 'paid')
            ->whereIn('kitchen_status', ['pending', 'preparing', 'ready'])
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id'             => $o->id,
                'invoice_no'     => $o->invoice_no,
                'table_name'     => $o->table?->name ?? '—',
                'customer_name'  => $o->customer_name,
                'kitchen_status' => $o->kitchen_status,
                'source'         => $o->source,
                'notes'          => $o->notes,
                'created_at'     => $o->created_at->format('H:i'),
                'items'          => $o->items->map(fn ($i) => [
                    'product_name' => $i->product_name,
                    'variant_name' => $i->variant_name,
                    'qty'          => $i->qty,
                    'notes'        => $i->notes,
                    'modifiers'    => $i->modifiers->pluck('option_name')->join(', '),
                ])->values(),
            ]);

        return response()->json(['orders' => $orders]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $branchId = auth()->user()->activeBranchId();

        abort_if($order->branch_id !== (int) $branchId, 403);
        abort_if($order->status !== 'paid', 422, 'Pesanan belum dibayar.');

        $data = $request->validate([
            'kitchen_status' => 'required|in:preparing,ready,delivered',
        ]);

        $order->update(['kitchen_status' => $data['kitchen_status']]);

        return response()->json(['ok' => true, 'kitchen_status' => $order->kitchen_status]);
    }
}
