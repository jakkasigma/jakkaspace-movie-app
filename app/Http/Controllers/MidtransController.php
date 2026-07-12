<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionTransaction;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MidtransController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
    ) {}

    public function notification(Request $request): JsonResponse
    {
        try {
            $notif = $this->midtrans->notificationHandler();

            $orderId = $notif->order_id;
            $transactionStatus = $notif->transaction_status;
            $paymentType = $notif->payment_type;
            $fraudStatus = $notif->fraud_status;

            $transaction = SubscriptionTransaction::find((int) str_replace('PLUS-', '', $orderId));

            if (! $transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            if ($transaction->status === 'paid') {
                return response()->json(['ok' => true]);
            }

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'challenge') {
                    $transaction->update(['status' => 'challenge', 'notes' => 'Challenge by Midtrans']);
                } elseif ($fraudStatus === 'accept') {
                    $this->activateTransaction($transaction, $paymentType);
                }
            } elseif ($transactionStatus === 'settlement') {
                $this->activateTransaction($transaction, $paymentType);
            } elseif ($transactionStatus === 'pending') {
                $transaction->update(['status' => 'pending']);
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $transaction->update(['status' => 'failed']);
            }

            return response()->json(['ok' => true]);
        } catch (\Throwable $th) {
            report($th);

            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function finish(Request $request): View|RedirectResponse
    {
        $orderId = $request->query('order_id');
        $transactionStatus = $request->query('transaction_status');
        $transaction = $orderId
            ? SubscriptionTransaction::find((int) str_replace('PLUS-', '', $orderId))
            : null;

        if ($transaction && $transaction->status === 'paid') {
            return view('premium.finish', [
                'status' => 'success',
                'transaction' => $transaction,
                'message' => 'Pembayaran berhasil! Subscription kamu sudah aktif.',
            ]);
        }

        if ($transactionStatus === 'pending') {
            return view('premium.finish', [
                'status' => 'pending',
                'transaction' => $transaction,
                'message' => 'Pembayaran sedang diproses. Cek status pembayaran di halaman riwayat.',
            ]);
        }

        return view('premium.finish', [
            'status' => 'error',
            'transaction' => $transaction,
            'message' => $transaction
                ? 'Pembayaran gagal. Silakan coba lagi.'
                : 'Transaksi tidak ditemukan.',
        ]);
    }

    private function activateTransaction(SubscriptionTransaction $transaction, string $paymentType): void
    {
        $user = $transaction->user;
        $plan = $transaction->plan;
        $period = $transaction->period_days;

        if ($transaction->action === 'upgrade') {
            $user->update([
                'subscription_tier' => 'plus_plus',
                'expires_at' => now()->addDays($period),
            ]);
        } elseif ($transaction->action === 'renew') {
            $newExpiry = $user->expires_at && $user->expires_at->isFuture()
                ? $user->expires_at->copy()->addDays($period)
                : now()->addDays($period);

            $user->update(['expires_at' => $newExpiry]);
        } else {
            $user->update([
                'subscription_tier' => $plan->tier,
                'subscribed_at' => now(),
                'expires_at' => now()->addDays($period),
            ]);
        }

        $transaction->update([
            'status' => 'paid',
            'payment_method' => $paymentType,
            'paid_at' => now(),
        ]);

        // Clean up promo session
        session()->forget('redeem_promo');
    }
}
