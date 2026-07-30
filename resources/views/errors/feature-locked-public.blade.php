<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur Tidak Tersedia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">

<div class="max-w-4xl w-full">

    {{-- Logo / brand --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2">
            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
            </div>
            <span class="font-bold text-slate-700 text-lg">Postera</span>
        </div>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

        {{-- Banner --}}
        <div class="bg-linear-to-br from-amber-500 to-amber-700 px-6 py-8 text-center relative overflow-hidden">
            <div class="absolute -top-4 -right-4 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-6 -left-6 w-32 h-32 rounded-full bg-white/10"></div>
            <div class="relative">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 mb-3 mx-auto">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white mb-1">QR Ordering</h2>
                <p class="text-amber-100 text-xs">Tidak tersedia saat ini</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-6 text-center">
            <p class="text-slate-600 text-sm leading-relaxed mb-4">
                Maaf, fitur pemesanan via QR tidak aktif untuk restoran ini. Silakan pesan langsung ke kasir.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs text-amber-700 font-medium">Butuh bantuan?</p>
                <p class="text-xs text-amber-600 mt-1">Hubungi staf restoran untuk melakukan pemesanan.</p>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">Powered by Postera</p>
</div>

</body>
</html>
