<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\User\InboxService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->registerPolicies();

        View::composer('components.movie.navbar', function (\Illuminate\View\View $view): void {
            $user = auth()->user();

            $unreadCount = $user !== null
                ? $user->unreadNotifications()->count()
                : 0;

            $inboxUnreadCount = $user !== null
                ? app(InboxService::class)->getUnreadCount($user)
                : 0;

            $view->with('unreadCount', $unreadCount)
                ->with('inboxUnreadCount', $inboxUnreadCount);
        });
    }
}
