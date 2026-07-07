# Phase 8 — Admin System: Rencana & Workflow

> Created: 2026-07-05
> Status: Planning
> Prerequisite: Phase 7 (Premium Subscription) ✅

---

## Visi

Sistem admin minimal untuk Jakkaspace yang mencakup:
1. **Dashboard** — overview statistik
2. **Theme Management** — CRUD tema dari browser tanpa deploy
3. **(Nanti) Subscription Management** — lihat pendapatan, manual grant/cancel
4. **(Nanti) User Management** — lihat/ban user

Tidak build full admin panel (guard terpisah, dll.) — cukup **middleware sederhana** yang ngecek flag `is_admin` di users table.

---

## Arsitektur

### Pendekatan: Simple Gate, Bukan Full Guard

```
User → `is_admin` boolean → Middleware `admin` → Route `/admin/*`
```

Tanpa:
- Admin guard terpisah
- Admin login page
- Spatie/package role permission

### File yang Akan Dibuat/Dimodifikasi

| # | File | Status |
|---|------|--------|
| 1 | `database/migrations/xxxx_xx_xx_add_is_admin_to_users.php` | New |
| 2 | `app/Http/Middleware/CheckAdmin.php` | New |
| 3 | `bootstrap/app.php` — register middleware alias | Modified |
| 4 | `app/Http/Controllers/Admin/ThemeController.php` | New |
| 5 | `app/Http/Controllers/Admin/DashboardController.php` | New |
| 6 | `app/Http/Requests/Admin/ThemeRequest.php` | New |
| 7 | `resources/views/admin/layout.blade.php` | New |
| 8 | `resources/views/admin/dashboard.blade.php` | New |
| 9 | `resources/views/admin/themes/index.blade.php` | New |
| 10 | `resources/views/admin/themes/create.blade.php` | New |
| 11 | `resources/views/admin/themes/edit.blade.php` | New |
| 12 | `routes/admin.php` | New |
| 13 | `app/Providers/AppServiceProvider.php` — load routes | Modified |
| 14 | `resources/css/welcome.css` — admin styles | Modified |

---

## Detail Implementasi

### 1. Migration: `add_is_admin_to_users`

```php
Schema::table('users', function (Blueprint $table): void {
    $table->boolean('is_admin')->default(false)->after('theme_id');
});
```

### 2. Middleware: `CheckAdmin`

```php
class CheckAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_admin, 404);

        return $next($request);
    }
}
```

Return **404** (bukan 403) — biar tidak ketahuan endpoint admin.

### 3. Alias Middleware di `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\CheckAdmin::class,
    ]);
    $middleware->redirectUsersTo('/your-space');
});
```

### 4. Model: `User::isAdmin()`

```php
public function isAdmin(): bool
{
    return (bool) $this->is_admin;
}
```

### 5. Routes: `routes/admin.php`

```php
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('themes', ThemeController::class)->except('show');
});
```

### 6. Admin Layout

Layout minimal dengan sidebar navigasi:

```
+----------------------------------+
| [Logo]        Admin Panel        |
+----------+-----------------------+
| Sidebar  | Content               |
| - Dashboard |                     |
| - Themes  |                     |
+----------+-----------------------+
```

### 7. ThemeController

| Method | Route | Fungsi |
|--------|-------|--------|
| `index()` | `GET /admin/themes` | List semua tema + search |
| `create()` | `GET /admin/themes/create` | Form create tema |
| `store()` | `POST /admin/themes` | Simpan tema baru |
| `edit()` | `GET /admin/themes/{theme}/edit` | Form edit tema |
| `update()` | `PUT /admin/themes/{theme}` | Update tema |
| `destroy()` | `DELETE /admin/themes/{theme}` | Hapus tema |

### 8. ThemeRequest (Form Request)

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:100'],
        'slug' => ['required', 'string', 'max:100', Rule::unique('themes', 'slug')->ignore($this->route('theme'))],
        'avatar_border_css' => ['required', 'string'],
        'accent_color' => ['required', 'string', 'max:7'],
        'badge_icon' => ['nullable', 'string', 'max:10'],
        'is_active' => ['boolean'],
    ];
}
```

### 9. Dashboard

Statistik overview:
- Total users
- Total Plus subscribers (aktif)
- Revenue estimasi (kalkulasi dari subscription aktif × harga)
- Total themes

### 10. Halaman Themes

**Index:**
```
+----+------------------+----------+--------+------------------+
| #  | Name             | Slug     | Active | Actions          |
+----+------------------+----------+--------+------------------+
| 1  | Marvel Superhero | marvel.. | ✅     | ✏️ Edit / 🗑 Hapus |
+----+------------------+----------+--------+------------------+
[+ Tambah Tema]
```

**Create/Edit Form:**
- Name (text)
- Slug (text, auto-fill dari name)
- Avatar Border CSS (textarea — `linear-gradient(...)`)
- Accent Color (color picker atau text `#hex`)
- Badge Icon (text, emoji)
- Is Active (checkbox/toggle)
- **Preview** — avatar demo dengan border sesuai input

---

## Urutan Pengerjaan

### Fase 1 — Foundation (1-2 jam)
```
1. Migration add_is_admin_to_users
2. Middleware CheckAdmin
3. Alias middleware di bootstrap/app.php
4. routes/admin.php
5. Admin layout blade
6. DashboardController + view
```

### Fase 2 — Theme Management (2-3 jam)
```
7. ThemeController@index
8. ThemeController@create + store + ThemeRequest
9. ThemeController@edit + update
10. ThemeController@destroy
11. Preview avatar live (JavaScript minimal)
```

### Fase 3 — Subscription Overview (nanti, 2 jam)
```
12. Admin subscription page — lihat active subscribers
13. Manual grant/cancel Plus
```

### Fase 4 — User Management (nanti, 2 jam)
```
14. Admin users list + search
15. Ban/suspend user
```

---

## Catatan

1. **404 not 403** — middleware return 404 biar endpoint admin tidak terlihat oleh non-admin
2. **Set admin pertama** — via tinker: `User::first()->update(['is_admin' => true])`
3. **Tidak ada admin login terpisah** — pakai login web biasa + middleware check
4. **Preview avatar live** — JavaScript kecil di form create/edit yang render border gradient secara real-time
5. **Admin layout** — reuse CSS variables dari welcome.css, dark theme sesuai existing design
6. **Route naming** — `admin.dashboard`, `admin.themes.index`, dll.
