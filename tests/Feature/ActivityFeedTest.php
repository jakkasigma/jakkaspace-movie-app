<?php

use App\Models\DiaryEntry;
use App\Models\User;

describe('activity feed', function (): void {
    it('redirects /feed to timeline following tab', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('feed'))
            ->assertRedirect(route('timeline', ['tab' => 'following']));
    });

    it('shows empty state on timeline following tab when user follows nobody', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('timeline', ['tab' => 'following']))
            ->assertOk()
            ->assertSee('Belum ada aktivitas');
    });

    it('shows diary activity from followed users on timeline following tab', function (): void {
        $user = User::factory()->create();
        $followed = User::factory()->create();
        $user->following()->attach($followed->id);

        DiaryEntry::factory()->for($followed)->create(['tmdb_id' => 12345]);

        $this->actingAs($user)
            ->get(route('timeline', ['tab' => 'following']))
            ->assertOk()
            ->assertSee($followed->name);
    });
});
