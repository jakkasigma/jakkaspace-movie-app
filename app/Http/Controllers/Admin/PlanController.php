<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Theme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::with('theme')->orderBy('sort_order')->get();
        $themes = Theme::where('is_active', true)->get();

        return view('admin.plans.index', compact('plans', 'themes'));
    }

    public function edit(SubscriptionPlan $plan): View
    {
        $themes = Theme::where('is_active', true)->get();

        return view('admin.plans.edit', compact('plan', 'themes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tier' => 'required|in:plus,plus_plus',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'theme_id' => 'nullable|exists:themes,id',
            'is_recommended' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_recommended'] = $request->boolean('is_recommended');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function update(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tier' => 'required|in:plus,plus_plus',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|integer|min:0',
            'theme_id' => 'nullable|exists:themes,id',
            'is_recommended' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $validated['is_recommended'] = $request->boolean('is_recommended');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => false]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan disabled successfully.');
    }

    public function toggleActive(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['is_active' => ! $plan->is_active]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan status toggled.');
    }
}
