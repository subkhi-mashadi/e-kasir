<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postera — Kasir Digital untuk Bisnis F&B Modern</title>
    <meta name="description" content="Sistem kasir POS berbasis cloud untuk restoran, kafe, dan warung. QR ordering, kitchen display, laporan real-time, dan bisa offline.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── Entrance keyframes ── */
        @keyframes fadeInUp    { from { opacity:0; transform:translateY(32px) } to { opacity:1; transform:translateY(0) } }
        @keyframes fadeInDown  { from { opacity:0; transform:translateY(-24px)} to { opacity:1; transform:translateY(0) } }
        @keyframes fadeInLeft  { from { opacity:0; transform:translateX(-40px)} to { opacity:1; transform:translateX(0) } }
        @keyframes fadeInRight { from { opacity:0; transform:translateX(40px) } to { opacity:1; transform:translateX(0) } }
        @keyframes scaleIn     { from { opacity:0; transform:scale(.88)       } to { opacity:1; transform:scale(1)    } }
        @keyframes float       { 0%,100%{ transform:translateY(0)   } 50%{ transform:translateY(-12px) } }
        @keyframes glowPulse   { 0%,100%{ box-shadow:0 0 0 0 rgba(251,191,36,.4) } 50%{ box-shadow:0 0 0 12px rgba(251,191,36,0) } }

        /* Hero entrance – runs once on load */
        .hero-badge   { animation: fadeInDown  .6s ease both; }
        .hero-h1      { animation: fadeInLeft  .7s .12s ease both; }
        .hero-sub     { animation: fadeInLeft  .7s .22s ease both; }
        .hero-btns    { animation: fadeInUp    .6s .32s ease both; }
        .hero-stats   { animation: fadeInUp    .6s .44s ease both; }
        .hero-mockup  { animation: fadeInRight .8s .18s ease both; }
        .hero-badge-offline { animation: scaleIn .5s .9s  ease both; }
        .hero-badge-qr      { animation: scaleIn .5s 1.05s ease both; }
        .scroll-arrow { animation: fadeInUp .6s .6s ease both; }

        /* Float loop on mockup */
        .mockup-float { animation: float 5s ease-in-out infinite; }

        /* Scroll-reveal – JS adds .revealed */
        [data-reveal] { opacity:0; transform:translateY(28px); transition: opacity .55s ease, transform .55s ease; }
        [data-reveal="left"]  { transform:translateX(-32px); }
        [data-reveal="right"] { transform:translateX(32px); }
        [data-reveal="scale"] { transform:scale(.92); }
        [data-reveal].revealed { opacity:1; transform:none; }

        /* Stagger helpers – set via style attribute */
        [data-delay="1"] { transition-delay:.08s }
        [data-delay="2"] { transition-delay:.16s }
        [data-delay="3"] { transition-delay:.24s }
        [data-delay="4"] { transition-delay:.32s }
        [data-delay="5"] { transition-delay:.40s }
        [data-delay="6"] { transition-delay:.48s }

        /* Button press */
        .btn-primary { transition: background-color .15s, transform .12s, box-shadow .15s; }
        .btn-primary:hover  { transform:translateY(-2px) scale(1.02); }
        .btn-primary:active { transform:translateY(0)   scale(.97); }
        .btn-ghost { transition: background-color .15s, transform .12s; }
        .btn-ghost:hover  { transform:translateY(-2px); }
        .btn-ghost:active { transform:translateY(0); }

        /* Feature card lift */
        .feature-card { transition: transform .22s ease, box-shadow .22s ease; }
        .feature-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px -8px rgba(0,0,0,.12); }

        /* Pricing card lift */
        .price-card { transition: transform .22s ease, box-shadow .22s ease; }
        .price-card:hover { transform:translateY(-8px); }

        /* Step number bounce */
        .step-num { transition: transform .2s ease, box-shadow .2s ease; }
        .step-card:hover .step-num { transform:scale(1.1) rotate(-4deg); }

        /* Nav link underline */
        .nav-link { position:relative; }
        .nav-link::after { content:''; position:absolute; bottom:-2px; left:0; width:0; height:2px; background:currentColor; border-radius:2px; transition:width .2s ease; }
        .nav-link:hover::after { width:100%; }

        /* Glow CTA button */
        .btn-glow { animation: glowPulse 2.5s ease infinite; }

        /* FAQ smooth expand */
        [x-show][x-cloak] { display:none!important; }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

{{-- ── NAVBAR ──────────────────────────────────────────────────────────── --}}
<header x-data="{ scrolled: false, mobileOpen: false }"
        @scroll.window="scrolled = window.scrollY > 40"
        :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-transparent'"
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <img src="{{ asset('icons/logo.png') }}" alt="Postera" class="w-14 h-14 object-contain group-hover:scale-110 transition-transform">
            <span :class="scrolled ? 'text-slate-800' : 'text-white'" class="text-lg font-black tracking-tight transition-colors">Postera</span>
        </a>

        <nav class="hidden md:flex items-center gap-8">
            @foreach([['#fitur','Fitur'],['#demo','Live Demo'],['#harga','Harga'],['#faq','FAQ']] as [$href,$label])
            <a href="{{ $href }}" :class="scrolled ? 'text-slate-600 hover:text-amber-600' : 'text-white/80 hover:text-white'"
               class="text-sm font-medium transition-colors nav-link">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="hidden md:flex items-center gap-4">
            <a href="{{ route('login') }}"
               :class="scrolled ? 'text-slate-700 hover:text-amber-600 border-slate-200 hover:border-amber-300' : 'text-white/90 hover:text-white border-white/30 hover:border-white/60'"
               class="text-sm font-semibold px-5 py-2 rounded-xl border transition-all btn-ghost">Masuk</a>
            <a href="{{ route('register') }}"
               class="bg-amber-500 hover:bg-amber-400 text-white text-sm font-black px-5 py-2 rounded-xl btn-primary shadow-lg shadow-amber-500/30">
                Coba Gratis
            </a>
        </div>

        <button @click="mobileOpen=!mobileOpen" class="md:hidden p-2 rounded-lg transition-colors"
                :class="scrolled ? 'text-slate-600' : 'text-white'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         class="md:hidden bg-white border-t border-slate-100 px-6 py-4 space-y-2 shadow-lg">
        @foreach([['#fitur','Fitur'],['#cara-kerja','Cara Kerja'],['#harga','Harga'],['#faq','FAQ']] as [$href,$label])
        <a href="{{ $href }}" @click="mobileOpen=false" class="block text-sm font-medium text-slate-600 hover:text-amber-600 py-1.5 transition-colors">{{ $label }}</a>
        @endforeach
        <div class="pt-2 border-t border-slate-100 space-y-2">
            <a href="{{ route('login') }}" class="block text-center text-sm font-semibold text-slate-700 border border-slate-200 py-2.5 rounded-xl hover:bg-slate-50 transition-colors">Masuk</a>
            <a href="{{ route('register') }}" class="block text-center bg-amber-500 hover:bg-amber-400 text-white text-sm font-black py-2.5 rounded-xl transition-colors">Coba Gratis 14 Hari</a>
        </div>
    </div>
</header>

{{-- ── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="relative min-h-screen bg-gradient-to-br from-amber-950 via-amber-900 to-amber-800 flex items-center overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-amber-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 -left-32 w-80 h-80 bg-amber-600/15 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-amber-400/10 rounded-full blur-2xl"></div>
        <svg class="absolute inset-0 w-full h-full opacity-[0.04]" xmlns="http://www.w3.org/2000/svg">
            <defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
            </pattern></defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    <div class="relative max-w-6xl mx-auto px-6 py-24 md:py-32 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            {{-- Teks kiri --}}
            <div>
                <div class="hero-badge inline-flex items-center gap-2 bg-amber-500/20 border border-amber-500/30 rounded-full px-4 py-1.5 mb-8 cursor-default">
                    <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                    <span class="text-amber-300 text-xs font-semibold tracking-wide">Trial 14 Hari Gratis · Tanpa Kartu Kredit</span>
                </div>

                <h1 class="hero-h1 text-5xl md:text-6xl font-black text-white leading-tight mb-6">
                    Kasir Digital<br>
                    <span class="text-amber-400">Cerdas & Modern</span><br>
                    untuk Bisnis F&B
                </h1>

                <p class="hero-sub text-amber-200/80 text-lg leading-relaxed mb-10">
                    Kelola kasir, dapur, dan laporan dalam satu platform cloud.
                    QR ordering per meja, bisa offline, multi-cabang — siap dipakai hari ini.
                </p>

                <div class="hero-btns flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('register') }}"
                       class="btn-primary btn-glow inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black px-8 py-4 rounded-2xl text-base shadow-xl shadow-amber-900/50">
                        Mulai Gratis Sekarang
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                    <a href="#fitur"
                       class="btn-ghost inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-8 py-4 rounded-2xl text-base border border-white/20">
                        Lihat Fitur
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                </div>

                <div class="hero-stats flex flex-wrap items-center gap-8 mt-12 pt-8 border-t border-white/10">
                    @foreach([['500+','Usaha aktif'],['1 juta+','Transaksi'],['99.9%','Uptime'],['14 hari','Trial gratis']] as [$num,$label])
                    <div class="group cursor-default">
                        <div class="text-2xl font-black text-amber-400 group-hover:scale-110 transition-transform inline-block">{{ $num }}</div>
                        <div class="text-xs text-amber-300/60 mt-0.5">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Mockup kanan --}}
            <div class="relative hidden lg:block hero-mockup">
                <div class="mockup-float bg-amber-900/40 border border-amber-700/40 rounded-3xl p-4 shadow-2xl backdrop-blur-sm">
                    <div class="flex items-center gap-2 mb-4 px-2">
                        <div class="w-3 h-3 rounded-full bg-red-400/70 hover:bg-red-400 transition-colors cursor-pointer"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400/70 hover:bg-amber-400 transition-colors cursor-pointer"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400/70 hover:bg-green-400 transition-colors cursor-pointer"></div>
                        <div class="flex-1 bg-amber-800/60 rounded-lg px-3 py-1.5 ml-2">
                            <span class="text-amber-400/50 text-xs">postera.app/pos</span>
                        </div>
                    </div>
                    <div class="bg-slate-900 rounded-2xl overflow-hidden">
                        <div class="bg-amber-900 px-4 py-3 flex items-center justify-between">
                            <span class="text-amber-200 text-xs font-bold">E-KASIR POS</span>
                            <div class="flex items-center gap-2">
                                <span class="bg-emerald-500 text-white text-xs px-2 py-0.5 rounded-full font-semibold animate-pulse">● Online</span>
                                <span class="text-amber-300/60 text-xs">Warung Kopi · Cabang Utama</span>
                            </div>
                        </div>
                        <div class="flex" style="height:300px">
                            <div class="flex-1 p-3 overflow-hidden">
                                <div class="flex gap-2 mb-3">
                                    @foreach(['Semua','Kopi','Non-Kopi','Makanan'] as $i => $cat)
                                    <span class="{{ $i===0 ? 'bg-amber-500 text-white' : 'bg-slate-700 text-slate-300 hover:bg-slate-600' }} text-xs px-3 py-1 rounded-full whitespace-nowrap font-medium cursor-pointer transition-colors">{{ $cat }}</span>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach([['Kopi Susu','18.000'],['Es Teh','8.000'],['Americano','20.000'],['Mie Goreng','22.000'],['Nasi Goreng','25.000'],['Pisang Goreng','12.000']] as [$name,$price])
                                    <div class="bg-slate-800 hover:bg-amber-900/50 rounded-xl p-2 cursor-pointer transition-colors group/card">
                                        <div class="bg-amber-500/20 rounded-lg h-12 mb-2 flex items-center justify-center group-hover/card:bg-amber-500/30 transition-colors">
                                            <svg class="w-5 h-5 text-amber-400/60 group-hover/card:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                        </div>
                                        <p class="text-white text-xs font-semibold truncate">{{ $name }}</p>
                                        <p class="text-amber-400 text-xs">Rp {{ $price }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="w-44 bg-slate-800/80 border-l border-slate-700 p-3 flex flex-col">
                                <p class="text-slate-400 text-xs font-bold mb-3">KERANJANG</p>
                                <div class="space-y-2 flex-1">
                                    @foreach([['Kopi Susu','18.000','2'],['Es Teh','8.000','1'],['Americano','20.000','1']] as [$n,$p,$q])
                                    <div class="bg-slate-700/60 hover:bg-slate-700 rounded-lg p-2 transition-colors">
                                        <p class="text-white text-xs font-medium truncate">{{ $n }}</p>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-amber-400 text-xs">{{ $q }}×</span>
                                            <span class="text-slate-300 text-xs">{{ $p }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="border-t border-slate-700 pt-2 mt-2">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-slate-400 text-xs">Total</span>
                                        <span class="text-amber-400 text-xs font-bold">Rp 64.000</span>
                                    </div>
                                    <div class="bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-center text-amber-950 text-xs font-black py-2 rounded-lg cursor-pointer transition-colors">BAYAR</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-badge-offline absolute -bottom-4 -left-4 bg-emerald-500 text-white rounded-2xl px-4 py-2 shadow-xl text-sm font-bold hover:scale-105 transition-transform cursor-default">
                    ✓ Bisa Offline
                </div>
                <div class="hero-badge-qr absolute -top-4 -right-4 bg-amber-400 text-amber-950 rounded-2xl px-4 py-2 shadow-xl text-sm font-bold hover:scale-105 transition-transform cursor-default">
                    QR Ordering
                </div>
            </div>

        </div>
    </div>

    <div class="scroll-arrow absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <a href="#fitur">
            <svg class="w-6 h-6 text-white/40 hover:text-white/70 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </a>
    </div>
</section>

{{-- ── FITUR ────────────────────────────────────────────────────────────── --}}
<section id="fitur" class="py-24 bg-slate-50">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-16" data-reveal>
            <span class="text-amber-500 text-sm font-bold uppercase tracking-widest">Fitur Lengkap</span>
            <h2 class="text-3xl md:text-4xl font-black text-slate-800 mt-3 mb-4">Semua yang Dibutuhkan Bisnis F&B</h2>
            <p class="text-slate-500 max-w-xl mx-auto">Dari kasir hingga dapur, dari meja pelanggan hingga laporan keuangan — semua terintegrasi dalam satu platform.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
            $features = [
                ['icon'=>'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 20h16a1 1 0 001-1V9a1 1 0 00-.293-.707l-5-5A1 1 0 0015 3H4a1 1 0 00-1 1v15a1 1 0 001 1z','title'=>'Kasir POS Cepat','color'=>'amber','desc'=>'Antarmuka kasir yang bersih dan responsif. Pencarian produk instan, keranjang cerdas, diskon, dan berbagai metode pembayaran.'],
                ['icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z','title'=>'QR Ordering Meja','color'=>'emerald','desc'=>'Pelanggan scan QR di meja, pesan sendiri dari HP. Order langsung masuk dapur tanpa antri ke kasir.'],
                ['icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10','title'=>'Kitchen Display','color'=>'blue','desc'=>'Layar dapur real-time. Antrian, proses, dan siap antar — semua terorganisir dengan notifikasi otomatis ke kasir.'],
                ['icon'=>'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0','title'=>'Offline & PWA','color'=>'purple','desc'=>'Internet mati? Kasir tetap jalan. Transaksi tersimpan lokal dan sinkron otomatis saat online kembali. Install sebagai app di HP.'],
                ['icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4','title'=>'Multi-Cabang','color'=>'rose','desc'=>'Kelola semua cabang dari satu dashboard. Data terpisah per cabang, laporan gabungan, satu akun untuk semua.'],
                ['icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','title'=>'Laporan Real-Time','color'=>'teal','desc'=>'Laporan penjualan harian, per kasir, dan top produk. Export Excel & PDF untuk pembukuan. Data selalu update.'],
            ];
            $colors=['amber'=>['bg'=>'bg-amber-100','text'=>'text-amber-600'],'emerald'=>['bg'=>'bg-emerald-100','text'=>'text-emerald-600'],'blue'=>['bg'=>'bg-blue-100','text'=>'text-blue-600'],'purple'=>['bg'=>'bg-purple-100','text'=>'text-purple-600'],'rose'=>['bg'=>'bg-rose-100','text'=>'text-rose-600'],'teal'=>['bg'=>'bg-teal-100','text'=>'text-teal-600']];
            @endphp

            @foreach($features as $i => $f)
            @php $c = $colors[$f['color']]; @endphp
            <div class="feature-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 group cursor-default"
                 data-reveal data-delay="{{ $i + 1 }}">
                <div class="{{ $c['bg'] }} w-12 h-12 rounded-2xl flex items-center justify-center mb-4 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                    <svg class="w-6 h-6 {{ $c['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="font-black text-slate-800 mb-2 group-hover:text-amber-600 transition-colors">{{ $f['title'] }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CARA KERJA ───────────────────────────────────────────────────────── --}}
<section id="cara-kerja" class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-16" data-reveal>
            <span class="text-amber-500 text-sm font-bold uppercase tracking-widest">Mudah Dimulai</span>
            <h2 class="text-3xl md:text-4xl font-black text-slate-800 mt-3 mb-4">3 Langkah Mulai Pakai Postera</h2>
            <p class="text-slate-500">Setup selesai dalam 10 menit. Tidak perlu keahlian teknis.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <div class="hidden md:block absolute top-10 left-[20%] right-[20%] h-0.5 bg-gradient-to-r from-amber-200 via-amber-400 to-amber-200"></div>

            @foreach([
                ['01','Daftar & Isi Data Usaha','Buat akun gratis, isi nama usaha, dan setup produk menu dalam hitungan menit.','bg-amber-500'],
                ['02','Setup Menu & Meja','Tambah produk dengan foto, atur kategori, buat nomor meja dan generate QR otomatis.','bg-amber-600'],
                ['03','Langsung Beroperasi','Kasir buka aplikasi, QR meja aktif, terima order dari pelanggan — selesai.','bg-amber-700'],
            ] as $i => [$num,$title,$desc,$bg])
            <div class="step-card text-center relative group cursor-default" data-reveal data-delay="{{ $i + 1 }}">
                <div class="{{ $bg }} step-num w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-amber-200 group-hover:shadow-amber-300">
                    <span class="text-2xl font-black text-white">{{ $num }}</span>
                </div>
                <h3 class="font-black text-slate-800 text-lg mb-2 group-hover:text-amber-600 transition-colors">{{ $title }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── LIVE DEMO ─────────────────────────────────────────────────────────── --}}
<section id="demo" class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center mb-14" data-reveal>
            <span class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-widest px-4 py-1.5 rounded-full mb-4">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                Live Demo Tersedia
            </span>
            <h2 class="text-3xl md:text-4xl font-black text-slate-800 mt-3 mb-4">Coba Langsung Tanpa Daftar</h2>
            <p class="text-slate-500 max-w-lg mx-auto">Akses demo sistem kasir, kitchen display, dan dashboard — semua sudah terisi data contoh. Gratis, langsung pakai.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Demo Kasir --}}
            <div class="feature-card relative bg-gradient-to-br from-amber-950 to-amber-800 rounded-3xl p-7 text-white overflow-hidden group cursor-pointer"
                 data-reveal data-delay="1"
                 onclick="window.open('{{ route('demo') }}?mode=pos', '_blank')">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-amber-500/20 rounded-full blur-2xl group-hover:bg-amber-400/30 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-amber-900/40">
                        <svg class="w-6 h-6 text-amber-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 20h16a1 1 0 001-1V9a1 1 0 00-.293-.707l-5-5A1 1 0 0015 3H4a1 1 0 00-1 1v15a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black mb-2">Demo Kasir POS</h3>
                    <p class="text-amber-200/70 text-sm leading-relaxed mb-6">
                        Coba antarmuka kasir lengkap. Pilih produk, proses pembayaran tunai, lihat struk — persis seperti aslinya.
                    </p>
                    <div class="space-y-2 mb-7">
                        @foreach(['16 produk demo siap pakai','Keranjang & diskon','Checkout tunai & QRIS'] as $f)
                        <div class="flex items-center gap-2 text-xs text-amber-300/80">
                            <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $f }}
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('demo') }}" target="_blank"
                       class="btn-primary inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black px-6 py-3 rounded-2xl text-sm w-full justify-center"
                       onclick="event.stopPropagation()">
                        Buka Demo Kasir
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Demo Kitchen --}}
            <div class="feature-card relative bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-7 text-white overflow-hidden group"
                 data-reveal data-delay="2">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-400/20 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-blue-500 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-blue-900/40">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black mb-2">Demo Kitchen Display</h3>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        Lihat antrian dapur real-time. Update status masak, tandai pesanan siap antar langsung dari layar ini.
                    </p>
                    <div class="space-y-2 mb-7">
                        @foreach(['Antrian 3 kolom: Masuk → Masak → Siap','Update status satu klik','Notifikasi ke kasir otomatis'] as $f)
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <svg class="w-3.5 h-3.5 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $f }}
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('demo') }}?redirect=kitchen" target="_blank"
                       class="btn-primary inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-400 text-white font-black px-6 py-3 rounded-2xl text-sm w-full justify-center">
                        Buka Demo Dapur
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Demo Dashboard --}}
            <div class="feature-card relative bg-gradient-to-br from-emerald-900 to-emerald-800 rounded-3xl p-7 text-white overflow-hidden group"
                 data-reveal data-delay="3">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-emerald-400/10 rounded-full blur-2xl group-hover:bg-emerald-400/20 transition-colors"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-emerald-400 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform shadow-lg shadow-emerald-900/40">
                        <svg class="w-6 h-6 text-emerald-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black mb-2">Demo Dashboard</h3>
                    <p class="text-emerald-200/70 text-sm leading-relaxed mb-6">
                        Jelajahi dashboard pemilik: kelola produk, kategori, meja, laporan penjualan, dan pengaturan usaha.
                    </p>
                    <div class="space-y-2 mb-7">
                        @foreach(['Manajemen produk & kategori','Laporan penjualan + export','Pengaturan cabang & meja'] as $f)
                        <div class="flex items-center gap-2 text-xs text-emerald-300/80">
                            <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $f }}
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('demo') }}?redirect=dashboard" target="_blank"
                       class="btn-primary inline-flex items-center gap-2 bg-emerald-400 hover:bg-emerald-300 text-emerald-950 font-black px-6 py-3 rounded-2xl text-sm w-full justify-center">
                        Buka Demo Dashboard
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-8" data-reveal>
            Demo berjalan di akun bersama — data bisa berubah kapan saja. Daftar untuk akun privat kamu.
        </p>
    </div>
</section>

{{-- ── HARGA ────────────────────────────────────────────────────────────── --}}
<section id="harga" class="py-24 bg-slate-50">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-8" data-reveal>
            <span class="text-amber-500 text-sm font-bold uppercase tracking-widest">Harga Transparan</span>
            <h2 class="text-3xl md:text-4xl font-black text-slate-800 mt-3 mb-4">Pilih Paket yang Sesuai</h2>
            <p class="text-slate-500">Semua paket mulai dengan trial 14 hari gratis. Tanpa kartu kredit.</p>
        </div>

        <div x-data="{ period: 'monthly' }" class="space-y-8">
            <div class="flex justify-center" data-reveal>
                <div class="bg-white border border-slate-200 rounded-2xl p-1 flex gap-1 shadow-sm">
                    <button @click="period='monthly'"
                            :class="period==='monthly'?'bg-amber-500 text-white shadow-sm scale-[1.02]':'text-slate-500 hover:text-slate-700'"
                            class="px-6 py-2 rounded-xl text-sm font-semibold transition-all">Bulanan</button>
                    <button @click="period='yearly'"
                            :class="period==='yearly'?'bg-amber-500 text-white shadow-sm scale-[1.02]':'text-slate-500 hover:text-slate-700'"
                            class="px-6 py-2 rounded-xl text-sm font-semibold transition-all">
                        Tahunan
                        <span class="ml-1.5 text-xs bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full">Hemat 17%</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $i => $plan)
                <div class="price-card relative bg-white rounded-3xl border-2 {{ $plan->slug === 'pro' ? 'border-amber-500 shadow-xl shadow-amber-100' : 'border-slate-100 shadow-sm' }} p-7 flex flex-col"
                     data-reveal data-delay="{{ $i + 1 }}">

                    @if($plan->slug === 'pro')
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-xs font-black px-5 py-1.5 rounded-full shadow-lg">
                        PALING POPULER
                    </div>
                    @endif

                    <div class="mb-5">
                        <h3 class="text-xl font-black text-slate-800">{{ $plan->name }}</h3>
                        <p class="text-slate-500 text-sm mt-1">{{ $plan->description }}</p>
                    </div>

                    <div class="mb-6">
                        <div x-show="period === 'monthly'" class="flex items-end gap-1">
                            <span class="text-4xl font-black text-slate-800">Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}</span>
                            <span class="text-slate-400 text-sm pb-1">/bln</span>
                        </div>
                        <div x-show="period === 'yearly'" x-cloak class="flex items-end gap-1">
                            <span class="text-4xl font-black text-slate-800">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}</span>
                            <span class="text-slate-400 text-sm pb-1">/thn</span>
                        </div>
                        <p class="text-xs text-amber-600 mt-1.5 font-semibold">✓ Trial {{ $plan->trial_days }} hari gratis</p>
                    </div>

                    <ul class="space-y-3 mb-7 flex-1">
                        @foreach([
                            [$plan->max_branches == 999 ? 'Cabang tak terbatas' : $plan->max_branches.' Cabang', true],
                            [$plan->max_users == 999    ? 'Pengguna tak terbatas' : $plan->max_users.' Pengguna', true],
                            [$plan->max_products == 9999? 'Produk tak terbatas' : $plan->max_products.' Produk', true],
                            ['QR Ordering per meja',     $plan->feature_qr_ordering],
                            ['Laporan lanjutan & export',$plan->feature_advanced_reports],
                            ['Multi-device bersamaan',   $plan->feature_multi_device],
                        ] as [$item, $active])
                        <li class="flex items-center gap-3 text-sm {{ $active ? 'text-slate-700' : 'text-slate-300' }}">
                            <svg class="w-4 h-4 shrink-0 {{ $active ? 'text-emerald-500' : 'text-slate-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $active ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}"/>
                            </svg>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('register') }}"
                       class="btn-primary w-full text-center font-black py-3 rounded-2xl text-sm block
                              {{ $plan->slug === 'pro' ? 'bg-amber-500 hover:bg-amber-400 text-white shadow-lg shadow-amber-200' : 'bg-slate-100 hover:bg-amber-50 hover:text-amber-700 text-slate-700' }}">
                        Mulai Trial Gratis
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── FAQ ────────────────────────────────────────────────────────────── --}}
<section id="faq" class="py-20 bg-white">
    <div class="max-w-2xl mx-auto px-6">
        <h2 class="text-3xl font-black text-slate-800 text-center mb-12" data-reveal>Pertanyaan Umum</h2>
        <div x-data="{ open: null }" class="space-y-3">
            @foreach([
                ['Apakah bisa dipakai offline?','Ya! Kasir Postera berbasis PWA. Saat internet mati, transaksi tetap bisa dilakukan dan tersimpan lokal. Begitu online kembali, data otomatis sinkron ke server.'],
                ['Berapa lama trial gratis?','Trial 14 hari gratis untuk semua paket, tanpa input kartu kredit. Setelah trial, pilih paket yang sesuai — data tidak akan hilang.'],
                ['Apakah bisa multi-kasir?','Ya, tergantung paket. Starter: 3 kasir, Pro: 10 kasir, Enterprise: tidak terbatas — semua beroperasi di cabang yang sama secara bersamaan.'],
                ['Bagaimana dengan keamanan data?','Data disimpan di cloud yang ter-enkripsi. Setiap usaha datanya terisolasi sepenuhnya. Backup otomatis harian.'],
                ['Apakah bisa untuk semua jenis F&B?','Postera cocok untuk kafe, restoran, warung makan, bakery, hingga food court. Sistem mendukung varian produk, modifier (topping/level), dan meja per area.'],
            ] as $i => [$q,$a])
            <div class="border border-slate-100 rounded-2xl overflow-hidden hover:border-amber-200 transition-colors" data-reveal data-delay="{{ $i + 1 }}">
                <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-slate-800 hover:bg-amber-50 transition-colors text-sm">
                    {{ $q }}
                    <svg :class="open === {{ $i }} ? 'rotate-180 text-amber-500' : 'text-slate-400'"
                         class="w-4 h-4 transition-all shrink-0 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $i }}" x-cloak
                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-5 pb-4 text-sm text-slate-500 leading-relaxed border-t border-slate-50">
                    {{ $a }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CTA FINAL ────────────────────────────────────────────────────────── --}}
<section class="py-24 bg-gradient-to-br from-amber-950 via-amber-900 to-amber-800 text-center relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-amber-600/10 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-2xl mx-auto px-6" data-reveal>
        <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Siap Modernisasi Bisnis F&B Kamu?</h2>
        <p class="text-amber-200/70 mb-10 text-lg">Mulai trial 14 hari gratis. Setup kurang dari 10 menit. Tidak perlu kartu kredit.</p>
        <a href="{{ route('register') }}"
           class="btn-primary btn-glow inline-flex items-center gap-3 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black px-10 py-4 rounded-2xl text-base shadow-2xl shadow-amber-950/50">
            Daftar Gratis Sekarang
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </a>
        <p class="text-amber-400/50 text-sm mt-6">Bergabung dengan 500+ usaha F&B yang sudah pakai Postera</p>
    </div>
</section>

{{-- ── FOOTER ───────────────────────────────────────────────────────────── --}}
<footer class="bg-slate-900 text-slate-400 py-10">
    <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <img src="{{ asset('icons/logo.png') }}" alt="Postera" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
            <span class="font-black text-white group-hover:text-amber-400 transition-colors">Postera</span>
        </a>
        <p class="text-xs text-slate-600">© {{ date('Y') }} Postera. Kasir digital untuk bisnis F&B modern.</p>
        <div class="flex gap-5 text-sm">
            <a href="{{ route('login') }}"    class="hover:text-amber-400 transition-colors">Masuk</a>
            <a href="{{ route('register') }}" class="hover:text-amber-400 transition-colors font-semibold">Daftar Gratis</a>
        </div>
    </div>
</footer>

<script>
// Scroll-reveal with IntersectionObserver
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('revealed');
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });

document.querySelectorAll('[data-reveal]').forEach(el => observer.observe(el));
</script>
</body>
</html>
