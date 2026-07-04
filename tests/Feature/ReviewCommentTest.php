<?php

use App\Models\Review;
use App\Models\ReviewComment;
use App\Models\User;

describe('review comment controller', function (): void {
    it('requires auth to comment', function (): void {
        $review = Review::factory()->create();

        $this->post(route('reviews.comments.store', $review), ['body' => 'Komentar'])
            ->assertRedirectToRoute('login');
    });

    it('adds a comment to a review', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.comments.store', $review), ['body' => 'Review yang bagus!'])
            ->assertRedirect();

        expect(ReviewComment::where('user_id', $user->id)->where('review_id', $review->id)->exists())
            ->toBeTrue();
    });

    it('validates comment body is required', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.comments.store', $review), ['body' => ''])
            ->assertSessionHasErrors('body');
    });

    it('validates comment max length', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.comments.store', $review), ['body' => str_repeat('a', 1001)])
            ->assertSessionHasErrors('body');
    });

    it('allows owner to delete their comment', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $comment = ReviewComment::factory()->for($user)->for($review)->create();

        $this->actingAs($user)
            ->delete(route('reviews.comments.destroy', [$review, $comment]))
            ->assertRedirect();

        expect(ReviewComment::find($comment->id))->toBeNull();
    });

    it('blocks non-owner from deleting a comment', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $review = Review::factory()->create();
        $comment = ReviewComment::factory()->for($owner)->for($review)->create();

        $this->actingAs($other)
            ->delete(route('reviews.comments.destroy', [$review, $comment]))
            ->assertForbidden();

        expect(ReviewComment::find($comment->id))->not->toBeNull();
    });
});
