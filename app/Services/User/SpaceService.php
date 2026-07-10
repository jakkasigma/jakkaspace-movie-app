<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Services\Movie\MovieService;
use App\Services\Movie\MovieTransformer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SpaceService
{
    public function __construct(
        private readonly MovieService $movieService,
        private readonly MovieTransformer $transformer,
    ) {}

    /**
     * @return array{total_watched: int, total_diary: int, total_reviews: int, total_watchlist: int, total_favorites: int, estimated_hours: int}
     */
    public function getStats(User $user): array
    {
        return Cache::remember("stats.{$user->id}", 300, function () use ($user): array {
            $totalWatched = WatchHistory::where('user_id', $user->id)->where('status', 'watched')->count();

            return [
                'total_watched' => $totalWatched,
                'total_diary' => DiaryEntry::where('user_id', $user->id)->count(),
                'total_reviews' => Review::where('user_id', $user->id)->count(),
                'total_watchlist' => Watchlist::where('user_id', $user->id)->count(),
                'total_favorites' => Favorite::where('user_id', $user->id)->count(),
                'estimated_hours' => $totalWatched * 2,
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentWatched(User $user, int $limit = 10): array
    {
        $tmdbIds = WatchHistory::where('user_id', $user->id)
            ->where('status', 'watched')
            ->latest()
            ->limit($limit)
            ->pluck('tmdb_id')
            ->all();

        return $this->fetchMovieCards($tmdbIds);
    }

    /**
     * @return Collection<int, DiaryEntry>
     */
    public function getRecentDiaryEntries(User $user, int $limit = 5): Collection
    {
        $entries = DiaryEntry::where('user_id', $user->id)
            ->orderByDesc('watched_at')
            ->limit($limit)
            ->get();

        $this->attachMovieInfoForDiary($entries, $user);

        return $entries;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getRecentReviews(User $user, int $limit = 5): Collection
    {
        $reviews = Review::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $this->attachMovieInfoForReviews($reviews);

        return $reviews;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getWatchlistMovies(User $user, int $limit = 20): array
    {
        $tmdbIds = Watchlist::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->pluck('tmdb_id')
            ->all();

        return $this->fetchMovieCards($tmdbIds);
    }

    /**
     * @return array{count: int, avg_rating: float|null}
     */
    public function getWatchlistInfo(User $user): array
    {
        $count = Watchlist::where('user_id', $user->id)->count();

        $tmdbIds = Watchlist::where('user_id', $user->id)->pluck('tmdb_id')->all();

        $avgRating = null;
        if (! empty($tmdbIds)) {
            $avgRating = Review::where('user_id', $user->id)
                ->whereIn('tmdb_id', $tmdbIds)
                ->whereNotNull('rating')
                ->avg('rating');
            $avgRating = $avgRating !== null ? round((float) $avgRating, 1) : null;
        }

        return [
            'count' => $count,
            'avg_rating' => $avgRating,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFavoriteMovies(User $user, int $limit = 20): array
    {
        $tmdbIds = Favorite::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->pluck('tmdb_id')
            ->all();

        return $this->fetchMovieCards($tmdbIds);
    }

    /**
     * @return array{count: int}
     */
    public function getFavoritesInfo(User $user): array
    {
        return [
            'count' => Favorite::where('user_id', $user->id)->count(),
        ];
    }

    /**
     * @return array{entries: LengthAwarePaginator, yearOptions: array<int, int>}
     */
    public function getDiaryEntries(User $user, ?string $year = null, string $sort = 'newest', int $perPage = 20): array
    {
        $query = DiaryEntry::where('user_id', $user->id);

        if ($year !== null) {
            $query->whereYear('watched_at', $year);
        }

        $query->when($sort === 'oldest', fn ($q) => $q->orderBy('watched_at')->orderBy('created_at'))
            ->when($sort === 'newest', fn ($q) => $q->orderByDesc('watched_at')->orderByDesc('created_at'));

        $entries = $query->paginate($perPage);

        $this->attachMovieInfoForDiary($entries, $user);

        $yearRange = DiaryEntry::where('user_id', $user->id)
            ->orderByDesc('watched_at')
            ->pluck('watched_at')
            ->map(fn ($d) => $d instanceof Carbon ? $d->year : Carbon::parse($d)->year)
            ->unique()
            ->values()
            ->all();

        return [
            'entries' => $entries,
            'yearOptions' => $yearRange,
        ];
    }

    /**
     * @return array{entries: LengthAwarePaginator, yearOptions: array<int, int>}
     */
    public function getWatchHistoryEntries(User $user, ?string $status = null, int $perPage = 20): array
    {
        $query = WatchHistory::where('user_id', $user->id)
            ->when($status !== null, fn ($q) => $q->where('status', $status));

        $entries = $query->clone()->latest()->paginate($perPage);

        $this->attachMovieInfoForWatchHistory($entries, $user);

        $yearRange = $query->clone()
            ->orderByDesc('created_at')
            ->pluck('created_at')
            ->map(fn ($d) => $d instanceof Carbon ? $d->year : Carbon::parse($d)->year)
            ->unique()
            ->values()
            ->all();

        return [
            'entries' => $entries,
            'yearOptions' => $yearRange,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function getDiarySummaryStats(User $user): array
    {
        return Cache::remember("diary_summary.{$user->id}", 300, function () use ($user): array {
            $totalEntries = DiaryEntry::where('user_id', $user->id)->count();

            $monthlyAvg = 0;
            if ($totalEntries > 0) {
                $firstDate = DiaryEntry::where('user_id', $user->id)->min('watched_at');
                if ($firstDate !== null) {
                    $monthsActive = max(1, now()->diffInMonths($firstDate) + 1);
                    $monthlyAvg = (int) round($totalEntries / $monthsActive);
                }
            }

            return [
                'total_entries' => $totalEntries,
                'monthly_avg' => $monthlyAvg,
            ];
        });
    }

    /**
     * @return array{total_hours: int}
     */
    public function getHistorySummaryStats(User $user): array
    {
        $count = WatchHistory::where('user_id', $user->id)
            ->where('status', 'watched')
            ->count();

        return [
            'total_hours' => $count * 2,
        ];
    }

    /**
     * @param  Collection<int, DiaryEntry>|LengthAwarePaginator  $entries
     */
    private function attachMovieInfoForDiary(Collection|LengthAwarePaginator $entries, User $user): void
    {
        $tmdbIds = $entries->pluck('tmdb_id')->unique()->all();
        $movieInfoById = $this->fetchMovieInfoBatch($tmdbIds);

        $ratingsByTmdb = Review::where('user_id', $user->id)
            ->whereIn('tmdb_id', $tmdbIds)
            ->whereNotNull('rating')
            ->pluck('rating', 'tmdb_id')
            ->all();

        $entries->each(function (DiaryEntry $entry) use ($movieInfoById, $ratingsByTmdb): void {
            $info = $movieInfoById[$entry->tmdb_id] ?? null;

            $title = $info['title'] ?? "Film #{$entry->tmdb_id}";

            if ($entry->getOriginal('movie_title') === null && $info !== null) {
                $entry->timestamps = false;
                $entry->updateQuietly(['movie_title' => $title]);
            }

            $entry->movie_title = $title;
            $entry->movie_poster_url = $info['poster_url'] ?? null;
            $entry->movie_release_year = $info['release_year'] ?? null;
            $entry->user_rating = $ratingsByTmdb[$entry->tmdb_id] ?? null;
        });
    }

    /**
     * @param  Collection<int, Review>  $reviews
     */
    private function attachMovieInfoForReviews(Collection $reviews): void
    {
        $tmdbIds = $reviews->pluck('tmdb_id')->unique()->all();
        $movieInfoById = $this->fetchMovieInfoBatch($tmdbIds);

        $reviews->each(function (Review $review) use ($movieInfoById): void {
            $info = $movieInfoById[$review->tmdb_id] ?? null;
            $review->movie_title = $info['title'] ?? "Film #{$review->tmdb_id}";
            $review->movie_poster_url = $info['poster_url'] ?? null;
        });
    }

    /**
     * @param  Collection<int, WatchHistory>|LengthAwarePaginator  $entries
     */
    private function attachMovieInfoForWatchHistory(Collection|LengthAwarePaginator $entries, User $user): void
    {
        $tmdbIds = $entries->pluck('tmdb_id')->unique()->all();
        $movieInfoById = $this->fetchMovieInfoBatch($tmdbIds);

        $ratingsByTmdb = Review::where('user_id', $user->id)
            ->whereIn('tmdb_id', $tmdbIds)
            ->whereNotNull('rating')
            ->pluck('rating', 'tmdb_id')
            ->all();

        $entries->each(function (WatchHistory $entry) use ($movieInfoById, $ratingsByTmdb): void {
            $info = $movieInfoById[$entry->tmdb_id] ?? null;
            $entry->movie_title = $info['title'] ?? "Film #{$entry->tmdb_id}";
            $entry->movie_poster_url = $info['poster_url'] ?? null;
            $entry->movie_release_year = $info['release_year'] ?? null;
            $entry->user_rating = $ratingsByTmdb[$entry->tmdb_id] ?? null;
        });
    }

    /**
     * @param  array<int, int>  $tmdbIds
     * @return array<int, array{title: string, poster_url: string|null, release_year: int|null}>
     */
    private function fetchMovieInfoBatch(array $tmdbIds): array
    {
        if (empty($tmdbIds)) {
            return [];
        }

        $movieDetails = $this->movieService->findMovies($tmdbIds);

        $info = [];

        foreach ($tmdbIds as $tmdbId) {
            $detail = $movieDetails[$tmdbId] ?? null;
            if ($detail !== null) {
                $info[$tmdbId] = [
                    'title' => $detail['title'],
                    'poster_url' => $detail['poster_url'] ?? null,
                    'release_year' => $detail['release_year'] ?? null,
                ];
            }
        }

        return $info;
    }

    /**
     * @param  array<int, int>  $tmdbIds
     * @return array<int, array<string, mixed>>
     */
    private function fetchMovieCards(array $tmdbIds): array
    {
        if (empty($tmdbIds)) {
            return [];
        }

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
