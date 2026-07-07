<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'tier' => 'plus',
            'duration_days' => 30,
            'price' => fake()->numberBetween(10000, 50000),
            'is_recommended' => false,
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
