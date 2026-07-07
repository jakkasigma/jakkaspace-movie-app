# Phase 7 — Premium Subscription: Rencana & Workflow

> Created: 2026-07-05
> Status: ✅ Done (executed 2026-07-05 — updated 2026-07-07)
> Prerequisite: Phase 6 ✅

> **Catatan:** Nama tier berubah dari "Premium" menjadi "Plus" selama eksekusi. Fungsi `isPremium()` di implementasi menjadi `isPlus()`. Admin system dibahas di `17-admin-system.md`.
>
> **Cover list custom (Plus+):** ✅ Implemented via file upload di create/edit list, gated dengan `User::canUploadCover()`, path `storage/covers/`.
>
> **Invoice history:** ✅ Halaman `/plus/history` (user) + `/admin/subscriptions/transactions` (admin) + tabel `subscription_transactions`.
>
> **Midtrans payment gateway:** ❌ Skip (belum punya API key). Simulasi via `/plus/simulate`.
>
> **Expiry notification cron:** ⚠️ Command `CheckExpiredSubscriptions` + scheduler `plus:check-expired` →daily ada, tapi cron OS belum diaktifkan.

---

## Visi

Menghadirkan model subscription premium untuk Jakkaspace dengan sistem **theme packs** (konsep seperti Discord Nitro) — pengguna premium mendapat tema personal yang mempengaruhi tampilan avatar, username, review card, komentar, dan chat bubble di seluruh platform.

Model limitasi sederhana di fitur tertentu (movie lists, pinned movies) untuk memberikan insentif upgrade tanpa merusak pengalaman free user.

---

## Perbandingan Fitur

| Fitur | Free | Premium |
|---|---|---|
| **Movie lists** | 1 list | Unlimited |
| **List per list** | 50 film | Unlimited |
| **Pinned movies** | 4 | 12 |
| **Export data CSV** (diary, review, history) | ❌ | ✅ |
| **Analytics lanjutan** (rating distribution, streak, estimasi jam, sutradara favorit) | ❌ | ✅ |
| **Theme packs** (avatar border, warna aksen, badge) | Default | Pilih bebas dari katalog tema |
| **Badge premium** di profil, komentar, chat | ❌ | ✅ |
| **Aksen warna** di review card, username, chat bubble | ❌ | ✅ |
| **Early access** fitur baru | ❌ | ✅ |

---

## Pricing (Usulan)

| Periode | Harga | Efektif per bulan |
|---|---|---|
| Bulanan | Rp 15.000 | Rp 15.000 |
| Tahunan | Rp 150.000 | Rp 12.500 |

---

## Theme System

### Konsep

Theme packs mirip Discord Nitro — bukan kustomisasi bebas (pilih warna sendiri), tapi **tema siap pakai** yang didesain oleh developer. Setiap tema adalah kumpulan CSS variables yang diterapkan ke elemen-elemen tertentu di seluruh platform.

### Table `themes`

```sql
themes
  id                  unsigned auto_increment PK
  slug                varchar(100) unique       -- 'marvel', 'ghibli', 'cyberpunk'
  name                varchar(100)               -- 'Marvel Superhero'
  avatar_border_css   text                       -- 'linear-gradient(135deg, #e23636, #f78f3f)'
  accent_color        varchar(7)                 -- '#e23636'
  badge_icon          varchar(10)                -- '🦸'
  is_active           boolean default true
  created_at          timestamp
  updated_at          timestamp
```

### Daftar Theme Launch (5-6 tema)

| Tema | Avatar Border | Aksen | Badge |
|---|---|---|---|
| **Marvel Superhero** | 🔴🟠 gradien `#e23636` → `#f78f3f` | `#e23636` | 🦸 |
| **Studio Ghibli** | 🌿 gradien `#2d8a4e` → `#f5e6c8` | `#2d8a4e` | 🐱 |
| **Cyberpunk** | 🟣 gradien `#ff00ff` → `#00ffff` neon | `#ff00ff` | 🤖 |
| **Star Wars** | ⚫ gradien `#000000` → `#2a6fdb` | `#2a6fdb` | 🌟 |
| **Horror** | 🖤 gradien `#1a1a1a` → `#8b0000` | `#cc0000` | 👻 |
| **Retro 80s** | 🟡 gradien `#ff6ec7` → `#00bfff` | `#ff6ec7` | 🌴 |

### CSS Variables per Tema

```css
/* Diterapkan ke container element */
--theme-avatar-border: linear-gradient(...);
--theme-accent: #e23636;
--theme-badge-icon: "🦸";
--theme-bubble-bg: rgba(226, 54, 54, 0.08);
--theme-bubble-border: rgba(226, 54, 54, 0.3);
--theme-card-border: 2px solid #e23636;
```

### Di Mana Efek Tema Muncul

| Elemen | Efek |
|---|---|
| **Avatar** (profil, komentar, chat, timeline) | Border gradient `--theme-avatar-border` |
| **Username** (timeline, komentar, review) | Warna `--theme-accent` |
| **Review card** | Border kiri `--theme-card-border` atau background tipis |
| **Chat bubble** (milik sendiri) | Background `--theme-bubble-bg`, border `--theme-bubble-border` |
| **Badge premium** | Ikon `--theme-badge-icon` di samping nama |

### Developer Nambah Tema Baru

Cukup insert row baru — otomatis muncul di dropdown user tanpa deploy ulang view:

```php
Theme::create([
    'name' => 'Godzilla',
    'slug' => 'godzilla',
    'avatar_border_css' => 'linear-gradient(135deg, #2d2d2d, #ff4444)',
    'accent_color' => '#ff4444',
    'badge_icon' => '🦎',
]);
```

---

## Pricing Page Flow

```
User klik "Coba Premium" / "Upgrade"
    ↓
Halaman /premium
    ↓
Tampilkan:
  - Perbandingan fitur (table)
  - Katalog tema (preview avatar border + warna)
  - Pilihan paket: Bulanan (Rp15k) / Tahunan (Rp150k)
  - Tombol "Langganan Sekarang"
    ↓
Pilih paket → Pilih metode pembayaran (Midtrans Snap popup)
    ↓
Bayar
    ↓
Midtrans callback → Webhook → Update users.subscription_tier + expires_at
    ↓
Redirect ke halaman settings premium
    ↓
Pilih tema pertama → Tema langsung aktif
```

---

## Implementasi

### Tahap 1 — Limit + Flag Premium (2-3 hari)

**Tujuan:** Sistem limitasi berjalan, flag premium bisa di-toggle manual via tinker untuk testing, tanpa payment dulu.

#### 1. Migration: `add_subscription_to_users`

```php
Schema::table('users', function (Blueprint $table): void {
    $table->string('subscription_tier', 20)->default('free')->after('has_password');
    $table->timestamp('subscribed_at')->nullable()->after('subscription_tier');
    $table->timestamp('expires_at')->nullable()->after('subscribed_at');
    $table->foreignId('theme_id')->nullable()->constrained('themes')->nullOnDelete()->after('expires_at');
});
```

#### 2. Migration: `create_themes_table`

```php
Schema::create('themes', function (Blueprint $table): void {
    $table->id();
    $table->string('slug', 100)->unique();
    $table->string('name', 100);
    $table->text('avatar_border_css');
    $table->string('accent_color', 7);
    $table->string('badge_icon', 10)->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### 3. Seeder: `ThemeSeeder`

Isi 6 theme launch (Marvel, Ghibli, Cyberpunk, Star Wars, Horror, Retro 80s).

#### 4. Model: `Theme`

```php
class Theme extends Model
{
    protected $fillable = ['slug', 'name', 'avatar_border_css', 'accent_color', 'badge_icon', 'is_active'];
}
```

#### 5. Method `User::isPremium()`

```php
public function isPremium(): bool
{
    return $this->subscription_tier === 'premium'
        && $this->expires_at !== null
        && now()->lessThan($this->expires_at);
}
```

#### 6. Limit Enforcement

**Movie list limit (store):**
```php
if (! $user->isPremium() && $user->movieLists()->count() >= 1) {
    return redirect()->back()->with('error', 'Kamu hanya bisa membuat 1 list. Upgrade ke Premium untuk unlimited.');
}
```

**Movie list items limit (store):**
```php
if (! $user->isPremium() && $list->listMovies()->count() >= 50) {
    return redirect()->back()->with('error', 'List gratis maksimal 50 film. Upgrade ke Premium untuk unlimited.');
}
```

**Pinned movies limit (store):**
```php
if (! $user->isPremium() && $pinnedCount >= 4) {
    return redirect()->back()->with('error', 'Kamu hanya bisa menyematkan 4 film. Upgrade ke Premium untuk 12 film.');
}
```

#### 7. Settings Halaman Premium

Halaman `/profile/premium` dengan:
- Informasi status premium (aktif sampai kapan / belum premium)
- Tombol "Upgrade" (nanti di tahap 2 mengarah ke Midtrans, di tahap 1 bisa dummy)
- Pilih tema (dropdown dari `Theme::where('is_active', true)->get()`)
- Preview avatar border sesuai tema yang dipilih

#### 8. Tampilkan Efek Tema di Komponen

**Avatar component** — tambah border gradient:
```blade
@if ($user->isPremium() && $user->theme)
    <div class="avatar-premium" style="--avatar-border: {{ $user->theme->avatar_border_css }}">
        <img src="..." class="avatar-img" />
    </div>
@else
    <img src="..." class="avatar-img" />
@endif
```

**CSS:**
```css
.avatar-premium {
    padding: 3px;
    background: var(--avatar-border);
    border-radius: 50%;
    display: inline-flex;
}
.avatar-premium .avatar-img {
    border-radius: 50%;
    border: 2px solid #000;
}
```

**Username di timeline/komentar:**
```blade
<span class="username {{ $user->isPremium() ? 'username-premium' : '' }}"
      @if ($user->theme) style="color: {{ $user->theme->accent_color }}" @endif>
    {{ $user->name }}
    @if ($user->isPremium() && $user->theme?->badge_icon)
        <span class="premium-badge">{{ $user->theme->badge_icon }}</span>
    @endif
</span>
```

**Chat bubble milik sendiri:**
```blade
@if ($isMine && $user->isPremium() && $user->theme)
    <div class="inbox-msg-text inbox-msg-premium"
         style="background: {{ $user->theme->accent_color }}; opacity: 0.9">
@else
    <div class="inbox-msg-text">
@endif
```

### Tahap 2 — Payment Midtrans (1 minggu)

#### 1. Integrasi Midtrans Snap

Pakai HTTP Client Laravel langsung ke Midtrans API:
- `POST /snap/v1/transactions` — dapat Snap token
- Midtrans Snap popup di frontend
- Handle response callback

#### 2. Halaman Pricing

Route `GET /premium` → view dengan:
- Table perbandingan fitur
- Kartu tema (preview avatar + efek)
- Pilihan paket (bulanan/tahunan)
- Tombol "Langganan"

#### 3. Webhook Midtrans

Route `POST /webhook/midtrans` (tanpa CSRF) → update subscription:
```php
public function handle(Request $request): Response
{
    $payload = $request->all();
    // Verifikasi signature
    // Cari user by order_id
    // Update subscription_tier = 'premium', expires_at = ...
    return response('OK', 200);
}
```

#### 4. Handle Expired Subscription

Scheduled command `premium:check-expired`:
```bash
php artisan premium:check-expired
```
```php
User::where('subscription_tier', 'premium')
    ->where('expires_at', '<', now())
    ->update(['subscription_tier' => 'free', 'theme_id' => null]);
```

### Tahap 3 — Premium Features (1-2 minggu)

#### 1. Export Data CSV

Controller `ExportController`:

| Route | Output |
|---|---|
| `GET /export/diary` | CSV diary entries |
| `GET /export/reviews` | CSV reviews |
| `GET /export/history` | CSV watch history |
| `GET /export/all` | ZIP semua CSV |

Format CSV:
```
Judul Film,Tanggal Tayang,Rating/10,Review,Diary Entry,Mood,Sutradara
Inception,2026-01-15,9,Masterpiece,Great movie,🤯,Christopher Nolan
```

#### 2. Analytics Lanjutan

Tambah di `AnalyticsService` metode baru (hanya untuk premium):

| Metrik | Implementasi |
|---|---|
| **Rating distribution** | `GROUP BY rating` di reviews → chart bar |
| **Streak harian** | Hitung hari beruntun diary entry |
| **Estimasi jam nonton** | Join watch_history ke duration film (dari TMDB) → SUM |
| **Sutradara favorit** | Parse credits dari TMDB → count per director |
| **Rating per tahun** | `GROUP BY YEAR(created_at)` → rata-rata rating |

Cek premium di controller:
```php
public function analytics(Request $request): View
{
    $analytics = $this->analyticsService->getAnalytics($request->user());
    $premiumAnalytics = $request->user()->isPremium()
        ? $this->analyticsService->getPremiumAnalytics($request->user())
        : null;

    return view('space.analytics', [
        'analytics' => $analytics,
        'premiumAnalytics' => $premiumAnalytics,
    ]);
}
```

---

## File yang Akan Dibuat

### Models
- `app/Models/Theme.php`

### Controllers
- `app/Http/Controllers/PremiumController.php` — halaman pricing
- `app/Http/Controllers/WebhookController.php` — Midtrans webhook
- `app/Http/Controllers/ExportController.php` — export data

### Migrations
- `xxxx_xx_xx_create_themes_table.php`
- `xxxx_xx_xx_add_subscription_to_users.php`

### Seeders
- `database/seeders/ThemeSeeder.php`

### Views
- `resources/views/premium/index.blade.php` — halaman pricing
- `resources/views/profile/partials/premium-settings.blade.php` — settings premium

### Console
- `app/Console/Commands/CheckExpiredSubscriptions.php`

### Routes
```php
Route::middleware('auth')->group(function () {
    Route::get('/premium', [PremiumController::class, 'index'])->name('premium');
    Route::post('/premium/subscribe', [PremiumController::class, 'subscribe'])->name('premium.subscribe');
    Route::put('/premium/theme', [PremiumController::class, 'updateTheme'])->name('premium.theme');
    Route::get('/export/{type}', [ExportController::class, 'export'])->name('export');
});

Route::post('/webhook/midtrans', [WebhookController::class, 'midtrans'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
```

---

## Urutan Pengerjaan

### Tahap 1a — Database & Model
```
1. Migration create_themes_table
2. Migration add_subscription_to_users
3. ThemeSeeder (6 tema launch)
4. Theme model
5. User::isPremium() method
```

### Tahap 1b — Limit Enforcement
```
6. MovieListController@store — cek limit 1 list
7. ListMovieController@store — cek limit 50 film per list
8. PinnedMovieController@store — cek limit 4 pinned
```

### Tahap 1c — Settings Premium & Tema
```
9. PremiumController@index (tanpa payment)
10. Route premium (GET)
11. View premium/index.blade.php — status + pilih tema
12. Route PUT premium/theme — simpan theme_id
13. Tampilkan avatar border gradient di avatar component
14. Tampilkan username accent di timeline/komentar
15. Tampilkan premium badge
16. Tampilkan aksen chat bubble
17. CSS untuk semua efek premium
```

### Tahap 1d — Premium-Only Features (hidden)
```
18. ExportController — cek premium, kalau free tampilkan "Upgrade"
19. AnalyticsService@getPremiumAnalytics — hanya dipanggil kalau premium
```

### Tahap 2 — Payment
```
20. Integrasi Midtrans Snap
21. PremiumController@subscribe
22. WebhookController@midtrans
23. Handle expired subscription (command)
```

### Tahap 3 — Full Implementation
```
24. Export data CSV implementation
25. Premium analytics implementation
26. Tema tambahan tiap bulan
```

---

## Catatan

1. **Nama tier** berubah dari "Premium" menjadi "Plus" selama eksekusi
2. **Tahap 1** sudah cukup untuk MVP tanpa payment — limit + tema berjalan dulu
3. **Theme packs** bisa diperluas — developer tinggal insert row baru di `themes` table
4. **CSS variables** approach bikin efek tema ringan tanpa perlu render ulang component berat
5. **Harga** Rp 15rb/bulan masih usulan — bisa disesuaikan
6. **Midtrans** support GoPay, QRIS, transfer bank, kartu kredit, Indomaret — paling cocok untuk pasar Indonesia

---

## Realisasi

### ✅ Done — Database & Model
- `database/migrations/xxxx_create_themes_table`
- `database/migrations/xxxx_add_subscription_to_users`
- `database/seeders/ThemeSeeder` (6 tema: Marvel, Ghibli, Cyberpunk, Star Wars, Horror, Retro 80s)
- `app/Models/Theme`
- `app/Models/User` — method `isPlus()`, `theme()` relasi, `isAdmin()`

### ✅ Done — Limit Enforcement
- Movie list: max 1 (free) / unlimited (Plus)
- List items: max 50 (free) / unlimited (Plus)
- Pinned movies: max 4 (free) / 12 (Plus)

### ✅ Done — Simulated Payment
- `/plus` — halaman pricing + pilih paket (bulanan/tahunan)
- Modal pilih metode (GoPay, QRIS, Transfer Bank)
- `/plus/simulate` — halaman sukses

### ✅ Done — Theme Effects
- Avatar border gradient (`x-user-avatar` component + `--avatar-border`)
- Username accent + badge (`x-user-name` component + `--plus-accent`)
- Review card border-left (timeline + detail page)
- Chat bubble gradient border (inbox)
- Comments & replies premium border
- Feed items premium border
- Inbox list premium border
- Rating picker dark theme
- Plus indicator badge di profile & your space

### ✅ Done — Premium-Only Features
- Export CSV (diary, reviews, history, ZIP all)
- Premium analytics (rating distribution, streak, estimasi jam, sutradara favorit, rating per tahun)
- Upgrade prompt untuk free user

### ✅ Done — Admin System (file: `18-admin-system.md`)
- `is_admin` flag + `CheckAdmin` middleware
- Dashboard with stats
- Theme CRUD (create, edit, delete, preview live)
- Subscription management (list, grant, extend, cancel)
- User management (list, search, filter, ban/unban)
- `CheckBanned` middleware (auto logout banned user)

### ❌ Pending
- Midtrans real payment (butuh akun & API key)
- Notif kedaluwarsa H-7 (command sudah ada, tinggal schedule aktif)
- Invoice history page

---

