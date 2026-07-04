<?php

use App\Models\User;

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

    it('shows diary page', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.diary'))
            ->assertOk()
            ->assertViewIs('space.diary');
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

        $this->actingAs($user)
            ->get(route('your-space.watchlist'))
            ->assertOk()
            ->assertViewIs('space.watchlist');
    });

    it('shows favorites page', function (): void {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('your-space.favorites'))
            ->assertOk()
            ->assertViewIs('space.favorites');
    });
});
