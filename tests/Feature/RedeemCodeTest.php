<?php

use App\Models\RedeemCode;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;

beforeEach(function (): void {
    $this->seed(SubscriptionPlanSeeder::class);
});

describe('public redeem code', function (): void {
    it('requires auth to redeem', function (): void {
        $this->post(route('plus.redeem'), ['code' => 'TEST123'])
            ->assertRedirectToRoute('login');
    });

    it('redeems a valid free access code', function (): void {
        $user = User::factory()->create();
        $code = RedeemCode::factory()->create([
            'tier' => 'plus',
            'duration_days' => 30,
            'max_uses' => 5,
        ]);

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirectToRoute('plus');

        $user->refresh();
        expect($user->subscription_tier)->toBe('plus');
        expect($user->expires_at->isFuture())->toBeTrue();
        expect($code->fresh()->used_count)->toBe(1);
    });

    it('redeems a plus_plus code', function (): void {
        $user = User::factory()->create();
        $code = RedeemCode::factory()->plusPlus()->create(['max_uses' => 5]);

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirectToRoute('plus');

        $user->refresh();
        expect($user->subscription_tier)->toBe('plus_plus');
        expect($user->expires_at->isFuture())->toBeTrue();
    });

    it('rejects invalid code', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => 'INVALID'])
            ->assertRedirect()
            ->assertSessionHas('redeem_error');
    });

    it('rejects already redeemed code by same user', function (): void {
        $user = User::factory()->create();
        $code = RedeemCode::factory()->create(['max_uses' => 5]);
        $code->redeemers()->attach($user->id, ['redeemed_at' => now()]);

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirect()
            ->assertSessionHas('redeem_error');
    });

    it('rejects expired code', function (): void {
        $user = User::factory()->create();
        $code = RedeemCode::factory()->expired()->create();

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirect()
            ->assertSessionHas('redeem_error');
    });

    it('rejects exhausted code', function (): void {
        $user = User::factory()->create();
        $code = RedeemCode::factory()->exhausted()->create();

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirect()
            ->assertSessionHas('redeem_error');
    });

    it('rejects inactive code', function (): void {
        $user = User::factory()->create();
        $code = RedeemCode::factory()->inactive()->create();

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirect()
            ->assertSessionHas('redeem_error');
    });

    it('redeems promo code and redirects to plus with session', function (): void {
        $user = User::factory()->create();
        $plan = SubscriptionPlan::first();
        $code = RedeemCode::factory()->create([
            'type' => 'promo',
            'discount_type' => 'percent',
            'discount_value' => 25,
            'plan_id' => $plan->id,
            'max_uses' => 10,
        ]);

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirect(route('plus', ['promo' => $code->code]));

        expect(session()->has('redeem_promo'))->toBeTrue();
        expect(session('redeem_promo.code'))->toBe($code->code);
        expect(session('redeem_promo.discount_value'))->toBe(25);
        expect($code->fresh()->used_count)->toBe(1);
    });

    it('extends existing subscription cumulatively', function (): void {
        $user = User::factory()->create([
            'subscription_tier' => 'plus',
            'expires_at' => now()->addDays(10),
        ]);
        $code = RedeemCode::factory()->create([
            'tier' => 'plus_plus',
            'duration_days' => 30,
            'max_uses' => 5,
        ]);

        $this->actingAs($user)
            ->post(route('plus.redeem'), ['code' => $code->code])
            ->assertRedirectToRoute('plus');

        $user->refresh();
        expect($user->subscription_tier)->toBe('plus_plus');
        expect(now()->diffInDays($user->expires_at))->toBeGreaterThanOrEqual(39);
        expect(now()->diffInDays($user->expires_at))->toBeLessThanOrEqual(41);
    });

    it('requires code field', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('plus.redeem'), [])
            ->assertSessionHasErrors('code');
    });
});

describe('admin promo-redeem crud', function (): void {
    it('requires admin to access page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.promo-redeem.index'))
            ->assertNotFound();
    });

    it('lists promo-redeem page', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.promo-redeem.index'))
            ->assertOk()
            ->assertSee('Promo');
    });

    it('creates a free access redeem code', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.redeem-codes.store'), [
                'code' => 'SUMMER2024',
                'type' => 'free_access',
                'tier' => 'plus',
                'duration_days' => 30,
                'max_uses' => 100,
            ])
            ->assertRedirect();

        expect(RedeemCode::where('code', 'SUMMER2024')->where('type', 'free_access')->exists())->toBeTrue();
    });

    it('creates a promo-type redeem code', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $plan = SubscriptionPlan::first();

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.redeem-codes.store'), [
                'code' => 'HEMAT25',
                'type' => 'promo',
                'discount_type' => 'percent',
                'discount_value' => 25,
                'plan_id' => $plan->id,
                'max_uses' => 100,
            ])
            ->assertRedirect();

        $code = RedeemCode::where('code', 'HEMAT25')->first();
        expect($code)->not->toBeNull();
        expect($code->type)->toBe('promo');
        expect($code->discount_value)->toBe(25);
    });

    it('validates code uniqueness', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        RedeemCode::factory()->create(['code' => 'DUPLICATE']);

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.redeem-codes.store'), [
                'code' => 'DUPLICATE',
                'type' => 'free_access',
                'tier' => 'plus',
                'duration_days' => 30,
                'max_uses' => 10,
            ])
            ->assertSessionHasErrors('code');
    });

    it('shows code detail', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $code = RedeemCode::factory()->create(['code' => 'DETAIL01']);

        $this->actingAs($admin)
            ->get(route('admin.promo-redeem.redeem-codes.show', $code))
            ->assertOk()
            ->assertSee('DETAIL01');
    });

    it('disables a redeem code', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $code = RedeemCode::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.promo-redeem.redeem-codes.destroy', $code))
            ->assertRedirect();

        expect($code->fresh()->is_active)->toBeFalse();
    });

    it('activates a disabled redeem code', function (): void {
        $admin = User::factory()->create(['is_admin' => true]);
        $code = RedeemCode::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->post(route('admin.promo-redeem.redeem-codes.activate', $code))
            ->assertRedirect();

        expect($code->fresh()->is_active)->toBeTrue();
    });
});
