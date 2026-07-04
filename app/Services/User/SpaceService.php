<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Services\Movie\MovieService;
use App\Services\Movie\MovieTransformer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\LengthAwarePaginator;

class SpaceService
{
    public function __construct(
        private readonly MovieService $movieService,
        private readonly MovieTransformer $transformer,
    ) {}

    /**
     * @return array{total_watched: int, total_diary: int, total_reviews: int, total_watchlist: int, total_favorites: int}
     */
    public function getStats(Authenticatable $user): array
    {
        return [
            'total_watched' => WatchHistory::where('user_id', $user->id)->where('status', 'watched')->count(),
            'total_diary' => DiaryEntry::where('user_id', $user->id)->count(),
            'total_reviews' => Review::where('user_id', $user->id)->count(),
            'total_watchlist' => Watchlist::where('user_id', $user->id)->count(),
            'total_favorites' => Favorite::where('user_id', $user->id)->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentWatched(Authenticatable $user, int $limit = 10): array
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
     * @return array<int, array<string, mixed>>
     */
    public function getWatchlistMovies(Authenticatable $user, int $limit = 20): array
    {
        $tmdbIds = Watchlist::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->pluck('tmdb_id')
            ->all();

        return $this->fetchMovieCards($tmdbIds);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFavoriteMovies(Authenticatable $user, int $limit = 20): array
    {
        $tmdbIds = Favorite::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->pluck('tmdb_id')
            ->all();

        return $this->fetchMovieCards($tmdbIds);
    }

    public function getDiaryEntries(Authenticatable $user, int $perPage = 20): LengthAwarePaginator
    {
        $entries = DiaryEntry::where('user_id', $user->id)
            ->orderByDesc('watched_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // Attach movie info dari TMDB (cache 24 jam per film)
        $tmdbIds = $entries->pluck('tmdb_id')->unique()->all();
        $movieInfoById = [];

        foreach ($tmdbIds as $tmdbId) {
            [$detail] = $this->movieService->findMovie((int) $tmdbId);
            if ($detail !== null) {
                $movieInfoById[$tmdbId] = [
                    'title' => $detail['title'],
                    'poster_url' => $detail['poster_url'] ?? null,
                    'release_year' => $detail['release_year'] ?? null,
                ];
            }
        }

        // Inject ke setiap entry sebagai attribute tambahan
        $entries->each(function (DiaryEntry $entry) use ($movieInfoById): void {
            $info = $movieInfoById[$entry->tmdb_id] ?? null;
            $entry->movie_title = $info['title'] ?? "Film #{$entry->tmdb_id}";
            $entry->movie_poster_url = $info['poster_url'] ?? null;
            $entry->movie_release_year = $info['release_year'] ?? null;
        });

        return $entries;
    }

    public function getWatchHistoryEntries(Authenticatable $user, ?string $status, int $perPage = 20): LengthAwarePaginator
    {
        $entries = WatchHistory::where('user_id', $user->id)
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);

        $tmdbIds = $entries->pluck('tmdb_id')->unique()->all();
        $movieInfoById = [];

        foreach ($tmdbIds as $tmdbId) {
            [$detail] = $this->movieService->findMovie((int) $tmdbId);
            if ($detail !== null) {
                $movieInfoById[$tmdbId] = [
                    'title' => $detail['title'],
                    'poster_url' => $detail['poster_url'] ?? null,
                    'release_year' => $detail['release_year'] ?? null,
                ];
            }
        }

        $entries->each(function (WatchHistory $entry) use ($movieInfoById): void {
            $info = $movieInfoById[$entry->tmdb_id] ?? null;
            $entry->movie_title = $info['title'] ?? "Film #{$entry->tmdb_id}";
            $entry->movie_poster_url = $info['poster_url'] ?? null;
            $entry->movie_release_year = $info['release_year'] ?? null;
        });

        return $entries;
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

        $movies = [];

        foreach ($tmdbIds as $tmdbId) {
            [$detail] = $this->movieService->findMovie($tmdbId);

            if ($detail !== null) {
                $movies[] = $detail;
            }
        }

        return $movies;
    }
}
