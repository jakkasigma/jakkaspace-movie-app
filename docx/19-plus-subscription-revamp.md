# Phase 8 — Plus & Plus+ Subscription Revamp

> Created: 2026-07-06
> Status: ✅ **Completed** (2026-07-06 — updated 2026-07-07)
> Prerequisite: List → Group ✅
>
> **Cover list custom (Plus+):** ✅ Implemented — upload gambar di form create/edit list, display di detail list + kartu list + profile tab. File path `storage/covers/`.

---

## Visi

Memperkenalkan **2-tier subscription** (Plus & Plus+) dengan perbedaan fitur yang jelas, ditambah sistem redeem code untuk admin dan manajemen subscription via admin panel.

---

## Tier Subscription

| Fitur | Free | Plus (Rp15K/bln) | Plus+ (Rp30K/bln) |
|-------|------|------------------|-------------------|
| **Movie lists** (publik + privat) | 1 list | 7 (4 publik + 3 privat) | 15 (8 publik + 7 privat) |
| **Film per list** | 50 | 100 | Unlimited |
| **Pinned movies** | 6 | 6 | 12 |
| **Theme & avatar border** | Default | Garis luar warna | Beda dengan Plus |
| **Badge** | — | 👑 Plus | 💎 Plus+ |
| **Aksen warna** | ❌ | ✅ (dasar) | ✅ (lebih ekspresif) |
| **Analytics ringkasan** | ✅ | ✅ | ✅ |
| **Analytics lanjutan** | ❌ | Streak + distribusi rating | Streak + distribusi + per genre/tahun/director |
| **Export CSV** | ❌ | ✅ per halaman | ✅ batch semua |
| **Cover list custom** | ❌ | ❌ | ✅ |
| **Early access fitur** | ❌ | ❌ | ✅ |
| **Prioritas support** | ❌ | ❌ | ✅ |
| **Riwayat analytics** | 1 tahun | 3 tahun | Selamanya |
| **Review character limit** | 5.000 | 10.000 | 25.000 |

---

## ✅ Database (selesai)

### Kolom di `users` (migration: `2026_07_05_135628_add_subscription_to_users_table`)
| Kolom | Tipe |
|-------|------|
| `subscription_tier` | string(20) nullable — `null`/`'free'`, `'plus'`, `'plus_plus'` |
| `subscribed_at` | timestamp nullable |
| `expires_at` | timestamp nullable |

### `redeem_codes` (migration: `2026_07_06_143748_create_redeem_codes_table`)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigIncrements | |
| `code` | string, unique | 4-16 char, alphanumeric + `-` `_` |
| `tier` | string(20) | `'plus'` / `'plus_plus'` |
| `duration_days` | unsignedSmallInt | 30 / 365 |
| `max_uses` | unsignedSmallInt | 0 = unlimited |
| `used_count` | unsignedSmallInt | default 0 |
| `is_active` | boolean | default true |
| `created_by` | FK → users | Admin yang bikin |
| `expires_at` | timestamp, nullable | Masa berlaku kode |
| `timestamps` | | |

### `redeem_code_user` pivot
| Kolom | Tipe |
|-------|------|
| `redeem_code_id` | FK → redeem_codes (cascade) |
| `user_id` | FK → users (cascade) |
| `redeemed_at` | timestamp |
| `timestamps` | — dihapus dari `->withTimestamps()` karena gak ada kolom |

Unique: `[redeem_code_id, user_id]`

---

## ✅ Helper Methods di `User` Model

```php
public function isPlus(): bool
public function isPlusPlus(): bool
public function maxLists(): int        // Free=0, Plus=7, Plus+=15
public function maxPublicLists(): int  // Free=0, Plus=4, Plus+=8
public function maxPrivateLists(): int // Free=0, Plus=3, Plus+=7
public function maxPinned(): int       // Free=6, Plus=6, Plus+=12
public function maxMoviesPerList(): int // Free=0, Plus=100, Plus+=-1 (unlimited)
```

---

## ✅ Redeem Code System

### Model `RedeemCode`
- `creator(): BelongsTo` — admin pembuat
- `redeemers(): BelongsToMany` — user yg redeem (tanpa `->withTimestamps()`)
- `isValid(): bool` — cek is_active, expired, max_uses

### Alur Redeem (final)

```
User input kode di /plus
    ↓
Cari RedeemCode::where('code', $code)->first()
    ↓
Validasi:
  - Kode ditemukan?
  - is_valid? (active, belum expired, masih ada kuota)
  - User belum pernah redeem kode ini?
  - ❌ Plus+ user gak bisa redeem kode Plus (guard downgrade)
    ↓
Konversi pro-rata (kalo upgrade Plus → Plus+):
  - Sisa Plus 50hr ÷ 2 = 25hr nilai Plus+
  - Ditambah durasi kode 30hr = total 55hr Plus+
    ↓
Update user:
  - subscription_tier = tier kode
  - expires_at = cumulative / pro-rata
    ↓
Increment used_count + simpan pivot redeem_code_user
    ↓
Redirect ke /plus → popup sukses detail konversi
```

---

## ✅ Admin Panel

### Admin/RedeemCodeController
| Method | Fungsi |
|--------|--------|
| `index()` | Daftar semua kode + stat (total, aktif, redeem) |
| `store()` | Validasi input → create kode |
| `show()` | Detail kode + daftar pemakai |
| `destroy()` | Nonaktifkan kode (set is_active = false) |

### Admin/SubscriptionController (diupdate)
- Query mencakup `plus` + `plus_plus`
- Grant: pilih tier (Plus / Plus+) + periode
- Filter/search by tier
- Cancel/extend support semua tier

### Routes
- Admin: `GET/POST /admin/redeem-codes`, `GET /admin/redeem-codes/{id}`, `DELETE /admin/redeem-codes/{id}`
- Public: `POST /plus/redeem` (name: `plus.redeem`)

---

## ✅ PremiumController

### Subscribe — 4 plan
| Plan | Tier | Harga |
|------|------|-------|
| `monthly_plus` | Plus | Rp15.000 |
| `yearly_plus` | Plus | Rp150.000 |
| `monthly_plusplus` | Plus+ | Rp30.000 |
| `yearly_plusplus` | Plus+ | Rp300.000 |

### Guard downgrade
- ✅ Plus+ gak bisa subscribe plan Plus (ditolak dengan pesan)
- ✅ Konversi pro-rata 2:1 kalo upgrade Plus → Plus+

### Success popup
Setelah subscribe/redeem sukses, redirect ke `/plus` dgn session data → muncul modal detail:
- Subscribe baru → "Selamat! Kamu sekarang Plus/Plus+!"
- Renew → "Subscription Diperpanjang"
- Upgrade → "Upgrade ke Plus+ Berhasil!" + rincian konversi

---

## ✅ Limit Controllers

| Controller | Sebelum | Sesudah |
|------------|---------|---------|
| `MovieListController::store()` | Hardcoded 1 list | `$user->maxLists()` |
| `ListMovieController::store()` | Hardcoded 50 film | `$user->maxMoviesPerList()` |
| `PinnedMovieController::store()` | Hardcoded 12/4 | `$user->maxPinned()` |

---

## ✅ View Changes

### `premium/index.blade.php`
- Pricing 2 tier (4 kartu: Plus 30/365hr, Plus+ 30/365hr)
- Comparison table 3 kolom (Free, Plus, Plus+)
- Form redeem code + error/success message
- Plus+ active state (badge, benefit list berbeda)
- Info modal 3 kolom
- Renew/Upgrade section dengan 4 kartu
- **Success popup modal** — detail subscribe / renew / upgrade + konversi
- FAQ update (Plus+ info, redeem code)

### `premium/simulate.blade.php`
- Label plan dinamis (Plus Bulanan, Plus Tahunan, Plus+ Bulanan, Plus+ Tahunan)

### `space/index.blade.php`
- Badge Plus/Plus+ di header
- Info modal dukung Plus+

### `admin/subscriptions/index.blade.php`
- Kolom tier (Plus / Plus+)
- Filter by tier
- Grant modal: pilih tier + periode

### `admin/redeem-codes/index.blade.php`
- Form + tabel menggunakan class admin (`.admin-form-input`, `.admin-card`, `.admin-table`)
- `color-scheme:dark` built-in

### `admin/redeem-codes/show.blade.php`
- Detail kode + daftar pemakai
- Styling konsisten dgn tema admin

---

## ✅ Tests

| File | Jumlah Test |
|------|-------------|
| `tests/Feature/RedeemCodeTest.php` | 22 test — public redeem (valid, invalid, expired, exhausted, cumulative, guard) + admin CRUD |
| `tests/Feature/PremiumSubscriptionTest.php` | 16 test — subscribe 4 plan, extend, upgrade, guard, limits, admin grant |

**Total: 38 tests, 165 assertions ✅**

---

## 🔧 Changes Selama Implementasi

1. **Pivot `redeem_code_user`**: `->withTimestamps()` dihapus dari model RedeemCode karena tabel MySQL asli gak punya kolom `created_at`/`updated_at`. Cukup pake `redeemed_at`.
2. **Guard downgrade**: Plus+ user gak bisa redeem kode Plus atau subscribe plan Plus.
3. **Pro-rata conversion**: Upgrade Plus → Plus+ via redeem/bayar: sisa hari Plus dikonversi 2:1 ke nilai Plus+, ditambah durasi baru.
4. **Success popup modal**: Setelah subscribe/redeem, user liat modal detail (bukan toast biasa).

---

## Catatan

- Redeem code bersifat **kumulatif**: redeem kode saat sudah Plus → extend expires_at
- Upgrade Plus → Plus+ via redeem/bayar: **pro-rata** (2 hari Plus = 1 hari Plus+)
- Admin bisa buat kode **untuk upgrade** (dari free ke Plus, dari Plus ke Plus+)
- Harga & plan masih simulasi (belum payment gateway beneran)
- ✅ Pricing view udah dinamis dari DB (lihat Phase 9 untuk plan CRUD admin)
- Border Plus+ vs Plus dipikir kemudian
