<?php

use App\Models\PinnedMovie;
use App\Models\User;

describe('pinned movie controller', function (): void {
    it('requires auth to pin a movie', function (): void {
        $this->post(route('movies.pin.store', 12345))
            ->assertRedirectToRoute('login');
    });

    it('pins a movie', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.pin.store', 12345))
            ->assertRedirect();

        expect(PinnedMovie::where('user_id', $user->id)->where('tmdb_id', 12345)->exists())
            ->toBeTrue();
    });

    it('does not pin duplicate movies', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('movies.pin.store', 12345));
        $this->actingAs($user)->post(route('movies.pin.store', 12345));

        expect(PinnedMovie::where('user_id', $user->id)->where('tmdb_id', 12345)->count())
            ->toBe(1);
    });

    it('enforces max 6 pinned movies', function (): void {
        $user = User::factory()->create();

        foreach (range(1, 6) as $id) {
            PinnedMovie::factory()->for($user)->create(['tmdb_id' => $id]);
        }

        $this->actingAs($user)
            ->post(route('movies.pin.store', 999))
            ->assertRedirect();

        // Should not be added — already at max
        expect(PinnedMovie::where('user_id', $user->id)->count())->toBe(6);
    });

    it('unpins a movie', function (): void {
        $user = User::factory()->create();
        PinnedMovie::factory()->for($user)->create(['tmdb_id' => 12345]);

        $this->actingAs($user)
            ->delete(route('movies.pin.destroy', 12345))
            ->assertRedirect();

        expect(PinnedMovie::where('user_id', $user->id)->where('tmdb_id', 12345)->exists())
            ->toBeFalse();
    });

    it('requires auth to unpin a movie', function (): void {
        $this->delete(route('movies.pin.destroy', 12345))
            ->assertRedirectToRoute('login');
    });
});
