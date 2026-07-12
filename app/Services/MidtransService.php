<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(array $params): string
    {
        return Snap::getSnapToken($params);
    }

    public function notificationHandler(): Notification
    {
        return new Notification;
    }

    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): bool
    {
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        return $signature === hash('sha512', $orderId.$statusCode.$grossAmount.config('midtrans.server_key'));
    }
}
