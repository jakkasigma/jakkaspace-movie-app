<?php

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\User;
use App\Models\Watchlist;

describe('your space', function (): void {
    it('redirects guest to login', function (): void {
        $this->get(route('your-space'))->assertRedirectToRoute('login');
    });

    it('shows space dashboard to verified user', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space'))
            ->assertOk()
            ->assertViewIs('space.index');
    });

    it('shows stats on dashboard', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space'))
            ->assertOk()
            ->assertViewHas('stats');
    });

    it('shows recent diary section on dashboard', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);
        DiaryEntry::factory()->for($user)->create(['movie_title' => 'Test Film']);

        $this->actingAs($user)
            ->get(route('your-space'))
            ->assertOk()
            ->assertSee('Diary Terbaru');
    });

    it('shows recent reviews section on dashboard', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Review::factory()->for($user)->create(['tmdb_id' => 550, 'body' => 'Great film']);

        $this->actingAs($user)
            ->get(route('your-space'))
            ->assertOk()
            ->assertSee('Review Terbaru');
    });

    it('shows diary page with filter and sort', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.diary', ['sort' => 'oldest']))
            ->assertOk()
            ->assertViewIs('space.diary')
            ->assertViewHasAll(['yearOptions', 'activeSort', 'diaryStats']);
    });

    it('shows history page', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.history'))
            ->assertOk()
            ->assertViewIs('space.history');
    });

    it('shows watchlist page', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);
        Watchlist::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('your-space.watchlist'))
            ->assertOk()
            ->assertViewIs('space.watchlist')
            ->assertViewHas('watchlistInfo');
    });

    it('shows favorites page', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.favorites'))
            ->assertOk()
            ->assertViewIs('space.favorites')
            ->assertViewHas('favoritesInfo');
    });

    it('shows diary edit page for owner', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $entry = DiaryEntry::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('your-space.diary.edit', $entry))
            ->assertOk()
            ->assertViewIs('space.diary-edit');
    });

    it('prevents editing diary of other user', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $other = User::factory()->create();
        $entry = DiaryEntry::factory()->for($other)->create();

        $this->actingAs($user)
            ->get(route('your-space.diary.edit', $entry))
            ->assertForbidden();
    });

    it('updates diary entry', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $entry = DiaryEntry::factory()->for($user)->create(['notes' => 'Old notes']);

        $this->actingAs($user)
            ->put(route('your-space.diary.update', $entry), [
                'notes' => 'Updated notes',
                'mood' => '😊',
                'is_rewatch' => true,
                'watched_at' => now()->format('Y-m-d'),
            ])
            ->assertRedirectToRoute('your-space.diary');

        expect($entry->fresh()->notes)->toBe('Updated notes');
    });
});
