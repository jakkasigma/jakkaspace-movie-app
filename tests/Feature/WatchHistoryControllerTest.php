<?php

use App\Models\User;
use App\Models\WatchHistory;

describe('watch history controller', function (): void {
    it('requires auth to mark as watched', function (): void {
        $this->post(route('movies.watch.store', 12345), ['status' => 'watched'])
            ->assertRedirectToRoute('login');
    });

    it('marks a movie as watched', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watch.store', 12345), ['status' => 'watched'])
            ->assertRedirect();

        expect(WatchHistory::where('user_id', $user->id)->where('tmdb_id', 12345)->value('status'))
            ->toBe('watched');
    });

    it('marks a movie as watching', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watch.store', 12345), ['status' => 'watching'])
            ->assertRedirect();

        expect(WatchHistory::where('user_id', $user->id)->where('tmdb_id', 12345)->value('status'))
            ->toBe('watching');
    });

    it('rejects invalid status', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.watch.store', 12345), ['status' => 'invalid'])
            ->assertSessionHasErrors('status');
    });

    it('removes from watch history', function (): void {
        $user = User::factory()->create();
        WatchHistory::factory()->watched()->create(['user_id' => $user->id, 'tmdb_id' => 12345]);

        $this->actingAs($user)
            ->delete(route('movies.watch.destroy', 12345))
            ->assertRedirect();

        expect(WatchHistory::where('user_id', $user->id)->where('tmdb_id', 12345)->exists())
            ->toBeFalse();
    });
});
