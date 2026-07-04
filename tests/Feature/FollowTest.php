<?php

use App\Models\User;

describe('follow controller', function (): void {
    it('requires auth to follow', function (): void {
        $target = User::factory()->create();

        $this->post(route('users.follow', $target))
            ->assertRedirectToRoute('login');
    });

    it('allows a user to follow another user', function (): void {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($follower)
            ->post(route('users.follow', $target))
            ->assertRedirect();

        expect($follower->following()->where('following_id', $target->id)->exists())
            ->toBeTrue();
    });

    it('does not allow self-follow', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.follow', $user))
            ->assertRedirect();

        expect($user->following()->where('following_id', $user->id)->exists())
            ->toBeFalse();
    });

    it('does not create duplicate follows', function (): void {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($follower)->post(route('users.follow', $target));
        $this->actingAs($follower)->post(route('users.follow', $target));

        expect($follower->following()->where('following_id', $target->id)->count())
            ->toBe(1);
    });

    it('allows a user to unfollow', function (): void {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $follower->following()->attach($target->id);

        $this->actingAs($follower)
            ->delete(route('users.unfollow', $target))
            ->assertRedirect();

        expect($follower->following()->where('following_id', $target->id)->exists())
            ->toBeFalse();
    });

    it('shows followers page', function (): void {
        $user = User::factory()->create(['username' => 'testuser']);
        $follower = User::factory()->create(['name' => 'Follower Satu']);
        $follower->following()->attach($user->id);

        $this->get(route('profile.followers', 'testuser'))
            ->assertOk()
            ->assertSee('Follower Satu');
    });

    it('shows following page', function (): void {
        $user = User::factory()->create(['username' => 'testuser']);
        $following = User::factory()->create(['name' => 'Following Satu']);
        $user->following()->attach($following->id);

        $this->get(route('profile.following', 'testuser'))
            ->assertOk()
            ->assertSee('Following Satu');
    });
});
