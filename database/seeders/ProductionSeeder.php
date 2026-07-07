<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed data wajib untuk production.
     * Aman dijalankan berulang kali (idempotent).
     */
    public function run(): void
    {
        $this->call([
            SubscriptionPlanSeeder::class,
            ThemeSeeder::class,
        ]);
    }
}
