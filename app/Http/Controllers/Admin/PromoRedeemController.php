<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedeemCode;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPromo;
use Illuminate\Contracts\View\View;

class PromoRedeemController extends Controller
{
    public function index(): View
    {
        $promos = SubscriptionPromo::with('plan', 'creator')->latest()->get();
        $plans = SubscriptionPlan::active()->ordered()->get();

        $codes = RedeemCode::with('creator', 'plan')->latest()->paginate(20);

        return view('admin.promo-redeem.index', [
            'promos' => $promos,
            'plans' => $plans,
            'codes' => $codes,
            'totalCodes' => RedeemCode::count(),
            'totalActive' => RedeemCode::where('is_active', true)->count(),
            'totalRedeemed' => RedeemCode::sum('used_count'),
        ]);
    }
}
