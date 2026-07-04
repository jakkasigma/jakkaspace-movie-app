<?php

namespace Database\Factories;

use App\Models\ListMovie;
use App\Models\MovieList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListMovie>
 */
class ListMovieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'movie_list_id' => MovieList::factory(),
            'tmdb_id' => fake()->numberBetween(1, 999999),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
