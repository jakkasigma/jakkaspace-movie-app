<?php

namespace App\Services\User;

use App\Models\MovieList;
use App\Models\User;
use App\Services\Movie\MovieService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProfileService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    /**
     * @return array{
     *     total_watched: int,
     *     total_reviews: int,
     *     total_lists: int,
     *     total_followers: int,
     *     total_following: int,
     * }
     */
    public function getStats(User $profile): array
    {
        return [
            'total_watched' => $profile->watchHistories()->count(),
            'total_reviews' => $profile->reviews()->count(),
            'total_lists' => $profile->movieLists()->where('is_public', true)->count(),
            'total_followers' => $profile->followers()->count(),
            'total_following' => $profile->following()->count(),
        ];
    }

    public function getPublicDiary(User $profile): LengthAwarePaginator
    {
        return $profile->diaryEntries()
            ->latest('watched_at')
            ->paginate(20);
    }

    public function getPublicReviews(User $profile): LengthAwarePaginator
    {
        return $profile->reviews()
            ->withCount('likes')
            ->latest()
            ->paginate(15);
    }

    /**
     * @return Collection<int, MovieList>
     */
    public function getPublicLists(User $profile): Collection
    {
        return $profile->movieLists()
            ->where('is_public', true)
            ->withCount('listMovies')
            ->latest()
            ->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFavoritedMovies(User $profile): array
    {
        return Cache::remember("profile.{$profile->id}.favorites", 600, function () use ($profile): array {
            $tmdbIds = $profile->favorites()
                ->latest()
                ->limit(24)
                ->pluck('tmdb_id')
                ->all();

            return $this->fetchMovies($tmdbIds);
        });
    }

    /**
     * Reviews tab: return movie info + review id + review rating per film.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getReviewedMovies(User $profile): array
    {
        return Cache::remember("profile.{$profile->id}.reviews", 600, function () use ($profile): array {
            $reviews = $profile->reviews()
                ->withCount('likes')
                ->latest()
                ->limit(24)
                ->get();

            $tmdbIds = $reviews->pluck('tmdb_id')->all();
            $movieDetails = $this->movieService->findMovies($tmdbIds);

            $movies = [];

            foreach ($reviews as $review) {
                $detail = $movieDetails[(int) $review->tmdb_id] ?? null;
                if ($detail !== null) {
                    $movies[] = array_merge($detail, [
                        'review_id' => $review->id,
                        'review_rating' => $review->rating,
                        'review_likes' => $review->likes_count,
                    ]);
                }
            }

            return $movies;
        });
    }

    /**
     * @param  array<int, int>  $tmdbIds
     * @return array<int, array<string, mixed>>
     */
    private function fetchMovies(array $tmdbIds): array
    {
        $movieDetails = $this->movieService->findMovies($tmdbIds);

        $movies = [];

        foreach ($tmdbIds as $tmdbId) {
            $detail = $movieDetails[$tmdbId] ?? null;
            if ($detail !== null) {
                $movies[] = $detail;
            }
        }

        return $movies;
    }
}
