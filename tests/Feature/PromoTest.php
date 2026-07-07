<?php

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPromo;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;

beforeEach(function (): void {
    $this->seed(SubscriptionPlanSeeder::class);
});

describe('promo admin management', function (): void {
    it('requires admin to access promos page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.promo-redeem.index'))
            ->assertNotFound();
    });

    it('creates a percent promo', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $plan = SubscriptionPlan::first();

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.promos.store'), [
                'name' => 'Diskon Lebaran',
                'type' => 'percent',
                'value' => 25,
                'plan_id' => $plan->id,
                'max_uses' => 100,
            ])
            ->assertRedirect();

        $promo = SubscriptionPromo::where('name', 'Diskon Lebaran')->first();
        expect($promo)->not->toBeNull();
        expect($promo->type)->toBe('percent');
        expect($promo->value)->toBe(25);
        expect($promo->created_by)->toBe($admin->id);
    });

    it('creates a fixed promo', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.promos.store'), [
                'name' => 'Diskon Rp10rb',
                'type' => 'fixed',
                'value' => 10000,
                'max_uses' => 50,
            ])
            ->assertRedirect();

        expect(SubscriptionPromo::where('name', 'Diskon Rp10rb')->exists())->toBeTrue();
    });

    it('creates a plan discount promo', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $plan = SubscriptionPlan::first();

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.promos.store'), [
                'name' => 'Diskon Plan',
                'type' => 'percent',
                'value' => 10,
                'plan_id' => $plan->id,
                'max_uses' => 0,
            ])
            ->assertRedirect();

        $promo = SubscriptionPromo::where('name', 'Diskon Plan')->first();
        expect($promo)->not->toBeNull();
    });

    it('disables a promo', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $promo = SubscriptionPromo::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.promo-redeem.promos.destroy', $promo))
            ->assertRedirect();

        expect($promo->fresh()->is_active)->toBeFalse();
    });

    it('activates a disabled promo', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $promo = SubscriptionPromo::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.promos.activate', $promo))
            ->assertRedirect();

        expect($promo->fresh()->is_active)->toBeTrue();
    });
});

describe('promo validation', function (): void {
    it('isValid returns true for active valid promo', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'is_active' => true,
            'type' => 'percent',
            'value' => 25,
            'starts_at' => null,
            'expires_at' => null,
            'max_uses' => 0,
        ]);

        expect($promo->isValid())->toBeTrue();
    });

    it('isValid returns false for inactive promo', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'is_active' => false,
        ]);

        expect($promo->isValid())->toBeFalse();
    });

    it('isValid returns false for expired promo', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        expect($promo->isValid())->toBeFalse();
    });

    it('isValid returns false for exhausted promo', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'is_active' => true,
            'max_uses' => 10,
            'used_count' => 10,
        ]);

        expect($promo->isValid())->toBeFalse();
    });

    it('applyPrice calculates percent discount', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'type' => 'percent',
            'value' => 25,
        ]);

        expect($promo->applyPrice(100000))->toBe(75000);
        expect($promo->applyPrice(15000))->toBe(11250);
    });

    it('applyPrice calculates fixed discount', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'type' => 'fixed',
            'value' => 10000,
        ]);

        expect($promo->applyPrice(50000))->toBe(40000);
    });

    it('applyPrice floor to 0', function (): void {
        $promo = SubscriptionPromo::factory()->create([
            'type' => 'fixed',
            'value' => 999999,
        ]);

        expect($promo->applyPrice(10000))->toBe(0);
    });
});

describe('auto promo', function (): void {
    it('auto promo is active for SubscriptionPlan', function (): void {
        $plan = SubscriptionPlan::where('tier', 'plus')->where('duration_days', 30)->first();
        SubscriptionPromo::factory()->create([
            'type' => 'percent',
            'value' => 20,
            'plan_id' => $plan->id,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        expect($plan->hasActiveAutoPromo())->toBeTrue();
    });

    it('returns discounted price from auto promo', function (): void {
        $plan = SubscriptionPlan::where('tier', 'plus')->where('duration_days', 30)->first();
        SubscriptionPromo::factory()->create([
            'type' => 'percent',
            'value' => 20,
            'plan_id' => $plan->id,
            'is_active' => true,
            'expires_at' => now()->addDays(30),
        ]);

        expect($plan->discountedPrice())->toBe(12000);
    });
});
