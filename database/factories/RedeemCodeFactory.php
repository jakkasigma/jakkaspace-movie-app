<?php

namespace Database\Factories;

use App\Models\RedeemCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RedeemCodeFactory extends Factory
{
    protected $model = RedeemCode::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('????-####')),
            'tier' => 'plus',
            'duration_days' => 30,
            'max_uses' => 10,
            'used_count' => 0,
            'is_active' => true,
            'created_by' => User::factory(),
            'expires_at' => now()->addYear(),
        ];
    }

    public function plusPlus(): static
    {
        return $this->state(fn (array $attributes) => [
            'tier' => 'plus_plus',
            'duration_days' => 365,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_uses' => 1,
            'used_count' => 1,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
