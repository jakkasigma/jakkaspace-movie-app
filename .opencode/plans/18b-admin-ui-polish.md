# Phase 8b — Admin UI Polish: Navbar & Menu Terpisah

> Created: 2026-07-05
> Status: Planning
> Prerequisite: Phase 8a (Admin System) ✅

---

## Masalah

Admin pakai layout terpisah (`admin.layout`) tapi:
1. Ga ada link ke admin dari user navbar — harus ketik `/admin`
2. Sidebar admin masih minimal (Dashboard + Themes doang)
3. Admin belum punya top navbar — cuma sidebar doang
4. Fitur admin ke depan (subscriptions, users) belum ada di menu

---

## Solusi

### 1. Admin Top Navbar Baru

Persisten di atas, tidak terkait dengan user navbar:

```
┌─────────────────────────────────────────────────────┐
│ [JAKKA ADMIN]          Dashboard  Themes  ...    [👤] │
├──────────┬──────────────────────────────────────────┤
│ Sidebar  │ Content                                  │
│          │                                          │
└──────────┴──────────────────────────────────────────┘
```

### 2. Sidebar → Expand Menu Items

| Section | Items |
|---------|-------|
| **Management** | Themes, Subscriptions (stub), Users (stub) |
| **System** | Back to App, Logout |

### 3. Link Admin di User Navbar

Dropdown user navbar → tambah item "Admin Panel" (hanya kalo `isAdmin()`):

```
User Navbar Dropdown:
├─ Your Space
├─ Diary
├─ History
├─ ...
├─ Plus
├─ (separator)
├─ Admin Panel     ← NEW (only if isAdmin)
├─ Pengaturan
├─ Keluar
```

### 4. Link Admin di Mobile Panel Juga

Di section Akun → tambah "Admin Panel" (hanya admin).

---

## File yang Akan Dimodifikasi

| # | File | Perubahan |
|---|------|-----------|
| 1 | `resources/views/admin/layout.blade.php` | Restructure: tambah top navbar, expand sidebar menu, pisahkan CSS ke file terpisah atau inline yang lebih rapi |
| 2 | `resources/views/components/movie/navbar.blade.php` | Tambah link "Admin Panel" di dropdown + mobile panel (wrap dengan `@admin`) |
| 3 | `resources/css/welcome.css` | Tambah style admin link di navbar |

---

## Detail Implementasi

### 1. Admin Layout — Struktur Baru

```blade
<body>
    {{-- Top Navbar --}}
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <a href="{{ route('admin.dashboard') }}" class="admin-topbar-logo">JAKKA ADMIN</a>
            <nav class="admin-topbar-nav">
                <a href="{{ route('admin.dashboard') }}" class="admin-topbar-link @active(...)">Dashboard</a>
                <a href="{{ route('admin.themes.index') }}" class="admin-topbar-link @active(...)">Themes</a>
                {{-- Future: Subscriptions, Users --}}
            </nav>
        </div>
        <div class="admin-topbar-right">
            <span class="admin-topbar-user">{{ auth()->user()->name }}</span>
            <a href="{{ route('logout') }}" class="admin-topbar-logout"
               onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                Keluar
            </a>
            <form id="admin-logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
        </div>
    </header>

    <div class="admin-body">
        <aside class="admin-sidebar">... existing sidebar ...</aside>
        <main class="admin-main">... @yield('content') ...</main>
    </div>
</body>
```

Layout menjadi:
- `admin-topbar` — fixed top, z-index tinggi
- `.admin-body` — display flex, padding-top untuk topbar height
- Sidebar — height: calc(100vh - topbar height)

### 2. Sidebar Items

```
Management ─────────────────
  🎨 Themes
  💳 Subscriptions (Coming Soon)  ← stub, disabled
  👥 Users (Coming Soon)          ← stub, disabled

System ─────────────────────
  ← Back to App
  → Logout
```

### 3. Admin Link di User Navbar

Di dropdown desktop (antara "Plus" dan "Pengaturan"):

```blade
@if (auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-item">
        <svg>...</svg>
        Admin Panel
    </a>
    <div class="nav-dropdown-divider"></div>
@endif
```

Di mobile panel (antara Plus dan Akun):

```blade
@if (auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" class="nav-mobile-link" data-menu-link>
        <svg>...</svg>
        Admin Panel
    </a>
@endif
```

---

## Hasil Akhir

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| Admin access | Ketik `/admin` manual | Link di dropdown user |
| Top navbar | Tidak ada | Ada, dengan brand + nav |
| Sidebar | 2 links | Expandable, ada section |
| Admin vs User | Baur (navbar user masih keliatan) | Layout terpisah total |
| Mobile admin | Tidak ada akses | Link di mobile panel |

---

## Catatan

1. **Top navbar** — styling inline di `admin/layout.blade.php` (ikuti pola existing, ga perlu file CSS terpisah)
2. **Admin guard** — middleware `admin` tetap return 404, link di user navbar cuma muncul kalo `isAdmin()` jadi non-admin tetap ga tau
3. **Stub menu** — Subscriptions & Users disabled dengan tooltip "Coming Soon" — biar admin tau roadmap
