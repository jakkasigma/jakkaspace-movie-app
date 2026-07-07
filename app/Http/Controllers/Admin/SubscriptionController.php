<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GrantPlusRequest;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function transactions(Request $request): View
    {
        $query = SubscriptionTransaction::with('user', 'plan', 'promo', 'admin');

        $search = $request->string('q')->value();
        if ($search) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $action = $request->string('action')->value();
        if ($action && in_array($action, ['subscribe', 'renew', 'upgrade', 'cancel', 'admin_grant', 'admin_extend', 'redeem'])) {
            $query->where('action', $action);
        }

        $transactions = $query->latest()->paginate(30);

        return view('admin.subscriptions.transactions', [
            'transactions' => $transactions,
            'search' => $search,
            'actionFilter' => $action,
        ]);
    }

    public function index(Request $request): View
    {
        $query = User::whereIn('subscription_tier', ['plus', 'plus_plus']);

        $tierFilter = $request->string('tier')->value();
        if ($tierFilter && in_array($tierFilter, ['plus', 'plus_plus'])) {
            $query->where('subscription_tier', $tierFilter);
        }

        $search = $request->string('q')->value();
        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->latest('subscribed_at')->paginate(20);

        $totalActive = User::whereIn('subscription_tier', ['plus', 'plus_plus'])
            ->where('expires_at', '>=', now())
            ->count();
        $totalExpired = User::whereIn('subscription_tier', ['plus', 'plus_plus'])
            ->where('expires_at', '<', now())
            ->count();

        $activePlus = User::where('subscription_tier', 'plus')
            ->where('expires_at', '>=', now())
            ->count();
        $activePlusPlus = User::where('subscription_tier', 'plus_plus')
            ->where('expires_at', '>=', now())
            ->count();

        $plusMinPrice = SubscriptionPlan::where('tier', 'plus')->active()->min('price') ?? 15000;
        $plusPlusMinPrice = SubscriptionPlan::where('tier', 'plus_plus')->active()->min('price') ?? 30000;
        $monthlyRevenue = ($activePlus * $plusMinPrice) + ($activePlusPlus * $plusPlusMinPrice);

        $freeUsers = User::whereNull('subscription_tier')->orWhere('subscription_tier', 'free')->orderBy('name')->get();

        return view('admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'totalActive' => $totalActive,
            'totalExpired' => $totalExpired,
            'monthlyRevenue' => $monthlyRevenue,
            'freeUsers' => $freeUsers,
            'search' => $search,
            'tierFilter' => $tierFilter,
        ]);
    }

    public function grant(GrantPlusRequest $request): RedirectResponse
    {
        $user = User::findOrFail($request->validated('user_id'));
        $period = $request->validated('period');
        $tier = $request->validated('tier');

        $days = $period === 'monthly' ? 30 : 365;
        $expiresAt = now()->addDays($days);

        $user->update([
            'subscription_tier' => $tier,
            'subscribed_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        SubscriptionTransaction::create([
            'user_id' => $user->id,
            'plan_id' => null,
            'tier' => $tier,
            'action' => 'admin_grant',
            'price' => 0,
            'payment_method' => null,
            'period_days' => $days,
            'expires_at' => $expiresAt,
            'admin_id' => $request->user()->id,
            'notes' => "Granted by admin for {$period}",
        ]);

        $label = $tier === 'plus_plus' ? 'Plus+' : 'Plus';

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "{$label} granted to {$user->name} for {$period}.");
    }

    public function cancel(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->subscription_tier, ['plus', 'plus_plus'])) {
            return redirect()->route('admin.subscriptions.index')
                ->with('error', "{$user->name} is not a subscribed user.");
        }

        $tier = $user->subscription_tier;

        $user->update([
            'subscription_tier' => 'free',
            'expires_at' => null,
            'theme_id' => null,
        ]);

        SubscriptionTransaction::create([
            'user_id' => $user->id,
            'tier' => $tier,
            'action' => 'cancel',
            'price' => 0,
            'period_days' => 0,
            'admin_id' => $request->user()->id,
            'notes' => 'Cancelled by admin',
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription for {$user->name} has been cancelled.");
    }

    public function extend(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->subscription_tier, ['plus', 'plus_plus'])) {
            return redirect()->back()->with('error', 'User is not subscribed.');
        }

        $days = (int) $request->integer('days', 30);
        $newExpiry = $user->expires_at && $user->expires_at->isFuture()
            ? $user->expires_at->copy()->addDays($days)
            : now()->addDays($days);

        $user->update(['expires_at' => $newExpiry]);

        $label = $user->subscription_tier === 'plus_plus' ? 'Plus+' : 'Plus';

        SubscriptionTransaction::create([
            'user_id' => $user->id,
            'tier' => $user->subscription_tier,
            'action' => 'admin_extend',
            'price' => 0,
            'period_days' => $days,
            'expires_at' => $newExpiry,
            'admin_id' => $request->user()->id,
            'notes' => "Extended by {$days} days (admin)",
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "{$label} extended by {$days} days for {$user->name}.");
    }
}
