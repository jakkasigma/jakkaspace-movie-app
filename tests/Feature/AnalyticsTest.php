<?php

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchHistory;
use App\Services\User\AnalyticsService;

describe('analytics page', function (): void {
    it('requires auth to view analytics', function (): void {
        $this->get(route('your-space.analytics'))
            ->assertRedirectToRoute('login');
    });

    it('renders analytics page for authenticated user', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.analytics'))
            ->assertOk()
            ->assertViewIs('space.analytics');
    });

    it('shows empty state when user has no data', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.analytics'))
            ->assertOk()
            ->assertSee('Belum ada data');
    });

    it('shows stats when user has data', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        WatchHistory::factory()->for($user)->create(['status' => 'watched']);
        WatchHistory::factory()->for($user)->create(['status' => 'watched']);
        DiaryEntry::factory()->for($user)->create();
        Review::factory()->for($user)->create(['rating' => 4]);

        $this->actingAs($user)
            ->get(route('your-space.analytics'))
            ->assertOk()
            ->assertViewHas('analytics');
    });
});

describe('analytics service', function (): void {
    it('returns correct total watched count', function (): void {
        $user = User::factory()->create();

        WatchHistory::factory()->for($user)->create(['status' => 'watched']);
        WatchHistory::factory()->for($user)->create(['status' => 'watched']);
        WatchHistory::factory()->for($user)->create(['status' => 'watching']);

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['total_watched'])->toBe(2);
    });

    it('returns correct total diary count', function (): void {
        $user = User::factory()->create();

        DiaryEntry::factory()->for($user)->count(3)->create();

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['total_diary'])->toBe(3);
    });

    it('calculates average rating correctly', function (): void {
        $user = User::factory()->create();

        Review::factory()->for($user)->create(['rating' => 4]);
        Review::factory()->for($user)->create(['rating' => 3]);

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['avg_rating'])->toBe(3.5);
    });

    it('returns null avg_rating when no reviews', function (): void {
        $user = User::factory()->create();

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['avg_rating'])->toBeNull();
    });

    it('counts rewatches correctly', function (): void {
        $user = User::factory()->create();

        DiaryEntry::factory()->for($user)->create(['is_rewatch' => true]);
        DiaryEntry::factory()->for($user)->create(['is_rewatch' => true]);
        DiaryEntry::factory()->for($user)->create(['is_rewatch' => false]);

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['rewatch_count'])->toBe(2);
    });

    it('returns monthly activity with 12 months', function (): void {
        $user = User::factory()->create();

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['monthly_activity'])->toHaveCount(12);
    });

    it('counts diary entries in correct month', function (): void {
        $user = User::factory()->create();

        DiaryEntry::factory()->for($user)->create(['watched_at' => now()->startOfMonth()]);
        DiaryEntry::factory()->for($user)->create(['watched_at' => now()->startOfMonth()]);

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        $currentMonth = now()->format('Y-m');
        expect($data['monthly_activity'][$currentMonth])->toBe(2);
    });

    it('returns mood distribution', function (): void {
        $user = User::factory()->create();

        DiaryEntry::factory()->for($user)->create(['mood' => '😊']);
        DiaryEntry::factory()->for($user)->create(['mood' => '😊']);
        DiaryEntry::factory()->for($user)->create(['mood' => '😴']);

        $service = app(AnalyticsService::class);
        $data = $service->getAnalytics($user);

        expect($data['mood_distribution']['😊'])->toBe(2)
            ->and($data['mood_distribution']['😴'])->toBe(1);
    });
});
