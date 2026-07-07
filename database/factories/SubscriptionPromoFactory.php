<?php

namespace Database\Factories;

use App\Models\SubscriptionPromo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPromoFactory extends Factory
{
    protected $model = SubscriptionPromo::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => 'percent',
            'value' => fake()->numberBetween(10, 50),
            'max_uses' => 0,
            'used_count' => 0,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
