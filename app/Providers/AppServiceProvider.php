<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('components.movie.navbar', function (\Illuminate\View\View $view): void {
            $unreadCount = auth()->check()
                ? auth()->user()->unreadNotifications()->count()
                : 0;

            $view->with('unreadCount', $unreadCount);
        });
    }
}
