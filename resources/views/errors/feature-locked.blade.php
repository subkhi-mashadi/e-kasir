@extends('layouts.app')

@section('title', $featureName ?? 'Fitur Tidak Tersedia')
@section('page-title', $featureName ?? 'Fitur Tidak Tersedia')
@section('page-subtitle', 'Upgrade paket untuk mengakses fitur ini')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] px-4 ">

    {{-- Card utama --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 max-w-6xl w-full overflow-hidden">

        {{-- Banner atas --}}
        <div class="bg-linear-to-br from-amber-500 to-amber-700 px-8 py-10 text-center relative overflow-hidden">
            {{-- Lingkaran dekorasi --}}
            <div class="absolute -top-6 -right-6 w-32 h-32 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-8 -left-8 w-40 h-40 rounded-full bg-white/10"></div>

            <div class="relative">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-white/20 mb-4 mx-auto">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-white mb-1">Fitur Terkunci</h2>
                <p class="text-amber-100 text-sm">Paket Anda belum mencakup fitur ini</p>
            </div>
        </div>

        {{-- Konten --}}
        <div class="px-8 py-8">
            <h3 class="text-lg font-bold text-slate-800 mb-2">{{ $featureName ?? 'Fitur Tidak Tersedia' }}</h3>
            <p class="text-slate-500 text-sm mb-6 leading-relaxed">
                {{ $description ?? 'Fitur ini tidak tersedia di paket Anda saat ini. Upgrade ke paket yang lebih tinggi untuk mengakses.' }}
            </p>

            {{-- Fitur yang didapat saat upgrade --}}
            @if(!empty($benefits))
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                <p class="text-xs font-semibold text-amber-700 uppercase tracking-wider mb-3">Yang Anda dapatkan</p>
                <ul class="space-y-2">
                    @foreach($benefits as $benefit)
                    <li class="flex items-start gap-2 text-sm text-slate-700">
                        <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                        {{ $benefit }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Paket saat ini --}}
            <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-4 mb-6">
                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Paket saat ini</p>
                    <p class="text-sm font-semibold text-slate-700">
                        {{ auth()->user()->company?->plan()?->name ?? 'Starter' }}
                    </p>
                </div>
            </div>

            {{-- Tombol --}}
            <a href="{{ route('subscription.billing') }}"
               class="flex items-center justify-center gap-2 w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                </svg>
                Upgrade Paket
            </a>

            <a href="{{ url()->previous() === url()->current() ? route('app.dashboard') : url()->previous() }}"
               class="flex items-center justify-center gap-2 w-full mt-3 text-slate-500 hover:text-slate-700 text-sm font-medium py-2 transition-colors">
                ← Kembali
            </a>
        </div>
    </div>

</div>
@endsection
