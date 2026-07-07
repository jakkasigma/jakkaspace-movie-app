<?php

namespace App\Http\Controllers;

use App\Models\RedeemCode;
use App\Models\SubscriptionTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedeemCodeController extends Controller
{
    public function redeem(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:16'],
        ]);

        $code = RedeemCode::where('code', $request->input('code'))->first();

        if (! $code || ! $code->isValid()) {
            return redirect()->back()->with('redeem_error', 'Kode tidak valid atau sudah habis masa berlakunya.');
        }

        $user = $request->user();

        $alreadyRedeemed = $code->redeemers()->where('user_id', $user->id)->exists();
        if ($alreadyRedeemed) {
            return redirect()->back()->with('redeem_error', 'Kamu sudah pernah menggunakan kode ini.');
        }

        // Track usage immediately
        $code->increment('used_count');
        $code->redeemers()->attach($user->id, ['redeemed_at' => now()]);

        if ($code->isFreeAccess()) {
            return $this->handleFreeAccess($code, $user);
        }

        return $this->handlePromo($code, $user);
    }

    private function handleFreeAccess(RedeemCode $code, $user): RedirectResponse
    {
        $tier = $code->tier;
        $days = $code->duration_days;

        if ($user->isPlusPlus() && $tier === 'plus') {
            return redirect()->back()->with('redeem_error', 'Kamu sudah berlangganan Plus+ — tier yang lebih tinggi. Kode Plus tidak dapat digunakan.');
        }

        $hasActiveSub = $user->expires_at && now()->lessThan($user->expires_at);
        $remainingDays = $hasActiveSub ? (int) now()->diffInDays($user->expires_at, false) : 0;

        $result = [
            'code' => $code->code,
            'tier' => $tier,
            'action' => 'subscribe',
            'from_tier' => null,
            'remaining_days' => $remainingDays,
            'code_days' => $days,
            'converted_days' => null,
            'total_days' => $days,
        ];

        $user->subscription_tier = $tier;
        $user->subscribed_at = now();

        if ($hasActiveSub) {
            $isUpgrade = ! $user->isPlusPlus() && $tier === 'plus_plus';

            if ($isUpgrade) {
                $convertedDays = (int) floor($remainingDays / 2);
                $totalDays = $convertedDays + $days;
                $user->expires_at = now()->addDays($totalDays);

                $result['action'] = 'upgrade';
                $result['from_tier'] = 'Plus';
                $result['converted_days'] = $convertedDays;
                $result['total_days'] = $totalDays;
            } else {
                $currentExpiry = $user->expires_at;
                $newExpiry = $currentExpiry->isFuture()
                    ? $currentExpiry->copy()->addDays($days)
                    : now()->addDays($days);
                $user->expires_at = $newExpiry;

                $result['action'] = 'renew';
                $result['total_days'] = $remainingDays + $days;
            }
        } else {
            $user->expires_at = now()->addDays($days);
        }

        $user->save();

        $result['expires_at'] = $user->expires_at->format('d M Y');

        SubscriptionTransaction::create([
            'user_id' => $user->id,
            'plan_id' => null,
            'tier' => $tier,
            'action' => $result['action'],
            'price' => 0,
            'payment_method' => 'redeem',
            'period_days' => $result['total_days'],
            'expires_at' => $user->expires_at,
            'notes' => "Redeem code (free): {$code->code}",
        ]);

        return redirect()->route('plus')->with('redeem_result', $result);
    }

    private function handlePromo(RedeemCode $code, $user): RedirectResponse
    {
        session()->flash('redeem_promo', [
            'code' => $code->code,
            'discount_type' => $code->discount_type,
            'discount_value' => $code->discount_value,
            'plan_id' => $code->plan_id,
            'popup_title' => $code->popup_title,
            'popup_message' => $code->popup_message,
        ]);

        return redirect()->route('plus', ['promo' => $code->code]);
    }
}
