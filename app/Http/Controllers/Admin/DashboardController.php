<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::count();
        $totalPlus = User::whereIn('subscription_tier', ['plus', 'plus_plus'])
            ->where('expires_at', '>=', now())
            ->count();
        $totalThemes = Theme::count();

        $plusMinPrice = SubscriptionPlan::where('tier', 'plus')->active()->min('price') ?? 15000;
        $plusPlusMinPrice = SubscriptionPlan::where('tier', 'plus_plus')->active()->min('price') ?? 30000;

        $activePlus = User::where('subscription_tier', 'plus')
            ->where('expires_at', '>=', now())
            ->count();
        $activePlusPlus = User::where('subscription_tier', 'plus_plus')
            ->where('expires_at', '>=', now())
            ->count();

        $monthlyRevenue = ($activePlus * $plusMinPrice) + ($activePlusPlus * $plusPlusMinPrice);

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalPlus' => $totalPlus,
            'totalThemes' => $totalThemes,
            'monthlyRevenue' => $monthlyRevenue,
            'recentPlusUsers' => User::whereIn('subscription_tier', ['plus', 'plus_plus'])
                ->latest('subscribed_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
