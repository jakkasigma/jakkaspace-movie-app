<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tmdb_id' => fake()->numberBetween(1, 999999),
            'rating' => fake()->numberBetween(1, 5),
            'body' => fake()->optional(0.7)->paragraphs(2, true),
            'has_spoiler' => fake()->boolean(15),
        ];
    }

    public function withSpoiler(): static
    {
        return $this->state(fn (): array => ['has_spoiler' => true]);
    }

    public function ratingOf(int $rating): static
    {
        return $this->state(fn (): array => ['rating' => $rating]);
    }
}
