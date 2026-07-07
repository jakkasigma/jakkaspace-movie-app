<?php

use App\Models\Favorite;
use App\Models\User;
use App\Models\WatchHistory;
use App\Models\Watchlist;
use App\Services\User\UserActivityService;

beforeEach(function (): void {
    $this->service = app(UserActivityService::class);
    $this->user = User::factory()->create();
});

describe('watch history', function (): void {
    it('marks a movie as watched', function (): void {
        $history = $this->service->markAsWatched($this->user, 12345);

        expect($history->status)->toBe('watched')
            ->and($history->tmdb_id)->toBe(12345)
            ->and($history->user_id)->toBe($this->user->id);
    });

    it('updates status when marking same movie again', function (): void {
        $this->service->markAsWatching($this->user, 12345);
        $this->service->markAsWatched($this->user, 12345);

        expect(WatchHistory::where('user_id', $this->user->id)->where('tmdb_id', 12345)->count())->toBe(1)
            ->and($this->service->getWatchStatus($this->user, 12345))->toBe('watched');
    });

    it('removes from history', function (): void {
        $this->service->markAsWatched($this->user, 12345);
        $this->service->removeFromHistory($this->user, 12345);

        expect($this->service->getWatchStatus($this->user, 12345))->toBeNull();
    });

    it('returns null status when movie not in history', function (): void {
        expect($this->service->getWatchStatus($this->user, 99999))->toBeNull();
    });
});

describe('watchlist', function (): void {
    it('adds a movie to watchlist', function (): void {
        $this->service->addToWatchlist($this->user, 12345);

        expect($this->service->isOnWatchlist($this->user, 12345))->toBeTrue();
    });

    it('does not duplicate watchlist entry', function (): void {
        $this->service->addToWatchlist($this->user, 12345);
        $this->service->addToWatchlist($this->user, 12345);

        expect(Watchlist::where('user_id', $this->user->id)->where('tmdb_id', 12345)->count())->toBe(1);
    });

    it('removes from watchlist', function (): void {
        $this->service->addToWatchlist($this->user, 12345);
        $this->service->removeFromWatchlist($this->user, 12345);

        expect($this->service->isOnWatchlist($this->user, 12345))->toBeFalse();
    });
});

describe('favorites', function (): void {
    it('adds a movie to favorites', function (): void {
        $this->service->addToFavorites($this->user, 12345);

        expect($this->service->isFavorited($this->user, 12345))->toBeTrue();
    });

    it('does not duplicate favorite entry', function (): void {
        $this->service->addToFavorites($this->user, 12345);
        $this->service->addToFavorites($this->user, 12345);

        expect(Favorite::where('user_id', $this->user->id)->where('tmdb_id', 12345)->count())->toBe(1);
    });

    it('removes from favorites', function (): void {
        $this->service->addToFavorites($this->user, 12345);
        $this->service->removeFromFavorites($this->user, 12345);

        expect($this->service->isFavorited($this->user, 12345))->toBeFalse();
    });
});
