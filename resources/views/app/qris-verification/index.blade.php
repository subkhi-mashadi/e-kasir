@extends('layouts.app')
@section('title', 'Verifikasi QRIS')
@section('page-title', 'Verifikasi Pembayaran QRIS')
@section('page-subtitle', 'Cek bukti transfer dan konfirmasi pesanan')

@section('content')
<div class="space-y-4" x-data="{ rejectModal: null, rejectReason: '' }">

    @if ($orders->isEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
        <p class="text-4xl mb-3">✅</p>
        <p class="font-semibold text-slate-700">Tidak ada pesanan yang menunggu verifikasi</p>
        <p class="text-sm text-slate-400 mt-1">Semua bukti transfer sudah diproses</p>
    </div>
    @endif

    @foreach ($orders as $order)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" id="order-{{ $order->id }}">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <span class="font-semibold text-slate-800">{{ $order->table?->name ?? 'Meja —' }}</span>
                <span class="mx-2 text-slate-300">·</span>
                <span class="text-slate-600">{{ $order->customer_name }}</span>
                <span class="mx-2 text-slate-300">·</span>
                <span class="text-xs text-slate-400">{{ $order->created_at->format('H:i') }}</span>
            </div>
            <span class="text-base font-black text-amber-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Items --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Item Pesanan</p>
                <div class="space-y-1.5">
                    @foreach ($order->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-700">{{ $item->qty }}× {{ $item->product_name }}{{ $item->variant_name ? ' ('.$item->variant_name.')' : '' }}</span>
                        <span class="text-slate-500">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between text-sm font-semibold">
                    <span>Total</span>
                    <span class="text-amber-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>

                @if ($order->rejection_reason)
                <div class="mt-3 bg-red-50 border border-red-200 rounded-xl px-3 py-2 text-xs text-red-600">
                    <strong>Ditolak:</strong> {{ $order->rejection_reason }}
                </div>
                @endif
            </div>

            {{-- Proof --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Bukti Transfer</p>
                <a href="{{ Storage::url($order->payment_proof) }}" target="_blank">
                    <img src="{{ Storage::url($order->payment_proof) }}"
                         alt="Bukti Transfer"
                         class="w-full max-h-64 object-contain rounded-xl border border-slate-200 hover:opacity-90 transition-opacity cursor-zoom-in">
                </a>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-5 pb-5 flex gap-3">
            <button onclick="approveOrder({{ $order->id }})"
                    class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2.5 rounded-xl text-sm transition-colors">
                ✅ Konfirmasi Diterima
            </button>
            <button @click="rejectModal = {{ $order->id }}; rejectReason = ''"
                    class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2.5 rounded-xl text-sm border border-red-200 transition-colors">
                ❌ Tolak
            </button>
        </div>
    </div>
    @endforeach

    {{-- Reject modal --}}
    <div x-show="rejectModal !== null" x-cloak
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm p-6 space-y-4" @click.outside="rejectModal = null">
            <h3 class="font-bold text-slate-800">Alasan Penolakan</h3>
            <textarea x-model="rejectReason" rows="3"
                      placeholder="Contoh: Nominal tidak sesuai, bukti tidak jelas..."
                      class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-red-400 resize-none"></textarea>
            <div class="flex gap-3">
                <button @click="rejectModal = null" class="flex-1 border border-slate-200 py-2.5 rounded-xl text-sm text-slate-600">Batal</button>
                <button @click="rejectOrder(rejectModal, rejectReason)"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl text-sm">Tolak</button>
            </div>
        </div>
    </div>
</div>

<script>
async function approveOrder(orderId) {
    const res = await fetch(`/app/qris-verification/${orderId}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
    });
    if (res.ok) {
        document.getElementById(`order-${orderId}`)?.remove();
    } else {
        alert('Gagal mengkonfirmasi. Coba lagi.');
    }
}

async function rejectOrder(orderId, reason) {
    if (!reason.trim()) { alert('Isi alasan penolakan.'); return; }
    const res = await fetch(`/app/qris-verification/${orderId}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        body: JSON.stringify({ reason }),
    });
    if (res.ok) {
        document.getElementById(`order-${orderId}`)?.remove();
        window.dispatchEvent(new CustomEvent('alpine:init'));
        location.reload();
    } else {
        alert('Gagal menolak. Coba lagi.');
    }
}
</script>
@endsection
