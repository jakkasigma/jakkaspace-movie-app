<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WatchHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WatchHistory>
 */
class WatchHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tmdb_id' => fake()->numberBetween(1, 999999),
            'status' => fake()->randomElement(['watched', 'watching', 'dropped']),
        ];
    }

    public function watched(): static
    {
        return $this->state(fn (): array => ['status' => 'watched']);
    }

    public function watching(): static
    {
        return $this->state(fn (): array => ['status' => 'watching']);
    }

    public function dropped(): static
    {
        return $this->state(fn (): array => ['status' => 'dropped']);
    }
}
