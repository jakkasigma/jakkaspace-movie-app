<?php

namespace Database\Factories;

use App\Models\MovieList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovieList>
 */
class MovieListFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional(0.6)->sentence(),
            'is_public' => fake()->boolean(70),
        ];
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_public' => false]);
    }

    public function public(): static
    {
        return $this->state(fn (): array => ['is_public' => true]);
    }
}
