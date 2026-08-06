@extends('layouts.pos')
@section('title', 'Struk — ' . $order->invoice_no)

@section('content')
@php $paperWidth = $order->branch?->receipt_paper_width ?? 80; @endphp
<div class="flex flex-col items-center justify-start h-[calc(100vh-3rem)] overflow-y-auto bg-slate-100 py-8 px-4">

    <div class="w-full max-w-sm">
        {{-- Actions --}}
        <div class="flex gap-3 mb-4 print:hidden">
            <a href="{{ route('pos.index') }}"
               class="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-2xl text-sm text-center transition-colors">
                + Order Baru
            </a>
            <button onclick="window.print()"
                    class="flex-1 border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold py-2.5 rounded-2xl text-sm transition-colors">
                🖨️ Cetak
            </button>
        </div>

        {{-- Receipt --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden print:shadow-none print:rounded-none" id="receipt">

            {{-- Header --}}
            <div class="text-center px-6 pt-6 pb-4 border-b border-dashed border-slate-200">
                <h1 class="font-black text-lg text-slate-800">{{ $order->branch?->company?->name ?? 'Postera' }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ $order->branch?->name }}</p>
                @if ($order->branch?->address)
                    <p class="text-xs text-slate-400 mt-0.5">{{ $order->branch->address }}</p>
                @endif
            </div>

            {{-- Order Info --}}
            <div class="px-6 py-4 border-b border-dashed border-slate-200 space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">No. Invoice</span>
                    <span class="font-bold text-slate-800">{{ $order->invoice_no }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tanggal</span>
                    <span class="text-slate-700">{{ $order->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kasir</span>
                    <span class="text-slate-700">{{ $order->user?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tipe</span>
                    <span class="text-slate-700">{{ $order->type === 'dine_in' ? 'Makan di Sini' : 'Bawa Pulang' }}</span>
                </div>
                @if ($order->table)
                <div class="flex justify-between">
                    <span class="text-slate-500">Meja</span>
                    <span class="text-slate-700">{{ $order->table->name }}</span>
                </div>
                @endif
                @if ($order->customer_name || $order->customer)
                <div class="flex justify-between">
                    <span class="text-slate-500">Pelanggan</span>
                    <span class="text-slate-700">{{ $order->customer_name ?? $order->customer?->name }}</span>
                </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="px-6 py-4 border-b border-dashed border-slate-200 space-y-3">
                @foreach ($order->items as $item)
                <div>
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-800">{{ $item->product_name }}{{ $item->variant_name ? ' · ' . $item->variant_name : '' }}</p>
                            @if ($item->modifiers->count())
                                <p class="text-xs text-slate-400">{{ $item->modifiers->pluck('option_name')->join(', ') }}</p>
                            @endif
                            @if ($item->notes)
                                <p class="text-xs text-slate-400 italic">{{ $item->notes }}</p>
                            @endif
                            <p class="text-xs text-slate-500">{{ $item->qty }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 shrink-0">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="px-6 py-4 border-b border-dashed border-slate-200 space-y-1.5 text-sm">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600">
                    <span>Diskon</span>
                    <span>− Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if ($order->tax_amount > 0)
                @php
                    $taxPct = $order->subtotal > 0
                        ? round($order->tax_amount / $order->subtotal * 100, 1)
                        : 0;
                @endphp
                <div class="flex justify-between text-slate-500">
                    <span>Pajak{{ $taxPct > 0 ? ' ('.$taxPct.'%)' : '' }}</span>
                    <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between font-black text-slate-800 pt-2 border-t border-slate-100 text-base">
                    <span>TOTAL</span>
                    <span class="text-amber-600">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            {{-- Payment --}}
            <div class="px-6 py-4 border-b border-dashed border-slate-200 space-y-1.5 text-sm">
                @foreach ($order->payments as $pay)
                <div class="flex justify-between text-slate-600">
                    <span>{{ match($pay->method) {
                        'cash'     => 'Tunai',
                        'qris'     => 'QRIS',
                        'transfer' => 'Transfer',
                        'card'     => 'Kartu',
                        'credit'   => 'Kredit',
                        default    => $pay->method,
                    } }}</span>
                    <span>Rp {{ number_format($pay->amount, 0, ',', '.') }}</span>
                </div>
                @endforeach
                @if ($order->change_amount > 0)
                <div class="flex justify-between text-emerald-700 font-semibold">
                    <span>Kembalian</span>
                    <span>Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="px-6 py-5 text-center">
                <p class="text-xs text-slate-400">Terima kasih sudah berkunjung!</p>
                <p class="text-xs text-slate-300 mt-1">Powered by Postera</p>
            </div>
        </div>
    </div>
</div>

<style>
@page {
    size: {{ $paperWidth }}mm auto;
    margin: 0;
}
@media print {
    html, body {
        width: {{ $paperWidth }}mm;
        margin: 0;
        padding: 0;
    }
    body * { visibility: hidden; }
    #receipt, #receipt * { visibility: visible; }
    #receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: {{ $paperWidth }}mm;
        margin: 0;
        box-shadow: none;
        border-radius: 0;
    }
    .print\:hidden { display: none; }
}
</style>

@endsection
