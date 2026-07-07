<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;

beforeEach(function (): void {
    $this->seed(SubscriptionPlanSeeder::class);
});

describe('admin plan management', function (): void {
    it('requires admin to access plans page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.plans.index'))
            ->assertNotFound();
    });

    it('lists plans', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.plans.index'))
            ->assertOk()
            ->assertSee('Plus Bulanan')
            ->assertSee('Plus+ Tahunan');
    });

    it('creates a new plan', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => 'Plus Coba',
                'tier' => 'plus',
                'duration_days' => 7,
                'price' => 5000,
                'sort_order' => 10,
            ])
            ->assertRedirect();

        expect(SubscriptionPlan::where('name', 'Plus Coba')->exists())->toBeTrue();
    });

    it('updates a plan', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $plan = SubscriptionPlan::where('name', 'Plus Bulanan')->first();

        $this->actingAs($admin)
            ->put(route('admin.plans.update', $plan), [
                'name' => 'Plus Bulanan Updated',
                'tier' => 'plus',
                'duration_days' => 30,
                'price' => 20000,
                'sort_order' => 1,
            ])
            ->assertRedirect();

        expect($plan->fresh()->name)->toBe('Plus Bulanan Updated');
        expect($plan->fresh()->price)->toBe(20000);
    });

    it('disables (deactivates) a plan', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $plan = SubscriptionPlan::where('name', 'Plus Bulanan')->first();

        $this->actingAs($admin)
            ->delete(route('admin.plans.destroy', $plan))
            ->assertRedirect();

        expect($plan->fresh()->is_active)->toBeFalse();
    });

    it('toggles plan active status', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $plan = SubscriptionPlan::where('name', 'Plus Bulanan')->first();

        $this->actingAs($admin)
            ->post(route('admin.plans.toggle-active', $plan))
            ->assertRedirect();

        expect($plan->fresh()->is_active)->toBeFalse();

        $this->actingAs($admin)
            ->post(route('admin.plans.toggle-active', $plan))
            ->assertRedirect();

        expect($plan->fresh()->is_active)->toBeTrue();
    });

    it('validates plan creation', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.plans.store'), [
                'name' => '',
                'tier' => 'invalid',
                'duration_days' => -1,
                'price' => -1,
            ])
            ->assertSessionHasErrors(['name', 'tier', 'duration_days', 'price']);
    });
});
