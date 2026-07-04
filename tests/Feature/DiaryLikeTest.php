<?php

use App\Models\DiaryEntry;
use App\Models\DiaryLike;
use App\Models\User;

describe('diary like controller', function (): void {
    it('requires auth to like a diary entry', function (): void {
        $entry = DiaryEntry::factory()->create();

        $this->post(route('diary.like.store', $entry))
            ->assertRedirectToRoute('login');
    });

    it('likes a diary entry', function (): void {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->create();

        $this->actingAs($user)
            ->post(route('diary.like.store', $entry))
            ->assertRedirect();

        expect(DiaryLike::where('user_id', $user->id)->where('diary_entry_id', $entry->id)->exists())
            ->toBeTrue();
    });

    it('does not create duplicate likes', function (): void {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->create();

        $this->actingAs($user)->post(route('diary.like.store', $entry));
        $this->actingAs($user)->post(route('diary.like.store', $entry));

        expect(DiaryLike::where('user_id', $user->id)->where('diary_entry_id', $entry->id)->count())
            ->toBe(1);
    });

    it('unlikes a diary entry', function (): void {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->create();
        DiaryLike::factory()->for($user)->for($entry, 'diaryEntry')->create();

        $this->actingAs($user)
            ->delete(route('diary.like.destroy', $entry))
            ->assertRedirect();

        expect(DiaryLike::where('user_id', $user->id)->where('diary_entry_id', $entry->id)->exists())
            ->toBeFalse();
    });
});
