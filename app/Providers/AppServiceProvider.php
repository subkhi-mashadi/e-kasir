<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <--- 1. Tambahkan ini di atas

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->isSecure()) {
            URL::forceScheme('https');
        }
    }
}
