<?php

namespace Database\Factories;

use App\Models\PinnedMovie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PinnedMovie>
 */
class PinnedMovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tmdb_id' => fake()->numberBetween(1, 999999),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
