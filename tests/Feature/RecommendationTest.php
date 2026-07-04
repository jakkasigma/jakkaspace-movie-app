<?php

use App\Models\User;
use App\Models\WatchHistory;
use App\Services\Movie\RecommendationService;

describe('recommendation service', function (): void {
    it('returns empty personalized list when user has fewer than 3 watched films', function (): void {
        $user = User::factory()->create();
        WatchHistory::factory()->for($user)->create(['status' => 'watched']);
        WatchHistory::factory()->for($user)->create(['status' => 'watched']);

        $service = app(RecommendationService::class);

        expect($service->getPersonalizedMovies($user))->toBe([]);
    });

    it('returns empty trending when user follows nobody', function (): void {
        $user = User::factory()->create();

        $service = app(RecommendationService::class);

        expect($service->getTrendingAmongFollowing($user))->toBe([]);
    });

    it('trending only includes films watched within 30 days', function (): void {
        $user = User::factory()->create();
        $followed = User::factory()->create();
        $user->following()->attach($followed->id);

        // Old entry — should NOT count
        WatchHistory::factory()->for($followed)->create([
            'tmdb_id' => 99999,
            'created_at' => now()->subDays(40),
        ]);

        $service = app(RecommendationService::class);
        $trending = $service->getTrendingAmongFollowing($user);

        // tmdb 99999 should not be in trending (too old), result should be empty
        $tmdbIds = array_column($trending, 'id');
        expect($tmdbIds)->not->toContain(99999);
    });
});

describe('discover page with recommendations', function (): void {
    it('discover redirects to home for guest', function (): void {
        $this->get(route('movies.discover'))
            ->assertRedirectToRoute('movies.index');
    });

    it('discover redirects to home for authenticated user', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('movies.discover'))
            ->assertRedirectToRoute('movies.index');
    });
});

describe('feed page with trending', function (): void {
    it('redirects /feed to timeline following tab', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('feed'))
            ->assertRedirect(route('timeline', ['tab' => 'following']));
    });
});
