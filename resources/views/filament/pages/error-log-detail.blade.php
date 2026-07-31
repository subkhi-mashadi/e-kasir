@if ($entry)
    <div class="space-y-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Pesan</p>
            <p class="whitespace-pre-wrap wrap-break-word text-sm text-gray-800 dark:text-gray-100">{{ $entry['description'] }}</p>
        </div>

        @if ($entry['context'])
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1.5">Context</p>
                <pre class="whitespace-pre-wrap break-all font-mono text-xs leading-relaxed rounded-lg bg-gray-50 dark:bg-black/40 text-gray-700 dark:text-gray-200 p-3 max-h-56 overflow-y-auto">{{ $entry['context'] }}</pre>
            </div>
        @endif

        @if ($entry['trace'])
            <div x-data="{ showTrace: false }">
                <button
                    type="button"
                    @click="showTrace = !showTrace"
                    class="text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 mb-1.5 inline-flex items-center gap-1"
                >
                    <span x-text="showTrace ? 'Sembunyikan Stack Trace' : 'Tampilkan Stack Trace'"></span>
                    <svg :class="showTrace ? 'rotate-180' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <pre
                    x-show="showTrace"
                    x-cloak
                    class="whitespace-pre-wrap break-all font-mono text-xs leading-relaxed rounded-lg bg-gray-950 text-gray-200 p-3 max-h-72 overflow-y-auto"
                >{{ $entry['trace'] }}</pre>
            </div>
        @endif
    </div>
@else
    <p class="text-sm text-gray-400">Entri log tidak ditemukan (mungkin sudah dibersihkan).</p>
@endif
