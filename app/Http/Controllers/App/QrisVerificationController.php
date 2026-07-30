<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QrisVerificationController extends Controller
{
    public function index()
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;

        $orders = Order::with(['items', 'table'])
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->whereNotNull('payment_proof')
            ->latest()
            ->get();

        return view('app.qris-verification.index', compact('orders'));
    }

    public function approve(Order $order)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        abort_if($order->branch_id !== (int) $branchId, 403);
        abort_if($order->status !== 'open', 422);

        $today     = now()->format('Ymd');
        $seq       = Order::whereDate('created_at', today())
            ->where('branch_id', $branchId)
            ->whereNotNull('invoice_no')
            ->count() + 1;
        $invoiceNo = 'INV/' . $today . '/' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);

        $order->update([
            'status'          => 'paid',
            'kitchen_status'  => 'pending',
            'midtrans_status' => 'settlement',
            'paid_amount'     => $order->total,
            'invoice_no'      => $invoiceNo,
            'rejection_reason'=> null,
            'synced_at'       => now(),
        ]);

        $order->payments()->create([
            'method' => 'qris',
            'amount' => $order->total,
        ]);

        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, Order $order)
    {
        $branchId = session('branch_id') ?? auth()->user()->branch_id;
        abort_if($order->branch_id !== (int) $branchId, 403);
        abort_if($order->status !== 'open', 422);

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order->update([
            'rejection_reason' => $data['reason'],
            'midtrans_status'  => 'deny',
        ]);

        return response()->json(['ok' => true]);
    }
}
