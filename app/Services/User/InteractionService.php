<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\DiaryLike;
use App\Models\Review;
use App\Models\ReviewComment;
use App\Models\ReviewLike;
use App\Models\User;
use App\Notifications\DiaryLiked;
use App\Notifications\MentionedInComment;
use App\Notifications\ReviewCommented;
use App\Notifications\ReviewLiked;

class InteractionService
{
    public function likeReview(User $user, Review $review): void
    {
        ReviewLike::firstOrCreate([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // Notify review owner, not self
        if ($review->user_id !== $user->id) {
            $review->loadMissing('user');
            $review->user->notify(new ReviewLiked($user, $review));
        }
    }

    public function unlikeReview(User $user, Review $review): void
    {
        ReviewLike::where('user_id', $user->id)
            ->where('review_id', $review->id)
            ->delete();
    }

    public function isReviewLiked(User $user, Review $review): bool
    {
        return ReviewLike::where('user_id', $user->id)
            ->where('review_id', $review->id)
            ->exists();
    }

    public function likeDiary(User $user, DiaryEntry $entry): void
    {
        DiaryLike::firstOrCreate([
            'user_id' => $user->id,
            'diary_entry_id' => $entry->id,
        ]);

        // Notify diary owner, not self
        if ($entry->user_id !== $user->id) {
            $entry->loadMissing('user');
            $entry->user->notify(new DiaryLiked($user, $entry));
        }
    }

    public function unlikeDiary(User $user, DiaryEntry $entry): void
    {
        DiaryLike::where('user_id', $user->id)
            ->where('diary_entry_id', $entry->id)
            ->delete();
    }

    public function isDiaryLiked(User $user, DiaryEntry $entry): bool
    {
        return DiaryLike::where('user_id', $user->id)
            ->where('diary_entry_id', $entry->id)
            ->exists();
    }

    public function addComment(User $user, Review $review, string $body, ?int $parentId = null): ReviewComment
    {
        $comment = ReviewComment::create([
            'user_id' => $user->id,
            'review_id' => $review->id,
            'parent_id' => $parentId,
            'body' => $body,
        ]);

        // Notify review owner, not self
        if ($review->user_id !== $user->id) {
            $review->loadMissing('user');
            $review->user->notify(new ReviewCommented($user, $review, $body));
        }

        // Extract and notify mentioned users
        preg_match_all('/\B@(\w+)\b/', $body, $matches);
        if (! empty($matches[1])) {
            $mentionedUsernames = array_unique($matches[1]);
            $mentionedUsers = User::whereIn('username', $mentionedUsernames)
                ->where('id', '!=', $user->id) // Don't notify self
                ->get();

            foreach ($mentionedUsers as $mentionedUser) {
                $mentionedUser->notify(new MentionedInComment($user, $comment, $review));
            }
        }

        return $comment;
    }

    public function deleteComment(User $user, ReviewComment $comment): void
    {
        abort_unless($comment->user_id === $user->id, 403);

        $comment->delete();
    }
}
