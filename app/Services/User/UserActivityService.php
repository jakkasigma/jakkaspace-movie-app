<?php

namespace App\Services\User;

use App\Models\ActivityLog;
use App\Models\Favorite;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Services\Movie\MovieService;

class UserActivityService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * @return array{title: string, poster_url: string|null}
     */
    private function getMovieInfo(int $tmdbId): array
    {
        return $this->movieService->getLocalMovieInfo($tmdbId);
    }

    // ── Watch History ─────────────────────────────────────────────────────────

    public function markAsWatched(User $user, int $tmdbId): WatchHistory
    {
        $result = WatchHistory::updateOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['status' => 'watched'],
        );

        $info = $this->getMovieInfo($tmdbId);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'watch_status',
            'description' => "Menonton {$info['title']}",
            'metadata' => ['tmdb_id' => $tmdbId, 'movie_title' => $info['title'], 'poster_url' => $info['poster_url'], 'status' => 'watched'],
            'created_at' => now(),
        ]);

        return $result;
    }

    public function markAsWatching(User $user, int $tmdbId): WatchHistory
    {
        $result = WatchHistory::updateOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['status' => 'watching'],
        );

        $info = $this->getMovieInfo($tmdbId);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'watch_status',
            'description' => "Menandai sedang menonton {$info['title']}",
            'metadata' => ['tmdb_id' => $tmdbId, 'movie_title' => $info['title'], 'poster_url' => $info['poster_url'], 'status' => 'watching'],
            'created_at' => now(),
        ]);

        return $result;
    }

    public function markAsDropped(User $user, int $tmdbId): WatchHistory
    {
        $result = WatchHistory::updateOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['status' => 'dropped'],
        );

        $info = $this->getMovieInfo($tmdbId);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'watch_status',
            'description' => "Menghentikan {$info['title']}",
            'metadata' => ['tmdb_id' => $tmdbId, 'movie_title' => $info['title'], 'poster_url' => $info['poster_url'], 'status' => 'dropped'],
            'created_at' => now(),
        ]);

        return $result;
    }

    public function removeFromHistory(User $user, int $tmdbId): void
    {
        WatchHistory::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->delete();
    }

    public function getWatchStatus(User $user, int $tmdbId): ?string
    {
        return WatchHistory::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->value('status');
    }

    // ── Watchlist ─────────────────────────────────────────────────────────────

    public function addToWatchlist(User $user, int $tmdbId): Watchlist
    {
        $result = Watchlist::firstOrCreate([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
        ]);

        $info = $this->getMovieInfo($tmdbId);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'watchlist',
            'description' => "Menambahkan {$info['title']} ke Watchlist",
            'metadata' => ['tmdb_id' => $tmdbId, 'movie_title' => $info['title'], 'poster_url' => $info['poster_url']],
            'created_at' => now(),
        ]);

        return $result;
    }

    public function removeFromWatchlist(User $user, int $tmdbId): void
    {
        Watchlist::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->delete();
    }

    public function isOnWatchlist(User $user, int $tmdbId): bool
    {
        return Watchlist::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->exists();
    }

    // ── Favorites ─────────────────────────────────────────────────────────────

    public function addToFavorites(User $user, int $tmdbId): Favorite
    {
        $result = Favorite::firstOrCreate([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
        ]);

        $info = $this->getMovieInfo($tmdbId);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'favorite',
            'description' => "Menambahkan {$info['title']} ke Favorit",
            'metadata' => ['tmdb_id' => $tmdbId, 'movie_title' => $info['title'], 'poster_url' => $info['poster_url']],
            'created_at' => now(),
        ]);

        return $result;
    }

    public function removeFromFavorites(User $user, int $tmdbId): void
    {
        Favorite::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->delete();
    }

    public function isFavorited(User $user, int $tmdbId): bool
    {
        return Favorite::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->exists();
    }
}
