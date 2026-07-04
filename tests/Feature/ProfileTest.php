<?php

use App\Models\User;

describe('public profile', function (): void {
    it('returns 404 for unknown username', function (): void {
        $this->get(route('profile.show', 'tidak-ada'))
            ->assertNotFound();
    });

    it('shows public profile page', function (): void {
        $user = User::factory()->create([
            'username' => 'jakkauser',
            'name' => 'Jakka Tester',
        ]);

        $this->get(route('profile.show', 'jakkauser'))
            ->assertOk()
            ->assertSee('Jakka Tester');
    });

    it('shows follow button for authenticated visitor', function (): void {
        $profile = User::factory()->create(['username' => 'targetuser']);
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('profile.show', 'targetuser'))
            ->assertOk()
            ->assertSee('Follow');
    });

    it('shows following state when already following', function (): void {
        $profile = User::factory()->create(['username' => 'targetuser']);
        $viewer = User::factory()->create();
        $viewer->following()->attach($profile->id);

        $this->actingAs($viewer)
            ->get(route('profile.show', 'targetuser'))
            ->assertOk()
            ->assertSee('Following');
    });

    it('does not show follow button on own profile', function (): void {
        $user = User::factory()->create(['username' => 'ownprofile']);

        $this->actingAs($user)
            ->get(route('profile.show', 'ownprofile'))
            ->assertOk()
            ->assertDontSee('btn-follow', false);
    });
});
