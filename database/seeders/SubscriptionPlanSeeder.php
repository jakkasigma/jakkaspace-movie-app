<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Plus Bulanan', 'tier' => 'plus', 'duration_days' => 30, 'price' => 15000, 'is_recommended' => false, 'sort_order' => 1],
            ['name' => 'Plus 3 Bulan', 'tier' => 'plus', 'duration_days' => 90, 'price' => 45000, 'is_recommended' => false, 'sort_order' => 2],
            ['name' => 'Plus Tahunan', 'tier' => 'plus', 'duration_days' => 365, 'price' => 150000, 'is_recommended' => true, 'sort_order' => 3],
            ['name' => 'Plus+ Bulanan', 'tier' => 'plus_plus', 'duration_days' => 30, 'price' => 30000, 'is_recommended' => false, 'sort_order' => 4],
            ['name' => 'Plus+ 3 Bulan', 'tier' => 'plus_plus', 'duration_days' => 90, 'price' => 90000, 'is_recommended' => false, 'sort_order' => 5],
            ['name' => 'Plus+ Tahunan', 'tier' => 'plus_plus', 'duration_days' => 365, 'price' => 300000, 'is_recommended' => true, 'sort_order' => 6],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
