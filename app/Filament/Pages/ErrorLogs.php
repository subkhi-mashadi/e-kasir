<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ErrorLogs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Log Error';

    protected static ?string $title = 'Log Error Aplikasi';

    protected static ?int $navigationSort = 99;

    protected static string $view = 'filament.pages.error-logs';

    public string $search = '';

    public string $level = '';

    protected function logPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    public function getEntries(): array
    {
        if (! File::exists($this->logPath())) {
            return [];
        }

        $content = File::get($this->logPath());

        preg_match_all(
            '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.\w+:|\z)/ms',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $entries = collect($matches)
            ->map(function ($m) {
                $message = trim($m[4]);

                $head  = $message;
                $trace = null;

                if (str_contains($message, "\n[stacktrace]\n")) {
                    [$head, $trace] = explode("\n[stacktrace]\n", $message, 2);
                    $head  = trim($head);
                    $trace = trim($trace);
                }

                $description = $head;
                $context     = null;

                if (preg_match('/(\{.*\})\s*$/s', $head, $jm)) {
                    $decoded = json_decode($jm[1], true);

                    if (json_last_error() === JSON_ERROR_NONE) {
                        $context     = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                        $description = trim(Str::substr($head, 0, Str::length($head) - Str::length($jm[1])));
                    }
                }

                return [
                    'datetime'    => $m[1],
                    'env'         => $m[2],
                    'level'       => strtoupper($m[3]),
                    'summary'     => Str::limit($description, 180),
                    'description' => $description,
                    'context'     => $context,
                    'trace'       => $trace,
                ];
            })
            ->reverse()
            ->values();

        if ($this->level !== '') {
            $entries = $entries->where('level', $this->level);
        }

        if ($this->search !== '') {
            $needle = mb_strtolower($this->search);
            $entries = $entries->filter(
                fn ($e) => str_contains(mb_strtolower($e['description'] . ' ' . $e['context'] . ' ' . $e['trace']), $needle)
            );
        }

        return $entries->take(300)->values()->all();
    }

    public function getLevelsProperty(): array
    {
        return ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];
    }

    public function getLogSizeProperty(): string
    {
        if (! File::exists($this->logPath())) {
            return '0 KB';
        }

        return number_format(File::size($this->logPath()) / 1024, 1) . ' KB';
    }

    public function viewLogAction(): Action
    {
        return Action::make('viewLog')
            ->label('Detail')
            ->link()
            ->modalHeading(fn (array $arguments) => $arguments['meta'] ?? 'Detail Log')
            ->modalContent(function (array $arguments) {
                $entry = $this->getEntries()[$arguments['index']] ?? null;

                return view('filament.pages.error-log-detail', ['entry' => $entry]);
            })
            ->modalWidth(MaxWidth::FourExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelAction(false);
    }

    public function clearLog(): void
    {
        File::put($this->logPath(), '');

        Notification::make()
            ->title('Log berhasil dibersihkan')
            ->success()
            ->send();
    }
}
