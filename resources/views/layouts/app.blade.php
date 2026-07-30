<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Postera') — Postera</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-100 h-screen overflow-hidden">

<div class="flex h-screen">
    {{-- Sidebar (fixed height, own scroll) --}}
    <aside class="w-64 bg-amber-900 text-white flex-col shrink-0 hidden lg:flex h-screen overflow-y-auto">
        @include('layouts.partials.sidebar')
    </aside>

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
        class="fixed inset-0 bg-black/50 z-20 lg:hidden"></div>
    <aside x-show="sidebarOpen" x-cloak
        class="fixed inset-y-0 left-0 w-64 bg-amber-900 text-white z-30 lg:hidden flex flex-col">
        @include('layouts.partials.sidebar')
    </aside>

    {{-- Main (own scroll) --}}
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        {{-- Topbar --}}
        <header class="bg-white border-b border-slate-200 px-4 py-3 flex items-center gap-3 shrink-0 z-10">
            <button @click="sidebarOpen=!sidebarOpen" class="lg:hidden text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1">
                <h1 class="font-semibold text-slate-800 text-sm">@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-subtitle')
                <p class="text-xs text-slate-400">@yield('page-subtitle')</p>
                @endif
            </div>

            {{-- Branch badge --}}
            @php $currentBranch = \App\Models\Branch::find(session('branch_id') ?? auth()->user()->branch_id); @endphp
            @if($currentBranch)
            <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-medium">
                {{ $currentBranch->name }}
            </span>
            @endif
        </header>

        {{-- Content (scrollable independently) --}}
        <main class="flex-1 overflow-y-auto p-6 @yield('main-class') flex flex-col">
            @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
            @endif
            <div class="flex-1">
                @yield('content')
            </div>
            <p class="mt-8 text-center text-xs text-slate-400">
                Butuh bantuan? Hubungi <a href="mailto:support@postera.id" class="text-amber-600 hover:underline">support@postera.id</a>
            </p>
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
