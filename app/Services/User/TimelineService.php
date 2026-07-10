<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\User;
use App\Models\Watchlist;
use App\Services\Movie\MovieService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TimelineService
{
    /** Cache TTL constants (seconds) */
    private const int TTL_COMMUNITY = 3600;

    private const int TTL_FOLLOWING = 300;

    public function __construct(
        private readonly MovieService $movieService,
        private readonly ActivityFeedService $feedService,
    ) {}

    /**
     * Tab "Semua" — campuran trending TMDB + aktivitas komunitas.
     *
     * @return array{trending_movies: array<int, array<string, mixed>>, popular_reviews: Collection<int, Review>, most_watchlisted: array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>, most_reviewed: array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>}
     */
    public function getAllSections(): array
    {
        return Cache::remember('timeline.all', self::TTL_COMMUNITY, function (): array {
            return [
                'trending_movies' => $this->fetchTrendingMovies(),
                'popular_reviews' => $this->fetchPopularReviews(),
                'most_watchlisted' => $this->fetchMostWatchlisted(),
                'most_reviewed' => $this->fetchMostReviewed(),
            ];
        });
    }

    /**
     * Tab "Trending" — hanya data trending film.
     *
     * @return array{trending_movies: array<int, array<string, mixed>>, top_liked_reviews: Collection<int, Review>, most_diary: array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>}
     */
    public function getTrendingSections(): array
    {
        return Cache::remember('timeline.trending', self::TTL_COMMUNITY, function (): array {
            return [
                'trending_movies' => $this->fetchTrendingMovies(),
                'top_liked_reviews' => $this->fetchTopLikedReviews(),
                'most_diary' => $this->fetchMostDiaryThisWeek(),
            ];
        });
    }

    /**
     * Tab "Following" — aktivitas pengguna yang difollow.
     *
     * @return array{feed: Collection<int, array<string, mixed>>, trending_among_following: array<int, array<string, mixed>>}
     */
    public function getFollowingSections(User $user): array
    {
        $cacheKey = "timeline.following.{$user->id}";

        return Cache::remember($cacheKey, self::TTL_FOLLOWING, function () use ($user): array {
            return [
                'feed' => $this->feedService->getEnrichedFeed($user, 40),
                'trending_among_following' => $this->fetchTrendingAmongFollowing($user),
            ];
        });
    }

    // --------------------------------------------------------
    // Private helpers
    // --------------------------------------------------------

    /** @return array<int, array<string, mixed>> */
    private function fetchTrendingMovies(): array
    {
        $sections = $this->movieService->homeMovieSections();
        $trendingSection = collect($sections)->firstWhere('id', 'all-movies');

        return $trendingSection['movies'] ?? [];
    }

    /** @return Collection<int, Review> */
    private function fetchPopularReviews(): Collection
    {
        return Review::with(['user'])
            ->withCount('likes')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('likes_count')
            ->limit(6)
            ->get();
    }

    /** @return Collection<int, Review> */
    private function fetchTopLikedReviews(): Collection
    {
        return Review::with(['user'])
            ->withCount('likes')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('likes_count')
            ->limit(6)
            ->get()
            ->filter(fn (Review $review): bool => $review->likes_count > 0)
            ->values();
    }

    /**
     * @return array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>
     */
    private function fetchMostWatchlisted(): array
    {
        $rows = Watchlist::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('tmdb_id, COUNT(*) as count')
            ->groupBy('tmdb_id')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return $this->enrichMovieRows($rows);
    }

    /**
     * @return array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>
     */
    private function fetchMostReviewed(): array
    {
        $rows = Review::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('tmdb_id, COUNT(*) as count')
            ->groupBy('tmdb_id')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return $this->enrichMovieRows($rows);
    }

    /**
     * @return array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>
     */
    private function fetchMostDiaryThisWeek(): array
    {
        $rows = DiaryEntry::where('created_at', '>=', now()->subDays(7))
            ->selectRaw('tmdb_id, COUNT(*) as count')
            ->groupBy('tmdb_id')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        return $this->enrichMovieRows($rows);
    }

    /** @return array<int, array<string, mixed>> */
    private function fetchTrendingAmongFollowing(User $user): array
    {
        $followingIds = $user->following()->pluck('users.id');

        if ($followingIds->isEmpty()) {
            return [];
        }

        $rows = Watchlist::whereIn('user_id', $followingIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('tmdb_id, COUNT(*) as count')
            ->groupBy('tmdb_id')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $tmdbIds = $rows->pluck('tmdb_id')->all();
        $movieDetails = $this->movieService->findMovies($tmdbIds);

        return $rows
            ->map(fn ($row): ?array => $movieDetails[(int) $row->tmdb_id] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<int, array{tmdb_id: int, count: int, movie: array<string, mixed>|null}>
     */
    private function enrichMovieRows(Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }

        $tmdbIds = $rows->pluck('tmdb_id')->all();
        $movieDetails = $this->movieService->findMovies($tmdbIds);

        return $rows
            ->map(fn ($row): array => [
                'tmdb_id' => (int) $row->tmdb_id,
                'count' => (int) $row->count,
                'movie' => $movieDetails[(int) $row->tmdb_id] ?? null,
            ])
            ->filter(fn (array $row): bool => $row['movie'] !== null)
            ->values()
            ->all();
    }
}
