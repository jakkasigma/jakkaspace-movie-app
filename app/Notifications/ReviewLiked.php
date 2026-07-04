<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReviewLiked extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $liker,
        private readonly Review $review,
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
            'type' => 'review_like',
            'actor_id' => $this->liker->id,
            'actor_name' => $this->liker->name,
            'actor_username' => $this->liker->username,
            'actor_avatar' => $this->liker->avatar_url,
            'review_id' => $this->review->id,
            'tmdb_id' => $this->review->tmdb_id,
        ];
    }
}
