@php
$routeName = request()->route()?->getName() ?? '';
$is = fn($pattern) => str_starts_with($routeName, $pattern);
$linkClass = fn($pattern) => 'flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-colors ' .
    ($is($pattern)
        ? 'bg-amber-500 text-white shadow-md shadow-amber-900/40'
        : 'text-amber-100/80 hover:bg-amber-800/50 hover:text-amber-50');
@endphp

{{-- Brand / user --}}
<div class="flex items-center gap-3 px-5 py-4 border-b border-amber-800/60">
    <div class="w-12 h-12 rounded-xl shrink-0 overflow-hidden bg-white/10">
        <img src="{{ asset('icons/logo.png') }}" alt="Postera" class="w-full h-full object-contain">
    </div>
    <div class="min-w-0">
        <div class="font-bold text-sm truncate text-amber-50">{{ auth()->user()->company?->name ?? 'Postera' }}</div>
        <div class="text-xs text-amber-300/70 truncate">{{ auth()->user()->name }}</div>
    </div>
</div>

<nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5">

    {{-- Utama --}}
    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 pt-2 pb-1">Utama</p>

    <a href="{{ route('app.dashboard') }}" class="{{ $linkClass('app.dashboard') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Dashboard
    </a>

    {{-- Kasir --}}
    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 pt-4 pb-1">Kasir</p>

    <a href="{{ route('pos.index') }}" class="{{ $linkClass('app.pos') }}" target="_blank">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 20h16a1 1 0 001-1V9a1 1 0 00-.293-.707l-5-5A1 1 0 0015 3H4a1 1 0 00-1 1v15a1 1 0 001 1z"/>
        </svg>
        Kasir (POS)
    </a>

    <a href="{{ route('app.orders.index') }}" class="{{ $linkClass('app.orders') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        Riwayat Order
    </a>

    {{-- Menu & Stok --}}
    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 pt-4 pb-1">Menu & Stok</p>

    <a href="{{ route('app.products.index') }}" class="{{ $linkClass('app.products') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        Produk
    </a>

    <a href="{{ route('app.categories.index') }}" class="{{ $linkClass('app.categories') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        Kategori
    </a>

    <a href="{{ route('app.modifiers.index') }}" class="{{ $linkClass('app.modifiers') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
        Modifier
    </a>

    <a href="{{ route('app.inventory.index') }}" class="{{ $linkClass('app.inventory') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        Stok
    </a>

    {{-- Operasional --}}
    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 pt-4 pb-1">Operasional</p>

    <a href="{{ route('app.tables.index') }}" class="{{ $linkClass('app.tables') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
        </svg>
        Meja & QR
    </a>

    <a href="{{ route('app.customers.index') }}" class="{{ $linkClass('app.customers') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Pelanggan
    </a>

    @can('view_branches')
    <a href="{{ route('app.branches.index') }}" class="{{ $linkClass('app.branches') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        Cabang
    </a>
    @endcan

    {{-- Pengaturan --}}
    @role('owner')
    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 pt-4 pb-1">Pengaturan</p>

    <a href="{{ route('app.staff.index') }}" class="{{ $linkClass('app.staff') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Tim Staf
    </a>

    @unless(session('is_demo'))
    <a href="{{ route('app.settings.payment') }}" class="{{ $linkClass('app.settings') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        Pembayaran
    </a>
    @endunless
    @endrole

    {{-- Laporan --}}
    <p class="text-xs text-amber-400/60 uppercase tracking-wider px-3 pt-4 pb-1">Laporan</p>

    <a href="{{ route('app.reports.sales') }}" class="{{ $linkClass('app.reports.sales') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Laporan Penjualan
    </a>

    <a href="{{ route('app.reports.per-kasir') }}" class="{{ $linkClass('app.reports.per-kasir') }}">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Laporan Per Kasir
    </a>

</nav>

{{-- Footer --}}
<div class="px-3 py-4 border-t border-amber-800/60 space-y-1">
    {{-- Branch switcher for owner --}}
    @if(auth()->user()->hasRole('owner') && \App\Models\Branch::where('company_id', auth()->user()->company_id)->count() > 1)
    <div x-data="{ open: false }" class="relative">
        <button @click="open=!open"
            class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-amber-200/70 hover:bg-amber-800/50 hover:text-amber-50 w-full transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Ganti Cabang
        </button>
        <div x-show="open" @click.outside="open=false" x-cloak
            class="absolute bottom-full left-0 mb-1 w-52 bg-white rounded-xl shadow-xl border border-slate-100 py-1 z-50">
            @foreach(\App\Models\Branch::where('company_id', auth()->user()->company_id)->where('is_active', true)->get() as $b)
            <form method="POST" action="{{ route('branch.change') }}">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $b->id }}">
                <button type="submit"
                    class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-700
                        {{ session('branch_id') == $b->id ? 'font-semibold text-amber-600' : '' }}">
                    {{ $b->name }}
                </button>
            </form>
            @endforeach
        </div>
    </div>
    @endif

    @php $sub = auth()->user()->company?->subscription; @endphp
    @if ($sub && $sub->status === 'trial')
    <a href="{{ route('subscription.billing') }}"
       class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-amber-200/60 hover:bg-amber-800/50 w-full transition-colors">
        <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
        Trial — berakhir {{ $sub->trial_ends_at?->diffForHumans() }}
    </a>
    @elseif ($sub && $sub->status === 'expired')
    <a href="{{ route('subscription.billing') }}"
       class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs text-red-300 hover:bg-red-900/30 w-full transition-colors">
        <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
        Langganan Berakhir
    </a>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-amber-300/60 hover:bg-amber-800/50 hover:text-amber-50 w-full transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Keluar
        </button>
    </form>
</div>
