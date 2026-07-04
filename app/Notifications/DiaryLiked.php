<?php

namespace App\Notifications;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiaryLiked extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $liker,
        private readonly DiaryEntry $entry,
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
            'type' => 'diary_like',
            'actor_id' => $this->liker->id,
            'actor_name' => $this->liker->name,
            'actor_username' => $this->liker->username,
            'actor_avatar' => $this->liker->avatar_url,
            'diary_entry_id' => $this->entry->id,
            'tmdb_id' => $this->entry->tmdb_id,
        ];
    }
}
