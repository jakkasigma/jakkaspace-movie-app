<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewComment>
 */
class ReviewCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'review_id' => Review::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
