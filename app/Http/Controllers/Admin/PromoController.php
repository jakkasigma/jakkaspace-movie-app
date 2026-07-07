<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPromo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer|min:1|max:'.($request->type === 'percent' ? '100' : '999999'),
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'max_uses' => 'required|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'show_popup' => 'boolean',
            'popup_title' => 'nullable|string|max:100',
            'popup_message' => 'nullable|string',
        ]);

        $validated['show_popup'] = $request->boolean('show_popup');
        $validated['created_by'] = $request->user()->id;

        SubscriptionPromo::create($validated);

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', 'Promo created successfully.');
    }

    public function update(Request $request, SubscriptionPromo $promo): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|integer|min:1|max:'.($request->type === 'percent' ? '100' : '999999'),
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'max_uses' => 'required|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'show_popup' => 'boolean',
            'popup_title' => 'nullable|string|max:100',
            'popup_message' => 'nullable|string',
        ]);

        $validated['show_popup'] = $request->boolean('show_popup');

        $promo->update($validated);

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', 'Promo updated successfully.');
    }

    public function activate(SubscriptionPromo $promo): RedirectResponse
    {
        $promo->update(['is_active' => true]);

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', 'Promo activated successfully.');
    }

    public function destroy(SubscriptionPromo $promo): RedirectResponse
    {
        $promo->update(['is_active' => false]);

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', 'Promo disabled successfully.');
    }
}
