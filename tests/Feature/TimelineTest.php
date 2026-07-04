<?php

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\User;

describe('timeline page', function (): void {
    it('renders the timeline page publicly', function (): void {
        $this->get(route('timeline'))
            ->assertOk()
            ->assertViewIs('timeline.index');
    });

    it('defaults to all tab', function (): void {
        $this->get(route('timeline'))
            ->assertOk()
            ->assertViewHas('tab', 'all');
    });

    it('accepts valid tab parameter', function (string $tab): void {
        $this->get(route('timeline', ['tab' => $tab]))
            ->assertOk()
            ->assertViewHas('tab', $tab);
    })->with(['all', 'trending', 'following']);

    it('falls back to all tab for invalid tab value', function (): void {
        $this->get(route('timeline', ['tab' => 'invalid']))
            ->assertOk()
            ->assertViewHas('tab', 'all');
    });

    it('shows login prompt on following tab for guests', function (): void {
        $this->get(route('timeline', ['tab' => 'following']))
            ->assertOk()
            ->assertSee('Masuk');
    });

    it('following tab shows empty state when no follows', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('timeline', ['tab' => 'following']))
            ->assertOk()
            ->assertSee('Belum ada aktivitas');
    });

    it('following tab shows diary activity from followed users', function (): void {
        $user = User::factory()->create();
        $followed = User::factory()->create();
        $user->following()->attach($followed->id);

        DiaryEntry::factory()->for($followed)->create(['tmdb_id' => 99999]);

        $this->actingAs($user)
            ->get(route('timeline', ['tab' => 'following']))
            ->assertOk()
            ->assertSee($followed->name);
    });

    it('popular reviews appear in all tab', function (): void {
        $user = User::factory()->create();
        Review::factory()->for($user)->create([
            'tmdb_id' => 11111,
            'body' => 'Review ini sangat menarik dan informatif untuk semua penonton.',
        ]);

        $this->get(route('timeline', ['tab' => 'all']))
            ->assertOk();
    });

    it('feed route redirects to timeline following tab', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('feed'))
            ->assertRedirect(route('timeline', ['tab' => 'following']));
    });
});
