<?php

use App\Console\Commands\MigrateSubscriptionHistory;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('plus:check-expired')->daily();

Artisan::command('plus:migrate-history', function () {
    $this->call(MigrateSubscriptionHistory::class);
})->purpose('Migrate existing promo_user and redeem_code_user records to subscription_transactions');
