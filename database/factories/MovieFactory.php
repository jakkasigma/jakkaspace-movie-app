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
            'overview' => fake()->paragraph(),
            'genres' => fake()->randomElement(['Action, Adventure', 'Drama, Romance', 'Comedy', 'Horror, Thriller']),
            'rating' => fake()->randomFloat(1, 1, 10),
            'poster_url' => 'https://image.tmdb.org/t/p/w500/'.fake()->bothify('??########.jpg'),
            'release_year' => fake()->year(),
            'cached_at' => now(),
        ];
    }
}
