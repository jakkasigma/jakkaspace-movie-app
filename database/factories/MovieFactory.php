<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Movie>
 */
class MovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tmdb_id' => fake()->unique()->numberBetween(1, 999999),
            'title' => fake()->sentence(3),
            'original_title' => fake()->sentence(3),
            'poster_path' => '/'.fake()->bothify('??########.jpg'),
            'backdrop_path' => '/'.fake()->bothify('??########.jpg'),
            'release_date' => fake()->dateTimeBetween('-30 years', 'now')->format('Y-m-d'),
        ];
    }
}
