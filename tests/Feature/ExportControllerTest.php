<?php

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\User;
use App\Models\WatchHistory;

describe('export controller', function (): void {
    it('requires auth', function (): void {
        $this->get(route('export', 'diary'))
            ->assertRedirectToRoute('login');
    });

    it('blocks free users', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('export', 'diary'))
            ->assertForbidden();
    });

    it('allows plus users to export diary', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);

        DiaryEntry::factory()->for($user)->create(['tmdb_id' => 12345]);

        $this->actingAs($user)
            ->get(route('export', 'diary'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertHeader('Content-Disposition', 'attachment; filename=diary.csv');
    });

    it('allows plus users to export reviews', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);

        Review::factory()->for($user)->create(['tmdb_id' => 12345]);

        $this->actingAs($user)
            ->get(route('export', 'reviews'))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=reviews.csv');
    });

    it('allows plus users to export history', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);

        WatchHistory::factory()->for($user)->create(['tmdb_id' => 12345]);

        $this->actingAs($user)
            ->get(route('export', 'history'))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=history.csv');
    });

    it('allows plus users to export all as zip', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get(route('export', 'all'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/zip')
            ->assertHeader('Content-Disposition', 'attachment; filename=jakkaspace-export.zip');
    });

    it('returns 404 for unknown export type', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get(route('export', 'invalid'))
            ->assertNotFound();
    });
});
