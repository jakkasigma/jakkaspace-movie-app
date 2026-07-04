<?php

namespace App\Services\User;

use App\Models\Favorite;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\Watchlist;

class UserActivityService
{
    // ── Watch History ─────────────────────────────────────────────────────────

    public function markAsWatched(User $user, int $tmdbId): WatchHistory
    {
        return WatchHistory::updateOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['status' => 'watched'],
        );
    }

    public function markAsWatching(User $user, int $tmdbId): WatchHistory
    {
        return WatchHistory::updateOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['status' => 'watching'],
        );
    }

    public function markAsDropped(User $user, int $tmdbId): WatchHistory
    {
        return WatchHistory::updateOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['status' => 'dropped'],
        );
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
        return Watchlist::firstOrCreate([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
        ]);
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
        return Favorite::firstOrCreate([
            'user_id' => $user->id,
            'tmdb_id' => $tmdbId,
        ]);
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
