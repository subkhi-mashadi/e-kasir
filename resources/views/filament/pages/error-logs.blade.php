<x-filament-panels::page>
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
        <div class="flex flex-col sm:flex-row gap-3 flex-1">
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari pesan error..."
                class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm sm:max-w-xs"
            >
            <select
                wire:model.live="level"
                class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm sm:max-w-40"
            >
                <option value="">Semua level</option>
                @foreach ($this->levels as $lvl)
                    <option value="{{ $lvl }}">{{ $lvl }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-500 dark:text-gray-400">Ukuran file: {{ $this->logSize }}</span>
            <button
                type="button"
                wire:click="clearLog"
                wire:confirm="Yakin mau bersihkan seluruh isi log? Tindakan ini tidak bisa dibatalkan."
                class="fi-btn fi-btn-size-sm fi-color-danger inline-flex items-center gap-1 rounded-lg bg-danger-600 px-3 py-2 text-sm font-medium text-white hover:bg-danger-500"
            >
                Bersihkan Log
            </button>
        </div>
    </div>

    @php $entries = $this->getEntries(); @endphp

    <div class="mt-4 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2 whitespace-nowrap">Waktu</th>
                        <th class="px-4 py-2 whitespace-nowrap">Level</th>
                        <th class="px-4 py-2">Pesan</th>
                        <th class="px-4 py-2 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                    @forelse ($entries as $i => $entry)
                        @php
                            $badge = match ($entry['level']) {
                                'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400',
                                'WARNING', 'NOTICE' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400',
                                'INFO' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-400',
                                default => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400',
                            };
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $entry['datetime'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-medium {{ $badge }}">{{ $entry['level'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                <div class="truncate max-w-xl font-mono text-xs">{{ $entry['summary'] }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ ($this->viewLogAction)([
                                    'index' => $i,
                                    'meta'  => $entry['datetime'] . ' · ' . $entry['level'],
                                ]) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada log ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-2 text-xs text-gray-400">Menampilkan maksimal 300 entri terbaru dari storage/logs/laravel.log.</p>
</x-filament-panels::page>
