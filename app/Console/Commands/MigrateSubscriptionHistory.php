<?php

namespace App\Console\Commands;

use App\Models\SubscriptionTransaction;
use DB;
use Illuminate\Console\Command;

class MigrateSubscriptionHistory extends Command
{
    protected $signature = 'plus:migrate-history';

    protected $description = 'Migrate existing subscription_promo_user and redeem_code_user records to subscription_transactions';

    public function handle(): void
    {
        $this->info('Migrating subscription_promo_user records...');

        $promoRecords = DB::table('subscription_promo_user')
            ->join('subscription_promos', 'subscription_promo_user.subscription_promo_id', '=', 'subscription_promos.id')
            ->select([
                'subscription_promo_user.user_id',
                'subscription_promo_user.plan_id',
                'subscription_promo_user.original_price',
                'subscription_promo_user.discounted_price',
                'subscription_promo_user.code_used',
                'subscription_promo_user.applied_at',
                'subscription_promos.name as promo_name',
            ])
            ->get();

        $count = 0;
        foreach ($promoRecords as $record) {
            $plan = DB::table('subscription_plans')->find($record->plan_id);
            if (! $plan) {
                continue;
            }

            SubscriptionTransaction::create([
                'user_id' => $record->user_id,
                'plan_id' => $record->plan_id,
                'tier' => $plan->tier,
                'action' => 'subscribe',
                'price' => $record->discounted_price,
                'payment_method' => null,
                'promo_id' => null,
                'promo_code' => $record->code_used,
                'period_days' => $plan->duration_days,
                'expires_at' => null,
                'notes' => "Migrated from promo: {$record->promo_name}",
                'created_at' => $record->applied_at,
                'updated_at' => $record->applied_at,
            ]);

            $count++;
        }

        $this->info("Migrated {$count} promo records.");

        $this->info('Migrating redeem_code_user records...');

        $redeemRecords = DB::table('redeem_code_user')
            ->join('redeem_codes', 'redeem_code_user.redeem_code_id', '=', 'redeem_codes.id')
            ->select([
                'redeem_code_user.user_id',
                'redeem_code_user.redeemed_at',
                'redeem_codes.tier',
                'redeem_codes.duration_days',
                'redeem_codes.code',
            ])
            ->get();

        $count = 0;
        foreach ($redeemRecords as $record) {
            SubscriptionTransaction::create([
                'user_id' => $record->user_id,
                'plan_id' => null,
                'tier' => $record->tier,
                'action' => 'redeem',
                'price' => 0,
                'payment_method' => 'redeem',
                'period_days' => $record->duration_days,
                'expires_at' => null,
                'notes' => "Migrated from redeem code: {$record->code}",
                'created_at' => $record->redeemed_at,
                'updated_at' => $record->redeemed_at,
            ]);

            $count++;
        }

        $this->info("Migrated {$count} redeem records.");
        $this->info('Migration complete.');
    }
}
