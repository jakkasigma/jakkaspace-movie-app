<?php

namespace Database\Factories;

use App\Models\DiaryEntry;
use App\Models\DiaryLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiaryLike>
 */
class DiaryLikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'diary_entry_id' => DiaryEntry::factory(),
        ];
    }
}
