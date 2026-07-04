<?php

namespace App\Services\Movie;

use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * Films from same genres, excluding already-watched.
     *
     * @param  array<int, int>  $genreIds
     * @return array<int, array<string, mixed>>
     */
    public function getGenreRecommendations(User $user, array $genreIds, int $excludeTmdbId): array
    {
        if (empty($genreIds)) {
            return [];
        }

        $watchedIds = WatchHistory::where('user_id', $user->id)
            ->pluck('tmdb_id')
            ->all();

        $genreString = implode(',', $genreIds);

        [$data] = $this->movieService->discoverMoviesRaw([
            'with_genres' => $genreString,
            'sort_by' => 'popularity.desc',
            'page' => 1,
        ]);

        return collect($data)
            ->filter(fn (array $m): bool => (int) $m['id'] !== $excludeTmdbId
                && ! in_array((int) $m['id'], $watchedIds, true)
            )
            ->take(10)
            ->values()
            ->all();
    }

    /**
     * Personalised movies based on user's top genres.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPersonalizedMovies(User $user): array
    {
        $historyCount = WatchHistory::where('user_id', $user->id)->count();

        if ($historyCount < 3) {
            return [];
        }

        return Cache::remember("recommendation.{$user->id}.personal", 7200, function () use ($user): array {
            $topGenreIds = $this->getTopGenreIds($user, 3);

            if (empty($topGenreIds)) {
                return [];
            }

            $watchedIds = WatchHistory::where('user_id', $user->id)
                ->pluck('tmdb_id')
                ->all();

            $genreString = implode(',', $topGenreIds);

            [$movies] = $this->movieService->discoverMoviesRaw([
                'with_genres' => $genreString,
                'sort_by' => 'popularity.desc',
                'page' => 1,
            ]);

            return collect($movies)
                ->filter(fn (array $m): bool => ! in_array((int) $m['id'], $watchedIds, true))
                ->take(12)
                ->values()
                ->all();
        });
    }

    /**
     * Films trending among the people the user follows (last 30 days).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTrendingAmongFollowing(User $user): array
    {
        $followingIds = $user->following()->pluck('users.id');

        if ($followingIds->isEmpty()) {
            return [];
        }

        return Cache::remember("recommendation.{$user->id}.following_trending", 3600, function () use ($followingIds): array {
            $topTmdbIds = WatchHistory::whereIn('user_id', $followingIds)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('tmdb_id, COUNT(*) as watch_count')
                ->groupBy('tmdb_id')
                ->orderByDesc('watch_count')
                ->limit(6)
                ->pluck('tmdb_id')
                ->all();

            if (empty($topTmdbIds)) {
                return [];
            }

            $movies = [];

            foreach ($topTmdbIds as $tmdbId) {
                [$detail] = $this->movieService->findMovie((int) $tmdbId);
                if ($detail !== null) {
                    $movies[] = $detail;
                }
            }

            return $movies;
        });
    }

    /**
     * Get top N genre IDs from user's watch history and favorites.
     * Uses raw TMDB listing data to extract genre_ids efficiently.
     *
     * @return array<int, int>
     */
    private function getTopGenreIds(User $user, int $limit): array
    {
        $watchedIds = WatchHistory::where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->pluck('tmdb_id')
            ->all();

        $favoriteIds = $user->favorites()
            ->latest()
            ->limit(10)
            ->pluck('tmdb_id')
            ->all();

        $allIds = array_unique(array_merge($watchedIds, $favoriteIds));
        $genreCounts = [];

        foreach (array_slice($allIds, 0, 20) as $tmdbId) {
            // Fetch detail to get genres array (cached 24h)
            [$detail] = $this->movieService->findMovie((int) $tmdbId);

            if ($detail === null) {
                continue;
            }

            // genres in detail is a comma-separated string — resolve to IDs via genre list
            $genreNames = array_map('trim', explode(',', $detail['genres'] ?? ''));
            $allGenres = $this->movieService->genres();

            foreach ($allGenres as $genre) {
                if (in_array($genre['name'], $genreNames, true)) {
                    $gid = (int) $genre['id'];
                    $genreCounts[$gid] = ($genreCounts[$gid] ?? 0) + 1;
                }
            }
        }

        arsort($genreCounts);

        return array_keys(array_slice($genreCounts, 0, $limit, true));
    }
}
