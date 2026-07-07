<?php

namespace App\Http\Controllers;

use App\Models\RedeemCode;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPromo;
use App\Models\SubscriptionTransaction;
use App\Models\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PremiumController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $themes = Theme::where('is_active', true)->get();
        $daysLeft = ($user->isPlus() || $user->isPlusPlus()) && $user->expires_at
            ? (int) now()->diffInDays($user->expires_at, false)
            : 0;

        $plans = SubscriptionPlan::active()->ordered()->with('theme')->get();

        $promoPopup = null;
        if (! $user->isPlus() && ! session('promo_popup_dismissed', false)) {
            $promoPopup = SubscriptionPromo::with('plan')
                ->where('show_popup', true)
                ->where('is_active', true)
                ->get()
                ->first(fn ($promo) => $promo->isValid());
        }

        $redeemPromo = session('redeem_promo');

        return view('premium.index', [
            'user' => $user,
            'themes' => $themes,
            'daysLeft' => $daysLeft,
            'plans' => $plans,
            'promoPopup' => $promoPopup,
            'redeemPromo' => $redeemPromo,
        ]);
    }

    public function subscribe(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id,is_active,1',
            'payment_method' => 'required|in:gopay,qris,transfer_bank',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        $isPlusPlus = $plan->tier === 'plus_plus';
        $period = $plan->duration_days;

        if ($user->isPlusPlus() && ! $isPlusPlus) {
            return redirect()->back()->with('error', 'Kamu sudah berlangganan Plus+. Silakan pilih paket Plus+ untuk perpanjangan.');
        }

        $price = $plan->price;

        // Check for redeem_promo discount from session
        $redeemPromo = session('redeem_promo');
        $appliedPromo = null;

        if ($redeemPromo) {
            $discountType = $redeemPromo['discount_type'];
            $discountValue = $redeemPromo['discount_value'];
            $promoPlanId = $redeemPromo['plan_id'] ?? null;

            if (! $promoPlanId || $promoPlanId === $plan->id) {
                if ($discountType === 'percent') {
                    $price = max(0, (int) round($plan->price * (1 - $discountValue / 100)));
                } else {
                    $price = max(0, $plan->price - $discountValue);
                }
                $appliedPromo = $redeemPromo;
            }
        }

        // Auto promos (SubscriptionPromo) — 1× per user
        if (! $appliedPromo) {
            $autoPromo = SubscriptionPromo::where('is_active', true)->get()
                ->filter(fn ($p) => $p->isValid() && (! $p->plan_id || $p->plan_id === $plan->id) && $p->canUseBy($user))
                ->first();

            if ($autoPromo) {
                $price = $autoPromo->applyPrice($plan->price);
                $appliedPromo = $autoPromo;
            }
        }

        $hasActiveSub = $user->expires_at && $user->expires_at->isFuture();
        $remainingDays = $hasActiveSub ? (int) now()->diffInDays($user->expires_at, false) : 0;

        $result = [
            'tier' => $plan->tier,
            'plan_name' => $plan->name,
            'action' => 'subscribe',
            'from_tier' => null,
            'remaining_days' => $remainingDays,
            'plan_days' => $period,
            'converted_days' => null,
            'total_days' => $period,
        ];

        if ($hasActiveSub) {
            $isUpgrade = ! $user->isPlusPlus() && $isPlusPlus;

            if ($isUpgrade) {
                $convertedDays = (int) floor($remainingDays / 2);
                $totalDays = $convertedDays + $period;

                $user->update([
                    'subscription_tier' => 'plus_plus',
                    'expires_at' => now()->addDays($totalDays),
                ]);

                $result['action'] = 'upgrade';
                $result['from_tier'] = 'Plus';
                $result['converted_days'] = $convertedDays;
                $result['total_days'] = $totalDays;
            } else {
                $newExpiry = $user->expires_at->isFuture()
                    ? $user->expires_at->copy()->addDays($period)
                    : now()->addDays($period);

                $user->update([
                    'expires_at' => $newExpiry,
                ]);

                $result['action'] = 'renew';
                $result['total_days'] = $remainingDays + $period;
            }
        } else {
            $user->update([
                'subscription_tier' => $plan->tier,
                'subscribed_at' => now(),
                'expires_at' => now()->addDays($period),
            ]);
        }

        $freshUser = $user->fresh();
        $result['expires_at'] = $freshUser->expires_at->format('d M Y');

        $notes = null;
        $promoId = null;
        $promoCode = null;

        if ($appliedPromo) {
            if ($appliedPromo instanceof RedeemCode || isset($appliedPromo['code'])) {
                $promoCode = $appliedPromo['code'] ?? $appliedPromo->code;
                $notes = "Redeem promo: {$promoCode}";
            } else {
                $promoId = $appliedPromo->id;
                $notes = "Auto promo: {$appliedPromo->name}";

                $appliedPromo->increment('used_count');
                $user->subscriptionPromos()->attach($appliedPromo->id, [
                    'plan_id' => $plan->id,
                    'original_price' => $plan->price,
                    'discounted_price' => $price,
                    'code_used' => null,
                    'applied_at' => now(),
                ]);
            }
        }

        SubscriptionTransaction::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'tier' => $plan->tier,
            'action' => $result['action'],
            'price' => $price,
            'payment_method' => $validated['payment_method'],
            'promo_id' => $promoId,
            'promo_code' => $promoCode,
            'period_days' => $result['total_days'],
            'expires_at' => $freshUser->expires_at,
            'notes' => $notes,
        ]);

        session()->forget('redeem_promo');

        return redirect()->route('plus')->with('subscription_result', $result);
    }

    public function validatePromo(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:32',
            'plan_id' => 'required|exists:subscription_plans,id,is_active,1',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $redeemCode = RedeemCode::where('code', $request->code)->where('type', 'promo')->first();

        if (! $redeemCode || ! $redeemCode->isValid()) {
            return response()->json([
                'valid' => false,
                'error' => 'Kode promo tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        if ($redeemCode->redeemers()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'valid' => false,
                'error' => 'Kamu sudah pernah menggunakan kode promo ini.',
            ]);
        }

        if ($redeemCode->plan_id && $redeemCode->plan_id !== $plan->id) {
            return response()->json([
                'valid' => false,
                'error' => 'Kode promo tidak berlaku untuk paket ini.',
            ]);
        }

        $originalPrice = $plan->price;
        $discountedPrice = $redeemCode->applyPrice($originalPrice);
        $discountLabel = $redeemCode->discount_type === 'percent'
            ? "{$redeemCode->discount_value}%"
            : 'Rp'.number_format($redeemCode->discount_value, 0, ',', '.');

        return response()->json([
            'valid' => true,
            'promo_name' => $redeemCode->code,
            'discount_label' => $discountLabel,
            'original_price' => $originalPrice,
            'discounted_price' => $discountedPrice,
            'error' => null,
        ]);
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isPlus(), 403);

        $themeId = $request->integer('theme_id') ?: null;

        if ($themeId !== null && ! Theme::where('id', $themeId)->exists()) {
            return redirect()->back()->with('error', 'Tema tidak ditemukan.');
        }

        $user->update(['theme_id' => $themeId]);

        $msg = $themeId ? 'Tema berhasil dipilih.' : 'Tema Default diterapkan.';

        return redirect()->back()->with('success', $msg);
    }

    public function history(Request $request): View
    {
        $transactions = $request->user()
            ->subscriptionTransactions()
            ->with('plan', 'promo')
            ->latest()
            ->paginate(20);

        return view('premium.history', ['transactions' => $transactions]);
    }

    public function simulatePayment(Request $request): View
    {
        $user = $request->user();
        $planId = $request->integer('plan_id') ?: null;
        $method = $request->string('payment_method')->value() ?: 'gopay';

        $plan = $planId ? SubscriptionPlan::find($planId) : SubscriptionPlan::active()->ordered()->first();

        $methodLabels = [
            'gopay' => 'GoPay',
            'qris' => 'QRIS',
            'transfer_bank' => 'Transfer Bank',
        ];

        $price = $plan?->price ?? 0;
        $promoDiscount = null;

        $autoPromo = SubscriptionPromo::where('is_active', true)->get()
            ->filter(fn ($p) => $p->isValid() && (! $p->plan_id || ($plan && $p->plan_id === $plan->id)))
            ->first();

        if ($autoPromo && $plan) {
            $discounted = $autoPromo->applyPrice($plan->price);
            if ($discounted < $plan->price) {
                $promoDiscount = [
                    'name' => $autoPromo->name,
                    'label' => $autoPromo->type === 'percent' ? "{$autoPromo->value}%" : 'Rp'.number_format($autoPromo->value, 0, ',', '.'),
                    'original' => $plan->price,
                    'discounted' => $discounted,
                ];
                $price = $discounted;
            }
        }

        return view('premium.simulate', [
            'user' => $user,
            'plan' => $plan,
            'price' => $price,
            'method' => $method,
            'methodLabel' => $methodLabels[$method],
            'promoDiscount' => $promoDiscount,
        ]);
    }
}
