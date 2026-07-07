<?php

use App\Models\Review;
use App\Models\User;

describe('review page', function (): void {
    it('shows public review page', function (): void {
        $review = Review::factory()->create([
            'body' => 'Film yang sangat mengesankan.',
            'rating' => 4,
        ]);

        $this->get(route('reviews.show', $review))
            ->assertOk()
            ->assertSee('Film yang sangat mengesankan.')
            ->assertSee('4/5');
    });

    it('shows like button for authenticated user', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->create(['body' => 'Keren banget filmnya.']);

        $this->actingAs($user)
            ->get(route('reviews.show', $review))
            ->assertOk()
            ->assertSee('♡');
    });
});
