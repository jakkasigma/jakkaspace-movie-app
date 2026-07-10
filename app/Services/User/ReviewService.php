<?php

namespace App\Services\User;

use App\Models\ActivityLog;
use App\Models\Review;
use App\Models\User;
use App\Services\Movie\MovieService;
use Illuminate\Support\Facades\Cache;

class ReviewService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * @param  array{rating?: int|null, body?: string|null, has_spoiler?: bool}  $data
     */
    public function upsertReview(User $user, int $tmdbId, array $data): Review
    {
        $isNew = ! Review::where('user_id', $user->id)->where('tmdb_id', $tmdbId)->exists();

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

        $detail = $this->movieService->getLocalMovieInfo($tmdbId);
        $title = $detail['title'];

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'review',
            'description' => $isNew ? "Menulis review untuk {$title}" : "Memperbarui review untuk {$title}",
            'metadata' => [
                'tmdb_id' => $tmdbId,
                'movie_title' => $title,
                'poster_url' => $detail['poster_url'],
                'rating' => $review->rating,
                'body' => $review->body,
            ],
            'created_at' => now(),
        ]);

        Cache::forget("movie.community_rating.{$tmdbId}");

        return $review;
    }

    public function deleteReview(Review $review): void
    {
        $review->delete();

        Cache::forget("movie.community_rating.{$review->tmdb_id}");
    }

    public function getUserReview(User $user, int $tmdbId): ?Review
    {
        return Review::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->first();
    }
}
