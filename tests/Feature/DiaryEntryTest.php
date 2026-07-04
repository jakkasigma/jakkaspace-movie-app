<?php

use App\Models\DiaryEntry;
use App\Models\User;

describe('diary controller', function (): void {
    it('requires auth to create diary entry', function (): void {
        $this->post(route('movies.diary.store', 12345), [
            'watched_at' => today()->toDateString(),
        ])->assertRedirectToRoute('login');
    });

    it('creates a diary entry', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.diary.store', 12345), [
                'watched_at' => today()->toDateString(),
                'mood' => 'happy',
                'notes' => 'Film yang bagus.',
                'is_rewatch' => false,
            ])
            ->assertRedirect();

        expect(DiaryEntry::where('user_id', $user->id)->where('tmdb_id', 12345)->exists())
            ->toBeTrue();
    });

    it('rejects future date', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('movies.diary.store', 12345), [
                'watched_at' => today()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('watched_at');
    });

    it('allows multiple diary entries for same movie', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('movies.diary.store', 12345), [
            'watched_at' => today()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('movies.diary.store', 12345), [
            'watched_at' => today()->subWeek()->toDateString(),
            'is_rewatch' => true,
        ]);

        expect(DiaryEntry::where('user_id', $user->id)->where('tmdb_id', 12345)->count())->toBe(2);
    });

    it('only allows owner to delete diary entry', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $entry = DiaryEntry::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->delete(route('diary.destroy', $entry))
            ->assertForbidden();

        expect(DiaryEntry::find($entry->id))->not->toBeNull();
    });

    it('allows owner to delete diary entry', function (): void {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('diary.destroy', $entry))
            ->assertRedirect();

        expect(DiaryEntry::find($entry->id))->toBeNull();
    });
});
