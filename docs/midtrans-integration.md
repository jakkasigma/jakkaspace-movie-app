# Midtrans Snap Integration — Plus Subscription

## Flow
```
User pilih plan + promo → Klik Bayar → Backend create Snap token →
Popup Midtrans → User bayar (GoPay/QRIS/CC/Bank Transfer) →
Midtrans kirim notif webhook → Aktivasi subscription
```

## File Baru

| File | Fungsi |
|---|---|
| `config/midtrans.php` | Config server key, client key, is production |
| `app/Services/MidtransService.php` | Service: create Snap token, verify signature, parse notification |
| `app/Http/Controllers/MidtransController.php` | Webhook handler + finish page |

## File Diubah

| File | Perubahan |
|---|---|
| `.env` | Tambah MIDTRANS_SERVER_KEY, MIDTRANS_CLIENT_KEY, MIDTRANS_IS_PRODUCTION |
| `composer.json` | Tambah `midtrans/midtrans-php` |
| `routes/web.php` | Route webhook (POST, no CSRF), finish page (GET) |
| `app/Http/Controllers/PremiumController.php` | subscribe() → create Snap token instead of direct activation |
| `resources/views/premium/index.blade.php` | Load snap.js, update payment modal JS, handle popup response |

## Steps

### 1. Install Package
```bash
composer require midtrans/midtrans-php
```

### 2. Environment (.env)
```
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
```

### 3. Config (config/midtrans.php)
```php
return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
];
```

### 4. Service (app/Services/MidtransService.php)
- createSnapToken(array $params): string
- verifySignature(orderId, statusCode, grossAmount, serverKey): bool
- notificationHandler(): array

### 5. Subscribe Refactor
Before: redirect + langsung aktivasi
After: AJAX → create Snap token → popup Midtrans → webhook → aktivasi

### 6. Webhook
POST /payment/notification (tanpa CSRF)
Parse notif midtrans, settlement → aktivasi subscription

### 7. Midtrans Dashboard Setup
- Settings → Snap Preference → atur payment methods
- Settings → Notification URL: https://domainkamu.com/payment/notification

## Timeline
~1.5 jam total
