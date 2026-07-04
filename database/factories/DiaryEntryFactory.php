<?php

namespace Database\Factories;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiaryEntry>
 */
class DiaryEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tmdb_id' => fake()->numberBetween(1, 999999),
            'watched_at' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'notes' => fake()->optional(0.6)->paragraph(),
            'mood' => fake()->optional(0.5)->randomElement(['happy', 'sad', 'thrilled', 'bored', 'moved']),
            'is_rewatch' => fake()->boolean(20),
        ];
    }

    public function rewatch(): static
    {
        return $this->state(fn (): array => ['is_rewatch' => true]);
    }

    public function withNotes(): static
    {
        return $this->state(fn (): array => ['notes' => fake()->paragraph()]);
    }
}
