<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Check if Gemini API key is configured
        if (config('app.env') !== 'testing') {
            if (empty(config('services.gemini.api_key'))) {
                Log::warning('GEMINI_API_KEY is not configured. AI narrative generation will not work.');
            }
        }
    }
}
