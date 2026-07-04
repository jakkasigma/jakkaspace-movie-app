<?php

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;

describe('review like controller', function (): void {
    it('requires auth to like a review', function (): void {
        $review = Review::factory()->create();

        $this->post(route('reviews.like.store', $review))
            ->assertRedirectToRoute('login');
    });

    it('likes a review', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.like.store', $review))
            ->assertRedirect();

        expect(ReviewLike::where('user_id', $user->id)->where('review_id', $review->id)->exists())
            ->toBeTrue();
    });

    it('does not create duplicate likes', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)->post(route('reviews.like.store', $review));
        $this->actingAs($user)->post(route('reviews.like.store', $review));

        expect(ReviewLike::where('user_id', $user->id)->where('review_id', $review->id)->count())
            ->toBe(1);
    });

    it('unlikes a review', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        ReviewLike::factory()->for($user)->for($review)->create();

        $this->actingAs($user)
            ->delete(route('reviews.like.destroy', $review))
            ->assertRedirect();

        expect(ReviewLike::where('user_id', $user->id)->where('review_id', $review->id)->exists())
            ->toBeFalse();
    });
});
