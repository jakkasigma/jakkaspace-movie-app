<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\WatchHistory;
use App\Services\Movie\MovieService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * Full analytics snapshot for a user's Space page.
     *
     * @return array{
     *     total_watched: int,
     *     total_diary: int,
     *     total_reviews: int,
     *     avg_rating: float|null,
     *     rewatch_count: int,
     *     top_genres: array<int, array{name: string, count: int}>,
     *     most_rewatched: array<int, array<string, mixed>>,
     *     monthly_activity: array<string, int>,
     *     mood_distribution: array<string, int>,
     * }
     */
    public function getAnalytics(Authenticatable $user): array
    {
        $cacheKey = "analytics.{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user): array {
            return [
                'total_watched' => $this->totalWatched($user),
                'total_diary' => $this->totalDiary($user),
                'total_reviews' => $this->totalReviews($user),
                'avg_rating' => $this->avgRating($user),
                'rewatch_count' => $this->rewatchCount($user),
                'top_genres' => $this->topGenres($user),
                'most_rewatched' => $this->mostRewatched($user),
                'monthly_activity' => $this->monthlyActivity($user),
                'mood_distribution' => $this->moodDistribution($user),
            ];
        });
    }

    private function totalWatched(Authenticatable $user): int
    {
        return WatchHistory::where('user_id', $user->id)
            ->where('status', 'watched')
            ->count();
    }

    private function totalDiary(Authenticatable $user): int
    {
        return DiaryEntry::where('user_id', $user->id)->count();
    }

    private function totalReviews(Authenticatable $user): int
    {
        return Review::where('user_id', $user->id)->count();
    }

    private function avgRating(Authenticatable $user): ?float
    {
        $avg = Review::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->avg('rating');

        return $avg !== null ? round((float) $avg, 1) : null;
    }

    private function rewatchCount(Authenticatable $user): int
    {
        return DiaryEntry::where('user_id', $user->id)
            ->where('is_rewatch', true)
            ->count();
    }

    /**
     * Top genres based on watch history (via TMDB genre names cached per tmdb_id).
     * Uses last 50 watched films for speed.
     *
     * @return array<int, array{name: string, count: int}>
     */
    private function topGenres(Authenticatable $user): array
    {
        $tmdbIds = WatchHistory::where('user_id', $user->id)
            ->where('status', 'watched')
            ->latest()
            ->limit(50)
            ->pluck('tmdb_id')
            ->all();

        $genreCounts = [];

        $movieDetails = $this->movieService->findMovies($tmdbIds);

        foreach ($tmdbIds as $tmdbId) {
            $detail = $movieDetails[$tmdbId] ?? null;

            if ($detail === null) {
                continue;
            }

            $genreNames = array_filter(array_map('trim', explode(',', $detail['genres'] ?? '')));

            foreach ($genreNames as $name) {
                $genreCounts[$name] = ($genreCounts[$name] ?? 0) + 1;
            }
        }

        arsort($genreCounts);

        return collect($genreCounts)
            ->take(5)
            ->map(fn (int $count, string $name): array => ['name' => $name, 'count' => $count])
            ->values()
            ->all();
    }

    /**
     * Films that appear more than once in diary (rewatched).
     *
     * @return array<int, array<string, mixed>>
     */
    private function mostRewatched(Authenticatable $user): array
    {
        $rows = DiaryEntry::where('user_id', $user->id)
            ->selectRaw('tmdb_id, COUNT(*) as watch_count')
            ->groupBy('tmdb_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('watch_count')
            ->limit(5)
            ->get();

        $tmdbIds = $rows->pluck('tmdb_id')->all();
        $movieDetails = $this->movieService->findMovies($tmdbIds);

        $result = [];

        foreach ($rows as $row) {
            $detail = $movieDetails[(int) $row->tmdb_id] ?? null;

            if ($detail !== null) {
                $detail['watch_count'] = $row->watch_count;
                $result[] = $detail;
            }
        }

        return $result;
    }

    /**
     * Diary entry counts per month for the last 12 months.
     * Returns ['YYYY-MM' => count, ...] ordered oldest to newest.
     *
     * @return array<string, int>
     */
    private function monthlyActivity(Authenticatable $user): array
    {
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $months[Carbon::now()->subMonths($i)->format('Y-m')] = 0;
        }

        // Use strftime-compatible expression for both MySQL and SQLite
        $driver = config('database.connections.'.config('database.default').'.driver');
        $monthExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', watched_at) as month"
            : "DATE_FORMAT(watched_at, '%Y-%m') as month";

        DiaryEntry::where('user_id', $user->id)
            ->where('watched_at', '>=', Carbon::now()->subMonths(12)->startOfMonth())
            ->selectRaw("{$monthExpr}, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->each(function ($row) use (&$months): void {
                if (isset($months[$row->month])) {
                    $months[$row->month] = (int) $row->count;
                }
            });

        return $months;
    }

    /**
     * Premium-only analytics: rating distribution, streak, estimated hours, favorite director, rating per year.
     *
     * @return array{
     *     rating_distribution: array<int, int>,
     *     streak: int,
     *     estimated_hours: int,
     *     favorite_director: string|null,
     *     rating_per_year: array<string, float>,
     * }
     */
    public function getPremiumAnalytics(Authenticatable $user): array
    {
        return [
            'rating_distribution' => $this->ratingDistribution($user),
            'streak' => $this->currentStreak($user),
            'estimated_hours' => $this->estimatedHours($user),
            'favorite_director' => $this->favoriteDirector($user),
            'rating_per_year' => $this->ratingPerYear($user),
        ];
    }

    private function ratingDistribution(Authenticatable $user): array
    {
        $ratings = array_fill(1, 5, 0);

        Review::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->get()
            ->each(fn ($row) => $ratings[(int) $row->rating] = (int) $row->count);

        return $ratings;
    }

    private function currentStreak(Authenticatable $user): int
    {
        $dates = DiaryEntry::where('user_id', $user->id)
            ->whereNotNull('watched_at')
            ->orderByDesc('watched_at')
            ->pluck('watched_at')
            ->map(fn ($d) => $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 1;
        $today = Carbon::today();

        if ($dates->first() !== $today->format('Y-m-d') && $dates->first() !== $today->subDay()->format('Y-m-d')) {
            return 0;
        }

        for ($i = 1; $i < $dates->count(); $i++) {
            $prev = Carbon::parse($dates[$i - 1]);
            $curr = Carbon::parse($dates[$i]);

            if ($prev->diffInDays($curr) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function estimatedHours(Authenticatable $user): int
    {
        // Approximate: assume average movie runtime ~120 min = 2 hours
        $count = WatchHistory::where('user_id', $user->id)
            ->where('status', 'watched')
            ->count();

        return $count * 2;
    }

    private function favoriteDirector(Authenticatable $user): ?string
    {
        $tmdbIds = DiaryEntry::where('user_id', $user->id)
            ->selectRaw('tmdb_id, COUNT(*) as count')
            ->groupBy('tmdb_id')
            ->orderByDesc('count')
            ->limit(20)
            ->pluck('tmdb_id');

        $directorCounts = [];

        foreach ($tmdbIds as $tmdbId) {
            [$detail] = $this->movieService->findMovie((int) $tmdbId);

            if ($detail !== null && ! empty($detail['credits'])) {
                $director = collect($detail['credits']['crew'] ?? [])
                    ->first(fn ($c) => ($c['job'] ?? '') === 'Director');

                if ($director !== null) {
                    $name = $director['name'] ?? 'Unknown';
                    $directorCounts[$name] = ($directorCounts[$name] ?? 0) + 1;
                }
            }
        }

        arsort($directorCounts);

        return ! empty($directorCounts) ? array_key_first($directorCounts) : null;
    }

    /**
     * @return array<string, float>
     */
    private function ratingPerYear(Authenticatable $user): array
    {
        $driver = config('database.connections.'.config('database.default').'.driver');
        $yearExpr = $driver === 'sqlite'
            ? "strftime('%Y', created_at) as year"
            : "DATE_FORMAT(created_at, '%Y') as year";

        return Review::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->selectRaw("{$yearExpr}, ROUND(AVG(rating), 1) as avg_rating")
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->mapWithKeys(fn ($row): array => [$row->year => (float) $row->avg_rating])
            ->all();
    }

    /**
     * Distribution of moods from diary entries.
     *
     * @return array<string, int>
     */
    private function moodDistribution(Authenticatable $user): array
    {
        return DiaryEntry::where('user_id', $user->id)
            ->whereNotNull('mood')
            ->where('mood', '!=', '')
            ->selectRaw('mood, COUNT(*) as count')
            ->groupBy('mood')
            ->orderByDesc('count')
            ->limit(8)
            ->get()
            ->mapWithKeys(fn ($row): array => [$row->mood => (int) $row->count])
            ->all();
    }
}
