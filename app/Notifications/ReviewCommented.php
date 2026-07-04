<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ReviewCommented extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $commenter,
        private readonly Review $review,
        private readonly string $body,
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
            'type' => 'review_comment',
            'actor_id' => $this->commenter->id,
            'actor_name' => $this->commenter->name,
            'actor_username' => $this->commenter->username,
            'actor_avatar' => $this->commenter->avatar_url,
            'review_id' => $this->review->id,
            'comment_preview' => Str::limit($this->body, 80),
            'tmdb_id' => $this->review->tmdb_id,
        ];
    }
}
