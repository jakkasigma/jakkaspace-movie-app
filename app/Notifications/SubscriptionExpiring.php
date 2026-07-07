<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionExpiring extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly int $daysLeft,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $renewUrl = route('plus');

        return [
            'type' => 'subscription_expiring',
            'days_left' => $this->daysLeft,
            'expires_at' => $this->user->expires_at?->format('Y-m-d'),
            'renew_url' => $renewUrl,
            'message' => "Subscription Plus kamu akan kedaluwarsa dalam {$this->daysLeft} hari. Perpanjang sekarang!",
        ];
    }
}
