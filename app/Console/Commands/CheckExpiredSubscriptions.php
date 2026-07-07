<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'plus:check-expired';

    protected $description = 'Downgrade expired Plus subscriptions and warn expiring soon';

    public function handle(): void
    {
        $downgraded = User::where('subscription_tier', 'plus')
            ->where('expires_at', '<', now())
            ->update(['subscription_tier' => 'free', 'theme_id' => null]);

        if ($downgraded > 0) {
            $this->info("{$downgraded} expired subscription(s) downgraded.");
        }

        $expiringSoon = User::where('subscription_tier', 'plus')
            ->where('expires_at', '>=', now())
            ->where('expires_at', '<=', now()->addDays(7))
            ->get();

        foreach ($expiringSoon as $user) {
            $daysLeft = (int) now()->diffInDays($user->expires_at, false);
            $user->notify(new SubscriptionExpiring($user, $daysLeft));
            $this->line("Notified {$user->name} — {$daysLeft} day(s) left.");
        }

        $this->info('Check complete.');
    }
}
