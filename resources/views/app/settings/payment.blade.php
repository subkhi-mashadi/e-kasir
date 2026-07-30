@extends('layouts.app')
@section('title', 'Pengaturan Pembayaran')
@section('page-title', 'Pengaturan Pembayaran')
@section('page-subtitle', 'Integrasi payment gateway untuk QR Order pelanggan')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ provider: '{{ old('payment_provider', $company->payment_provider ?? 'midtrans') }}' }">

    @if (session('success'))
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-3 text-sm text-emerald-700 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
        {{ session('success') }}
    </div>
    @endif

    {{-- Info card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 text-sm text-blue-700 flex gap-3">
        <svg class="w-5 h-5 shrink-0 mt-0.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
        </svg>
        <p>Pilih payment gateway untuk menerima pembayaran QRIS dari pelanggan. Pembayaran masuk langsung ke akun bisnis Anda.</p>
    </div>

    <form method="POST" action="{{ route('app.settings.payment.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Provider selector --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">Payment Provider</h2>
                <p class="text-sm text-slate-500 mt-0.5">Pilih provider yang ingin digunakan</p>
            </div>
            <div class="px-6 py-5">
                <input type="hidden" name="payment_provider" :value="provider">
                <div class="grid grid-cols-3 gap-3">
                    <button type="button" @click="provider = 'qris_static'"
                            :class="provider === 'qris_static'
                                ? 'border-emerald-400 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-300'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            class="flex flex-col items-center gap-2 border-2 rounded-2xl p-4 transition-all">
                        <span class="text-2xl">🟢</span>
                        <span class="font-semibold text-sm">QRIS Statis</span>
                        <span class="text-xs text-slate-400">Upload gambar</span>
                    </button>
                    <button type="button" @click="provider = 'midtrans'"
                            :class="provider === 'midtrans'
                                ? 'border-amber-400 bg-amber-50 text-amber-700 ring-2 ring-amber-300'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            class="flex flex-col items-center gap-2 border-2 rounded-2xl p-4 transition-all">
                        <span class="text-2xl">🟠</span>
                        <span class="font-semibold text-sm">Midtrans</span>
                        <span class="text-xs text-slate-400">QRIS via PG</span>
                    </button>
                    <button type="button" @click="provider = 'xendit'"
                            :class="provider === 'xendit'
                                ? 'border-blue-400 bg-blue-50 text-blue-700 ring-2 ring-blue-300'
                                : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                            class="flex flex-col items-center gap-2 border-2 rounded-2xl p-4 transition-all">
                        <span class="text-2xl">🔵</span>
                        <span class="font-semibold text-sm">Xendit</span>
                        <span class="text-xs text-slate-400">QRIS via PG</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Midtrans section --}}
        <div x-show="provider === 'midtrans'" x-cloak class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">Midtrans Keys</h2>
                <p class="text-sm text-slate-500 mt-0.5">Settings → Access Keys di dashboard Midtrans</p>
            </div>
            <div class="px-6 py-5 space-y-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Server Key <span class="text-slate-400 font-normal ml-1">(rahasia)</span>
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'" name="midtrans_server_key"
                            value="{{ old('midtrans_server_key', $company->midtrans_server_key) }}"
                            placeholder="SB-Mid-server-xxxx atau Mid-server-xxxx"
                            class="w-full border-2 border-slate-300 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                        <button type="button" @click="show=!show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Client Key</label>
                    <input type="text" name="midtrans_client_key"
                        value="{{ old('midtrans_client_key', $company->midtrans_client_key) }}"
                        placeholder="SB-Mid-client-xxxx atau Mid-client-xxxx"
                        class="w-full border-2 border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                </div>

                <div class="flex items-start gap-3">
                    <input type="checkbox" id="midtrans_is_production" name="midtrans_is_production" value="1"
                        {{ old('midtrans_is_production', $company->midtrans_is_production) ? 'checked' : '' }}
                        class="mt-0.5 w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-400">
                    <div>
                        <label for="midtrans_is_production" class="text-sm font-medium text-slate-700 cursor-pointer">Mode Produksi</label>
                        <p class="text-xs text-slate-500 mt-0.5">Aktifkan untuk key produksi (transaksi nyata).</p>
                    </div>
                </div>

                @if($company->midtrans_server_key)
                <div class="flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    Terkonfigurasi — mode {{ $company->midtrans_is_production ? 'Produksi' : 'Sandbox' }}
                </div>
                @else
                <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    Belum dikonfigurasi
                </div>
                @endif
            </div>
        </div>

        {{-- Xendit section --}}
        <div x-show="provider === 'xendit'" x-cloak class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">Xendit Secret Key</h2>
                <p class="text-sm text-slate-500 mt-0.5">Settings → API Keys di dashboard Xendit</p>
            </div>
            <div class="px-6 py-5 space-y-5">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Secret Key <span class="text-slate-400 font-normal ml-1">(rahasia)</span>
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'" name="xendit_secret_key"
                            value="{{ old('xendit_secret_key', $company->xendit_secret_key) }}"
                            placeholder="xnd_production_... atau xnd_development_..."
                            class="w-full border-2 border-slate-300 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                        <button type="button" @click="show=!show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-600 space-y-1">
                    <p class="font-semibold text-slate-700">Webhook URL untuk Xendit:</p>
                    <code class="text-xs font-mono bg-white border border-slate-200 rounded px-2 py-1 block break-all">{{ route('webhook.xendit') }}</code>
                    <p class="text-xs text-slate-500 mt-1">Daftarkan URL ini di Xendit Dashboard → Webhooks → QR Code payment status.</p>
                </div>

                @if($company->xendit_secret_key)
                <div class="flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    Terkonfigurasi
                </div>
                @else
                <div class="flex items-center gap-2 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>
                    Belum dikonfigurasi
                </div>
                @endif
            </div>
        </div>

        {{-- QRIS Statis section --}}
        <div x-show="provider === 'qris_static'" x-cloak class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">Gambar QRIS Statis</h2>
                <p class="text-sm text-slate-500 mt-0.5">Upload gambar QRIS dari mesin EDC atau aplikasi merchant Anda</p>
            </div>
            <div class="px-6 py-5 space-y-5">
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-600">
                    <p class="text-xs text-slate-500">Paste string QRIS statis dari mesin EDC atau app merchant. Sistem akan otomatis generate QRIS dinamis per transaksi (nominal terisi otomatis). Pelanggan scan → bayar → upload bukti → kasir konfirmasi.</p>
                </div>

                {{-- Tutorial --}}
                <div x-data="{ open: false }" class="border-2 border-dashed border-emerald-200 rounded-xl overflow-hidden">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-emerald-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">💡</span>
                            <span class="text-sm font-semibold text-emerald-700">Cara mendapatkan string QRIS dari gambar</span>
                        </div>
                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-emerald-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" class="px-4 pb-4 space-y-3">
                        <ol class="space-y-3 text-sm text-slate-600">
                            <li class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">1</span>
                                <div>
                                    <p class="font-medium text-slate-700">Siapkan gambar QRIS statis Anda</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Screenshot dari aplikasi merchant, foto mesin EDC, atau unduh dari dashboard bank.</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">2</span>
                                <div>
                                    <p class="font-medium text-slate-700">Buka decoder QRIS online</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Kunjungi link berikut di tab baru:</p>
                                    <a href="https://zxing.org" target="_blank" rel="noopener"
                                       class="inline-flex items-center gap-1 mt-1 text-xs font-mono bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-emerald-600 hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
                                        🔗 zxing.org
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">3</span>
                                <div>
                                    <p class="font-medium text-slate-700">Upload gambar QRIS</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Klik <strong>Choose File</strong> → pilih gambar QRIS → klik <strong>Submit</strong>.</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">4</span>
                                <div>
                                    <p class="font-medium text-slate-700">Salin hasilnya</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Setelah decode berhasil, muncul teks panjang diawali <code class="bg-slate-100 px-1 rounded font-mono">00020101...</code> — salin semua teks tersebut.</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="shrink-0 w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">5</span>
                                <div>
                                    <p class="font-medium text-slate-700">Paste di kolom di bawah ini</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Tempel string tersebut ke kolom "String QRIS Statis" lalu simpan.</p>
                                </div>
                            </li>
                        </ol>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-3 py-2 text-xs text-amber-700">
                            ⚠️ Pastikan gambar QRIS cukup jelas dan tidak blur agar decode berhasil.
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5" for="qris_static_string">String QRIS Statis</label>
                    <textarea id="qris_static_string" name="qris_static_string" rows="5"
                              placeholder="00020101021226..."
                              class="w-full border-2 border-slate-300 rounded-xl px-4 py-3 text-sm font-mono outline-none focus:border-emerald-400 resize-none">{{ old('qris_static_string', $company->qris_static_string ?? '') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1.5">Salin dari aplikasi merchant atau mesin EDC — biasanya diawali <code class="bg-slate-100 px-1 rounded">000201</code></p>
                </div>

                @if($company->qris_static_string)
                <div class="flex items-center m-4 gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    String QRIS tersimpan — QRIS dinamis aktif
                </div>
                @else
                <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    Belum ada string QRIS
                </div>
                @endif
            </div>
        </div>

        <div>
            <button type="submit"
                class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2.5 mt-4 rounded-xl text-sm transition-colors">
                Simpan Konfigurasi
            </button>
        </div>

    </form>
</div>
@endsection
