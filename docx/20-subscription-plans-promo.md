# Phase 9 — Subscription Plans & Promo System

> Created: 2026-07-06
> Status: ✅ **Done** (executed 2026-07-07)
> Prerequisite: 19-plus-subscription-revamp ✅
>
> ---
>
> ## Perubahan Implementasi dari Rencana Awal
>
> ### 1. Kode promo dipindah ke Redeem Codes
> - `subscription_promos.code` **dihapus** (migration `2026_07_07_000002`).
> - Promo sekarang **2 tipe**: **Promo Campaign** (dengan popup) & **Diskon Plan** (langsung ke plan).
> - Semua kode (promo/free access) dikelola di `redeem_codes` dengan field `type` baru (`free_access` / `promo`).
>
> ### 2. Menu Admin digabung
> - Menu "Promos" + "Redeem Codes" digabung jadi **"🎯 Promo & Redeem"**.
> - Di dalamnya ada 2 tab: **📢 Promo** dan **🎟️ Redeem Codes**.
>
> ### 3. Redeem Codes — 2 tipe
> - **`free_access`** (existing): kode akses gratis Plus/Plus+ (tier + durasi).
> - **`promo`** (baru): kode diskon (discount_type, discount_value, plan_id, popup).
>
> ### 4. Promo Campaign popup — tanpa kode
> - Tidak ada kotak kode di popup campaign (karena `code` dihapus dari `subscription_promos`).
> - Teks: "✨ Diskon otomatis — langsung terlihat di harga".
> - Tombol: **[→ Lihat Paket Plus]** + **[✕]** close.
> - Juga muncul di `/plus` (sebelumnya cuma di `/your-space`).
>
> ### 5. Redeem Code Kode Promo (type=promo)
> - Flow: redeem → catat pivot → set session(`redeem_promo`) → redirect `/plus?promo=KODE`.
> - Popup dari session, bukan dari DB.
> - Kode terisi otomatis di payment modal via JS param `?promo=KODE`.
> - Subscribe cek session → apply discount → hapus session.
>
> ### 6. Auto promo 1× per user
> - `canUseBy($user)` dicek sebelum apply auto promo.
> - Pivot `subscription_promo_user` dicatat.
> - `used_count` di-increment.
>
> ### 7. Riwayat transaksi (`subscription_transactions`)
> - Setiap subscribe/renew/upgrade/admin_grant/admin_extend/redeem dicatat.
> - User: `/plus/history`, Admin: `/admin/subscriptions/transactions`.
>
> ### 8. Cover List Custom (Plus+)
> - Upload cover di form create/edit list (`storage/covers/`).
> - Ditampilkan di detail list, kartu list, profile tab.
> - Gated dengan `User::canUploadCover()` → isPlusPlus().
>
> ### 9. Edit & Activate Promo/Redeem
> - **Edit**: modal pre-filled via JS + `PUT /promo-redeem/promos/{promo}`.
> - **Activate**: `POST /promo-redeem/promos/{promo}/activate` — set `is_active = true`.
> - Tombol Activate muncul kalo item nonaktif.
>
> ### 10. Admin responsif mobile
> - Hamburger ☰ → sidebar drawer slide dari kiri.
> - Backdrop overlay, body scroll lock.
> - Tabel horizontal scroll, form stack, tombol full-width di HP.
>
> ### 11. Midtrans payment gateway
> - **Skip** (belum punya API key). Simulasi via `/plus/simulate`.

---

## Visi

Memberi admin kontrol penuh atas subscription plans (CRUD) dan sistem promo (diskon persen / harga tetap) yang tampil di halaman `/plus` dan `/your-space`.

---

## Arsitektur

### Relasi

```
subscription_plans
    ├── tier: plus / plus_plus
    └── theme_id → themes (nullable)

subscription_promos
    ├── plan_id → subscription_plans (nullable = semua plan)
    └── code: unique nullable (null = promo otomatis)

subscription_promo_user (pivot)
    └── [promo_id, user_id] unique
```

---

## 1. Database — Migration

### `subscription_plans`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigIncrements | |
| `name` | string(100) | "Plus Bulanan", "Plus+ Tahunan" |
| `tier` | string(20) | `plus` / `plus_plus` |
| `duration_days` | unsignedSmallInteger | 30, 90, 180, 365 |
| `price` | unsignedInteger | Harga **manual** (admin isi bebas) |
| `theme_id` | FK → themes, nullable | Border/theme yg dikasih ke user pas subscribe |
| `is_recommended` | boolean | Centang → tampilkan border "Terbaik" |
| `sort_order` | smallInteger | Urutan tampil di halaman pricing |
| `is_active` | boolean | default true |
| `timestamps` | | |

### `subscription_promos`

> Kolom `code` **dihapus** via migration `2026_07_07_000002`. Semua kode promo dipindah ke `redeem_codes`.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigIncrements | |
| `name` | string(100) | "Diskon Lebaran" |
| `type` | string(10) | `percent` / `fixed` |
| `value` | unsignedSmallInteger | 25 (persen) atau 50000 (harga tetap) |
| `plan_id` | FK → subscription_plans, nullable | null = berlaku untuk semua plan |
| `max_uses` | unsignedSmallInteger | 0 = unlimited |
| `used_count` | unsignedSmallInteger | default 0 |
| `starts_at` | datetime, nullable | |
| `expires_at` | datetime, nullable | |
| `show_popup` | boolean | Tampilkan popup ke user |
| `popup_title` | string(100), nullable | |
| `popup_message` | text, nullable | |
| `is_active` | boolean | default true |
| `created_by` | FK → users | Admin pembuat |
| `timestamps` | | |

### `subscription_promo_user` (pivot tracking)

| Kolom | Tipe |
|-------|------|
| `subscription_promo_id` | FK → subscription_promos (cascade) |
| `user_id` | FK → users (cascade) |
| `plan_id` | FK → subscription_plans |
| `original_price` | unsignedInteger |
| `discounted_price` | unsignedInteger |
| `code_used` | string(32), nullable | Kode yg diinput user (kalo ada) |
| `applied_at` | timestamp |

Unique: `[subscription_promo_id, user_id]` — 1 user cuma bisa pake 1 promo sekali.

---

## 2. Model

### `SubscriptionPlan`

| Method | Keterangan |
|--------|------------|
| `theme(): BelongsTo` | Relasi ke Theme |
| `scopeActive()` | `where('is_active', true)` |
| `scopeOrdered()` | `orderBy('sort_order')` |
| `tierLabel(): string` | 'Plus' / 'Plus+' |
| `priceFormatted(): string` | 'Rp15.000' |

### `SubscriptionPromo`

> `scopeAuto()` dan `scopeWithCode()` **dihapus** — kolom `code` sudah tidak ada.

| Method | Keterangan |
|--------|------------|
| `plan(): BelongsTo` | Relasi ke SubscriptionPlan (nullable) |
| `users(): BelongsToMany` | Pivot tracking |
| `creator(): BelongsTo` | Admin pembuat |
| `isValid(): bool` | Cek is_active, date range, max_uses |
| `canUseBy(User $user): bool` | Cek belum pernah pake (pivot) — 1× per user |
| `applyPrice(int $originalPrice): int` | Hitung harga final |

---

## 3. Admin CRUD

### `Admin/PlanController`

| Method | Fungsi |
|--------|--------|
| `index()` | Daftar semua plan + toggle active |
| `store()` | Validasi → create plan |
| `update()` | Edit plan |
| `destroy()` | Set is_active = false |

**Form fields:**
- name (required)
- tier (required: plus / plus_plus)
- duration_days (required: integer)
- price (required: integer, min 0)
- theme_id (optional: select dari themes)
- is_recommended (boolean)
- sort_order (integer)

### `Admin/PromoController`

| Method | Fungsi |
|--------|--------|
| `index()` | Daftar semua promo + stat |
| `store()` | Validasi → create promo |
| `destroy()` | Nonaktifkan (set is_active = false) |

**Form fields:**
- name (required)
- code (nullable, unique kalo diisi)
- type (required: percent / fixed)
- value (required: 1-100 kalo percent, min 0 kalo fixed)
- plan_id (nullable)
- max_uses (integer, 0 = unlimited)
- starts_at / expires_at (nullable datetime)
- show_popup (boolean)
- popup_title / popup_message (nullable)

### Routes admin.php (aktual — menu Promo & Redeem digabung)
```php
// Promos
Route::post('/promo-redeem/promos', [PromoController::class, 'store'])->name('promo-redeem.promos.store');
Route::put('/promo-redeem/promos/{promo}', [PromoController::class, 'update'])->name('promo-redeem.promos.update');
Route::post('/promo-redeem/promos/{promo}/activate', [PromoController::class, 'activate'])->name('promo-redeem.promos.activate');
Route::delete('/promo-redeem/promos/{promo}', [PromoController::class, 'destroy'])->name('promo-redeem.promos.destroy');

// Redeem codes (free_access + promo)
Route::post('/promo-redeem/redeem-codes', [RedeemCodeController::class, 'store'])->name('promo-redeem.redeem-codes.store');
Route::get('/promo-redeem/redeem-codes/{redeemCode}', [RedeemCodeController::class, 'show'])->name('promo-redeem.redeem-codes.show');
Route::post('/promo-redeem/redeem-codes/{redeemCode}/activate', [RedeemCodeController::class, 'activate'])->name('promo-redeem.redeem-codes.activate');
Route::delete('/promo-redeem/redeem-codes/{redeemCode}', [RedeemCodeController::class, 'destroy'])->name('promo-redeem.redeem-codes.destroy');
```

---

## 4. Frontend — Halaman `/plus`

### 4a. Pricing Cards (dari DB)

```blade
@foreach ($plans as $plan)
    <div class="plus-plan-card {{ $plan->is_recommended ? 'plus-plan-featured' : '' }}">
        @if ($plan->is_recommended)
            <div class="plus-plan-badge">Terbaik</div>
        @endif
        <h3>{{ $plan->name }}</h3>
        <p class="plus-plan-price">
            @if ($plan->hasActiveAutoPromo())
                <span class="plus-price-original">Rp{{ number_format($plan->price) }}</span>
                Rp{{ number_format($plan->discountedPrice()) }}
            @else
                Rp{{ number_format($plan->price) }}
            @endif
        </p>
        ...
    </div>
@endforeach
```

### 4b. Strikethrough Price

Kalo ada promo otomatis aktif yg cocok sama plan:
```
Rp30.000          ← harga asli (dicoret, warna abu)
Rp22.500          ← harga setelah diskon (warna emas/menonjol)
[Diskon 25% — Promo Lebaran]  ← label kecil hijau/ungu
```

### 4c. Input Kode Promo di Payment Modal

> **Validasi kode promo sekarang cek `RedeemCode` (type=promo), bukan `SubscriptionPromo`.**

Di modal payment, ada section input kode:
```
┌──────────────────────────────┐
│ Pilih Metode Pembayaran       │
│ [💳 GoPay] [📱 QRIS] [🏦 B]  │
│                               │
│ ── Promo ──                   │
│ [Kode promo________] [Pakai] │ ← AJAX validasi
│ Diskon: 25% → -Rp7.500       │ ← muncul setelah valid
│ Total: Rp22.500               │
│                               │
│ [     Bayar Rp22.500     ]    │
│ [          Batal          ]   │
└──────────────────────────────┘
```

**JS flow:**
1. User klik "Pakai" → POST AJAX ke route `promo.validate`
2. Backend cek `RedeemCode` type=promo → return json `{valid, discount_label, final_price, error?}`
3. Update UI: tampilkan diskon + harga final
4. Hidden input `promo_code` dikirim bareng form subscribe

### 4d. Promo Popup (2 tipe)

Ada **2 sumber** popup promo:

**1. Promo Campaign (dari `SubscriptionPromo`):**
- Cek di `index()` method: promo aktif dgn `show_popup = true`.
- Tampil untuk free user yg belum dismiss.
- Tanpa kode — diskon otomatis langsung strikethrough di harga.

```
┌──────────────────────────────┐
│ ✕                           │
│ 🎉                          │
│ <popup_title / name>         │
│                              │
│ <popup_message / fallback>   │
│                              │
│ ✨ Diskon otomatis —         │
│ langsung terlihat di harga   │
│                              │
│ [→ Lihat Paket Plus]         │
└──────────────────────────────┘
```

**2. Redeem Promo (dari `RedeemCode` type=promo):**
- Muncul setelah user redeem kode promo di `/plus`.
- Data dari session `redeem_promo`, bukan dari DB.
- Ada kotak kode yg bisa di-copy.

```
┌──────────────────────────────┐
│ ✕                           │
│ 🎉                          │
│ Kode Promo Berhasil!         │
│                              │
│ Dapatkan diskon 25%!         │
│                              │
│ ┌────────────────────────┐   │
│ │ BARU25                 │   │
│ └────────────────────────┘   │
│                              │
│ [→ Lihat Paket Plus]         │
└──────────────────────────────┘
```

**JS auto-fill:** Kalo URL ada param `?promo=KODE`, kode terisi otomatis + validasi di `/plus`.

---

## 5. Backend — Apply Promo

### `PremiumController::subscribe()` — flow aktual

```
1. Validasi plan dari request
2. Cek session('redeem_promo') — dari redeem kode promo
   → apply discount via RedeemCode::applyPrice()
3. Kalo tidak ada → cari auto promo (SubscriptionPromo)
   → filter: isValid() + plan cocok + canUseBy($user) — 1× per user
   → apply discount via SubscriptionPromo::applyPrice()
4. Simpan subscription
5. Record pivot subscription_promo_user (kalo auto promo)
6. Catat subscription_transaction
7. Hapus session('redeem_promo')
8. Redirect /plus → success popup
```

### Route `promo.validate` (AJAX)

```php
Route::post('/plus/promo/validate', [PremiumController::class, 'validatePromo'])
    ->middleware('auth')->name('plus.promo.validate');
```

Validasi cek `RedeemCode` (type=promo), bukan `SubscriptionPromo`. Return JSON:
```json
{
    "valid": true,
    "promo_name": "BARU25",
    "discount_label": "25%",
    "original_price": 30000,
    "discounted_price": 22500,
    "error": null
}
```

---

## 6. Halaman `/your-space` — Promo Popup

Di `SpaceController::index()` atau langsung di view:
- Cek promo aktif dgn `show_popup = true`
- Kalo ada dan flag session belum dismiss → tampilkan modal yg sama

---

## 7. Simulate Page

Tambahkan baris diskon kalo ada:
```
Paket:    Plus+ Bulanan
Harga:    Rp30.000
Promo:    25% (-Rp7.500)
Total:    Rp22.500
Metode:   GoPay
```

---

## 8. Seeder

Default 6 plans supaya gak kosong pas migration:

| Name | Tier | Durasi | Harga | Recommended |
|------|------|--------|-------|-------------|
| Plus Bulanan | plus | 30 | 15.000 | |
| Plus 3 Bulan | plus | 90 | 45.000 | |
| Plus Tahunan | plus | 365 | 150.000 | ✅ |
| Plus+ Bulanan | plus_plus | 30 | 30.000 | |
| Plus+ 3 Bulan | plus_plus | 90 | 90.000 | |
| Plus+ Tahunan | plus_plus | 365 | 300.000 | ✅ |

---

## 9. Files (Aktual)

### Migration Baru (di luar rencana awal)

| File | Keterangan |
|------|------------|
| `2026_07_07_000002_drop_code_from_subscription_promos.php` | Hapus kolom `code` dari `subscription_promos` |
| `2026_07_07_000003_add_type_to_redeem_codes.php` | Tambah kolom `type`, `discount_type`, `discount_value`, `plan_id`, `popup_title`, `popup_message` ke `redeem_codes` |

### File Baru

| File | Fungsi |
|------|--------|
| `database/migrations/XXXX_create_subscription_plans_table.php` | Tabel plans |
| `database/migrations/XXXX_create_subscription_promos_table.php` | Tabel promos |
| `database/migrations/XXXX_create_subscription_promo_user_table.php` | Pivot tracking |
| `database/migrations/XXXX_create_subscription_transactions_table.php` | Riwayat transaksi |
| `database/seeders/SubscriptionPlanSeeder.php` | 6 default plans |
| `app/Models/SubscriptionPlan.php` | Model + relasi |
| `app/Models/SubscriptionPromo.php` | Model + isValid, canUseBy, applyPrice (scopes code dihapus) |
| `app/Models/SubscriptionTransaction.php` | Model riwayat transaksi |
| `app/Http/Controllers/Admin/PlanController.php` | Admin CRUD plans |
| `app/Http/Controllers/Admin/PromoController.php` | Admin CRUD promos (tanpa code) |
| `app/Http/Controllers/Admin/PromoRedeemController.php` | Controller halaman gabungan Promo & Redeem |
| `app/Http/Controllers/Admin/RedeemCodeController.php` | Admin CRUD redeem codes (type free_access + promo) |
| `app/Console/Commands/MigrateSubscriptionHistory.php` | Migrasi data promo_user + redeem_code_user ke transactions |
| `resources/views/admin/promo-redeem/index.blade.php` | Halaman gabungan Promo & Redeem (2 tab) |
| `resources/views/admin/redeem-codes/show.blade.php` | Detail redeem code (support type free_access & promo) |
| `resources/views/premium/history.blade.php` | Riwayat langganan user |
| `resources/views/admin/subscriptions/transactions.blade.php` | Riwayat transaksi admin |
| `database/factories/SubscriptionPlanFactory.php` | Factory untuk testing |
| `tests/Feature/PromoTest.php` | Test promo (create, disable, activate, validasi) |
| `tests/Feature/RedeemCodeTest.php` | Test redeem code (public + admin CRUD, type promo) |

### File Diubah

| File | Perubahan |
|------|-----------|
| `app/Http/Controllers/PremiumController.php` | Load plans dari DB, apply auto promo + redeem_promo dari session, validatePromo cek RedeemCode |
| `app/Http/Controllers/SpaceController.php` | Kirim data promo ke view |
| `app/Http/Controllers/RedeemCodeController.php` | Redeem: type=free_access → grant langsung, type=promo → session + redirect |
| `app/Models/User.php` | + `canUploadCover()`, + `subscriptionTransactions()` |
| `app/Models/SubscriptionPlan.php` | + HasFactory, hapus scopeAuto dari hasActiveAutoPromo/discountedPrice |
| `app/Models/RedeemCode.php` | + `type`, `plan()`, `applayPrice()`, `isFreeAccess()`, `isPromo()` |
| `app/Models/SubscriptionPromo.php` | Hapus `scopeAuto`, `scopeWithCode`, `Builder` import |
| `resources/views/premium/index.blade.php` | Popup campaign + popup redeem_promo + JS auto-fill param promo |
| `resources/views/space/index.blade.php` | Popup campaign tanpa kode |
| `routes/web.php` | Route `plus.history` + route `plus` bisa param promo |
| `routes/admin.php` | Gabung Promo+Redeem jadi 1 prefix `promo-redeem` + route activate |
| `routes/console.php` | + command `plus:migrate-history` |
| `database/factories/SubscriptionPromoFactory.php` | Hapus `code` dari definition |
| `tests/Feature/PlanAdminTest.php` | Update route names |
| `tests/Feature/PremiumSubscriptionTest.php` | Update route names |

---

## 10. Aturan Promo (Aktual)

### SubscriptionPromo (auto / campaign — tanpa kode)

| Aturan | Detail |
|--------|--------|
| **1 user 1×** | Pivot `subscription_promo_user` — dicek via `canUseBy($user)` |
| **Expired** | `isValid()` cek `is_active`, `starts_at`, `expires_at`, `max_uses` |
| **Per plan** | `plan_id` nullable — null = semua plan |
| **Diskon** | `type = percent` / `fixed`, `value` = nilai diskon |
| **Pop-up** | `show_popup` + `popup_title` + `popup_message` — muncul di `/plus` & `/your-space` |
| **2 tipe admin** | **Promo Campaign** (len­gkap) dan **Diskon Plan** (minimalis, wajib plan_id) |
| **Edit** | ✅ Full CRUD — Edit modal via JS, Activate/Disable |

### RedeemCode (kode — free_access / promo)

| Aturan | Detail |
|--------|--------|
| **Free Access** | `type=free_access` → grant langsung Plus/Plus+ sesuai tier + durasi |
| **Kode Promo** | `type=promo` → catat pivot + set session → redirect ke `/plus` dgn popup |
| **1 user 1×** | Pivot `redeem_code_user` — dicek sebelum redeem |
| **Guard downgrade** | Plus+ user gak bisa redeem kode Plus |
| **Pro-rata** | Upgrade Plus → Plus+: sisa hari dikonversi 2:1 |
| **Kumulatif** | Redeem saat sudah Plus → perpanjang expires_at |
| **Validas AJAX** | `POST /plus/promo/validate` — cek RedeemCode type=promo |
| **Auto-fill** | Param `?promo=KODE` di URL → isi otomatis + validasi di `/plus` |

---
