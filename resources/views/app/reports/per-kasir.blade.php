@extends('layouts.app')
@section('title', 'Laporan Per Kasir')
@section('page-title', 'Laporan Per Kasir')

@section('content')

<div x-data="{ proofOpen: false, proofUrl: '', proofMeta: '' }" x-on:keydown.escape.window="proofOpen = false">

    {{-- Period filter --}}
    <form method="GET" action="{{ route('app.reports.per-kasir') }}"
          class="bg-white rounded-2xl shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Dari</label>
            <input type="date" name="dari" value="{{ $dari }}"
                   class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Sampai</label>
            <input type="date" name="sampai" value="{{ $sampai }}"
                   class="border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:border-amber-500">
        </div>
        <button type="submit"
                class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2 rounded-xl text-sm">
            Tampilkan
        </button>
        {{-- Quick presets --}}
        <div class="flex gap-2 flex-wrap">
            @php
                $presets = [
                    'Hari Ini'   => [today()->format('Y-m-d'), today()->format('Y-m-d')],
                    '7 Hari'     => [today()->subDays(6)->format('Y-m-d'), today()->format('Y-m-d')],
                    '30 Hari'    => [today()->subDays(29)->format('Y-m-d'), today()->format('Y-m-d')],
                    'Bulan Ini'  => [today()->startOfMonth()->format('Y-m-d'), today()->format('Y-m-d')],
                ];
            @endphp
            @foreach($presets as $label => [$d, $s])
                <a href="{{ route('app.reports.per-kasir', ['dari' => $d, 'sampai' => $s]) }}"
                   class="border border-slate-200 text-slate-600 hover:bg-amber-50 hover:border-amber-300 text-xs font-medium px-3 py-2 rounded-xl transition-colors
                          {{ $dari === $d && $sampai === $s ? 'bg-amber-50 border-amber-300 text-amber-700' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </form>

    {{-- Summary per kasir --}}
    @if($perKasir->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        @foreach($perKasir as $row)
        <div class="bg-white rounded-2xl shadow-sm p-4">
            <p class="text-sm font-semibold text-slate-700 truncate">{{ $row->user?->name ?? '(Tanpa Kasir)' }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ number_format($row->transaksi) }} transaksi</p>
            <p class="text-lg font-bold text-slate-800 mt-1">Rp {{ number_format($row->pendapatan, 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Detail transaksi --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800 text-sm">Detail Transaksi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Waktu</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Invoice</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Kasir</th>
                        <th class="text-left px-5 py-3 font-medium text-slate-600">Metode</th>
                        <th class="text-right px-5 py-3 font-medium text-slate-600">Total</th>
                        <th class="text-center px-5 py-3 font-medium text-slate-600">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksi as $order)
                    @php $metode = $order->payments->first()?->method; @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 whitespace-nowrap text-slate-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-3 whitespace-nowrap text-slate-700 font-medium">{{ $order->invoice_no ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-700">{{ $order->user?->name ?? '(Tanpa Kasir)' }}</td>
                        <td class="px-5 py-3 text-slate-600 capitalize">{{ $metode ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($order->payment_proof)
                            <button
                                type="button"
                                @click="proofOpen = true; proofUrl = @js(asset('storage/' . $order->payment_proof)); proofMeta = @js(($order->invoice_no ?? '#' . $order->id) . ' · ' . $order->created_at->format('d M Y H:i'))"
                                class="text-xs font-medium text-amber-600 hover:underline"
                            >
                                Lihat Bukti
                            </button>
                            @else
                            <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada data untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transaksi->isNotEmpty())
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-sm font-semibold text-slate-700">Total ({{ number_format($transaksi->count()) }} transaksi)</td>
                        <td class="px-5 py-3 text-right text-sm font-semibold text-slate-800">
                            Rp {{ number_format($transaksi->sum('total'), 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Bukti popup --}}
    <div x-show="proofOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="proofOpen" x-transition.opacity @click="proofOpen = false" class="absolute inset-0 bg-slate-950/60"></div>
        <div x-show="proofOpen" x-transition @click.outside="proofOpen = false"
             class="relative w-full max-w-md max-h-[85vh] flex flex-col rounded-2xl bg-white shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                <span class="text-xs font-medium text-slate-500" x-text="proofMeta"></span>
                <button type="button" @click="proofOpen = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="overflow-auto p-4">
                <img :src="proofUrl" alt="Bukti transfer" class="w-full h-auto rounded-xl">
            </div>
        </div>
    </div>

</div>

@endsection
