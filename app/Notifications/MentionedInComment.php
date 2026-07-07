<?php

namespace App\Notifications;

use App\Models\Review;
use App\Models\ReviewComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MentionedInComment extends Notification
{
    use Queueable;

    public function __construct(
        public User $mentionedBy,
        public ReviewComment $comment,
        public Review $review,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'mention',
            'actor_id' => $this->mentionedBy->id,
            'actor_name' => $this->mentionedBy->name,
            'actor_username' => $this->mentionedBy->username,
            'actor_avatar' => $this->mentionedBy->avatar_url,
            'review_id' => $this->review->id,
            'tmdb_id' => $this->review->tmdb_id,
            'comment_preview' => Str::limit($this->comment->body, 80),
        ];
    }
}
