<?php

use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;

beforeEach(function (): void {
    $this->seed(SubscriptionPlanSeeder::class);
});

describe('premium subscription', function (): void {
    it('requires auth to access plus page', function (): void {
        $this->get(route('plus'))
            ->assertRedirectToRoute('login');
    });

    it('shows plus page for free user', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('plus'))
            ->assertOk()
            ->assertSee('Plus')
            ->assertSee('Plus+');
    });

    it('shows pricing for free user', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('plus'))
            ->assertOk()
            ->assertSee('Rp15.000')
            ->assertSee('Rp30.000')
            ->assertSee('Rp150.000')
            ->assertSee('Rp300.000');
    });

    it('shows active state for plus user', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'subscribed_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get(route('plus'))
            ->assertOk()
            ->assertSee('Plus Active');
    });

    it('shows active state for plus_plus user', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus_plus',
            'subscribed_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get(route('plus'))
            ->assertOk()
            ->assertSee('Plus+ Active');
    });

    it('creates pending transaction for monthly plus', function (): void {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('tier', 'plus')->where('duration_days', 30)->first();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), ['plan_id' => $plan->id])
            ->assertOk()
            ->assertJsonStructure(['snap_token', 'transaction_id']);

        $user->refresh();
        expect($user->subscription_tier)->toBe('free');
    });

    it('creates pending transaction for yearly plus', function (): void {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('tier', 'plus')->where('duration_days', 365)->first();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), ['plan_id' => $plan->id])
            ->assertOk()
            ->assertJsonStructure(['snap_token', 'transaction_id']);
    });

    it('creates pending transaction for monthly plus_plus', function (): void {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('tier', 'plus_plus')->where('duration_days', 30)->first();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), ['plan_id' => $plan->id])
            ->assertOk()
            ->assertJsonStructure(['snap_token', 'transaction_id']);
    });

    it('creates pending transaction for yearly plus_plus', function (): void {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::where('tier', 'plus_plus')->where('duration_days', 365)->first();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), ['plan_id' => $plan->id])
            ->assertOk()
            ->assertJsonStructure(['snap_token', 'transaction_id']);
    });

    it('prevents duplicate plus_plus subscription', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus_plus',
            'expires_at' => now()->addDays(30),
        ]);
        $plan = SubscriptionPlan::where('tier', 'plus')->where('duration_days', 30)->first();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), ['plan_id' => $plan->id])
            ->assertStatus(422);
    });

    it('validates plan_id is required', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('plan_id');
    });

    it('validates plan_id must exist', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('plus.subscribe'), ['plan_id' => 99999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('plan_id');
    });
});

describe('subscription limits', function (): void {
    it('enforces max lists using user helper', function (): void {
        $user = User::factory()->create();
        expect($user->maxLists())->toBe(0);

        $plus = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);
        expect($plus->maxLists())->toBe(7);

        $plusPlus = User::factory()->create([
            'subscription_tier' => 'plus_plus',
            'expires_at' => now()->addDays(30),
        ]);
        expect($plusPlus->maxLists())->toBe(15);
    });

    it('enforces max pinned using user helper', function (): void {
        $user = User::factory()->create();
        expect($user->maxPinned())->toBe(6);

        $plusPlus = User::factory()->create([
            'subscription_tier' => 'plus_plus',
            'expires_at' => now()->addDays(30),
        ]);
        expect($plusPlus->maxPinned())->toBe(12);
    });

    it('enforces max movies per list using user helper', function (): void {
        $user = User::factory()->create();
        expect($user->maxMoviesPerList())->toBe(0);

        $plus = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);
        expect($plus->maxMoviesPerList())->toBe(100);

        $plusPlus = User::factory()->create([
            'subscription_tier' => 'plus_plus',
            'expires_at' => now()->addDays(30),
        ]);
        expect($plusPlus->maxMoviesPerList())->toBe(-1);
    });
});

describe('admin subscription management', function (): void {
    it('requires admin to access', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.subscriptions.index'))
            ->assertNotFound();
    });

    it('lists subscribers', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.subscriptions.index'))
            ->assertOk()
            ->assertSee('Subscriptions');
    });

    it('grants plus subscription', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.grant'), [
                'user_id' => $user->id,
                'tier' => 'plus',
                'period' => 'monthly',
            ])
            ->assertRedirectToRoute('admin.subscriptions.index');

        $user->refresh();
        expect($user->subscription_tier)->toBe('plus');
    });

    it('grants plus_plus subscription', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.subscriptions.grant'), [
                'user_id' => $user->id,
                'tier' => 'plus_plus',
                'period' => 'yearly',
            ])
            ->assertRedirectToRoute('admin.subscriptions.index');

        $user->refresh();
        expect($user->subscription_tier)->toBe('plus_plus');
    });
});
