<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedeemCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedeemCodeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'free_access');

        $baseRules = [
            'code' => ['required', 'string', 'min:4', 'max:16', 'regex:/^[a-zA-Z0-9\-_]+$/', 'unique:redeem_codes,code'],
            'max_uses' => ['required', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
        ];

        if ($type === 'free_access') {
            $validated = $request->validate(array_merge($baseRules, [
                'type' => ['required', 'in:free_access'],
                'tier' => ['required', 'in:plus,plus_plus'],
                'duration_days' => ['required', 'integer', 'in:30,365'],
            ]));

            RedeemCode::create([
                'code' => $validated['code'],
                'type' => 'free_access',
                'tier' => $validated['tier'],
                'duration_days' => $validated['duration_days'],
                'max_uses' => $validated['max_uses'],
                'created_by' => $request->user()->id,
                'expires_at' => $validated['expires_at'] ?? null,
            ]);
        } else {
            $validated = $request->validate(array_merge($baseRules, [
                'type' => ['required', 'in:promo'],
                'discount_type' => ['required', 'in:percent,fixed'],
                'discount_value' => ['required', 'integer', 'min:1', 'max:'.($request->discount_type === 'percent' ? '100' : '999999')],
                'plan_id' => ['nullable', 'exists:subscription_plans,id'],
                'show_popup' => ['boolean'],
                'popup_title' => ['nullable', 'string', 'max:100'],
                'popup_message' => ['nullable', 'string'],
            ]));

            RedeemCode::create([
                'code' => $validated['code'],
                'type' => 'promo',
                'tier' => 'plus',
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'plan_id' => $validated['plan_id'] ?? null,
                'max_uses' => $validated['max_uses'],
                'show_popup' => $request->boolean('show_popup'),
                'popup_title' => $validated['popup_title'] ?? null,
                'popup_message' => $validated['popup_message'] ?? null,
                'created_by' => $request->user()->id,
                'expires_at' => $validated['expires_at'] ?? null,
            ]);
        }

        $label = $type === 'free_access' ? 'Kode redeem' : 'Kode promo';

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', "{$label} {$validated['code']} berhasil dibuat.");
    }

    public function show(RedeemCode $redeemCode): View
    {
        $redeemCode->load('creator', 'redeemers', 'plan');

        return view('admin.redeem-codes.show', [
            'code' => $redeemCode,
        ]);
    }

    public function activate(RedeemCode $redeemCode): RedirectResponse
    {
        $redeemCode->update(['is_active' => true]);

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', "Kode {$redeemCode->code} diaktifkan kembali.");
    }

    public function destroy(RedeemCode $redeemCode): RedirectResponse
    {
        $redeemCode->update(['is_active' => false]);

        return redirect()->route('admin.promo-redeem.index')
            ->with('success', "Kode {$redeemCode->code} dinonaktifkan.");
    }
}
