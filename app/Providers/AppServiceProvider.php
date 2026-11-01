<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Illuminate\Foundation\Vite::class, \App\Services\SafeVite::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Skema https ditentukan per request oleh middleware ForceHttpsForAppUrl.

        // Aksi Livewire (/livewire/update) ikut dicek ulang: admin yang dicabut aksesnya tidak bisa lanjut.
        \Livewire\Livewire::addPersistentMiddleware([\App\Http\Middleware\IsAdmin::class]);
    }
}
