<?php

namespace App\Services\User;

use App\Models\Review;
use App\Models\User;

class ReviewService
{
    /**
     * @param  array{rating?: int|null, body?: string|null, has_spoiler?: bool}  $data
     */
    public function upsertReview(User $user, int $tmdbId, array $data): Review
    {
        $review = Review::firstOrNew([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
        ]);

        $review->fill([
            'rating' => $data['rating'] ?? null,
            'body' => $data['body'] ?? null,
            'has_spoiler' => $data['has_spoiler'] ?? false,
        ]);

        $review->save();

        return $review;
    }

    public function deleteReview(Review $review): void
    {
        $review->delete();
    }

    public function getUserReview(User $user, int $tmdbId): ?Review
    {
        return Review::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->first();
    }
}
