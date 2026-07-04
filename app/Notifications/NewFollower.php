<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewFollower extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $follower,
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
        return [
            'type' => 'follow',
            'actor_id' => $this->follower->id,
            'actor_name' => $this->follower->name,
            'actor_username' => $this->follower->username,
            'actor_avatar' => $this->follower->avatar_url,
        ];
    }
}
