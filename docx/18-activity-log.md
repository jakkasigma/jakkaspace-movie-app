# Phase 7.5 — Pusat Aktivitas (Activity Log)

> Created: 2026-07-06
> Status: In Progress
> Prerequisite: Phase 7 ✅

---

## Visi

Transformasi halaman History (/your-space/history) dari sekadar riwayat nonton menjadi **Pusat Aktivitas** — log kronologis semua aksi user: menonton film, menulis diary/review, mengubah profil, menambah watchlist/favorit.

---

## Status Progress

| Komponen | Status | Keterangan |
|---|---|---|
| **Migration & Model** | ⬜ Belum | `activity_logs` table + `ActivityLog` model |
| **Logging di UserActivityService** | ⬜ Belum | watch_status, watchlist, favorite |
| **Logging di DiaryService** | ⬜ Belum | diary create/update |
| **Logging di ReviewService** | ⬜ Belum | review create/update |
| **Logging di ProfileController** | ⬜ Belum | profile_update (name, username, bio, avatar) |
| **SpaceController** | ⬜ Belum | query ActivityLog instead of WatchHistory |
| **SpaceService** | ⬜ Belum | ganti getWatchHistoryEntries() → getActivityFeed() |
| **View history.blade.php** | ⬜ Belum | render semua tipe aktivitas |
| **Tests** | ⬜ Belum | |

---

## Database

### Migration: `activity_logs`

```php
Schema::create('activity_logs', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('type', 50);
    $table->text('description');
    $table->json('metadata')->nullable();
    $table->timestamp('created_at')->index();

    $table->index('type');
});
```

### ActivityLog Model

```php
class ActivityLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'type', 'description', 'metadata', 'created_at'];
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
    public function user(): BelongsTo { ... }
}
```

---

## Logging Points

### UserActivityService

Setelah `updateOrCreate` / `firstOrCreate`:

| Method | type | description | metadata |
|--------|------|-------------|----------|
| `markAsWatched()` | `watch_status` | "Menontan {title}" | `{tmdb_id, movie_title, status: "watched"}` |
| `markAsWatching()` | `watch_status` | "Menandai sedang menonton {title}" | `{tmdb_id, movie_title, status: "watching"}` |
| `markAsDropped()` | `watch_status` | "Menghentikan {title}" | `{tmdb_id, movie_title, status: "dropped"}` |
| `addToWatchlist()` | `watchlist` | "Menambahkan {title} ke Watchlist" | `{tmdb_id, movie_title}` |
| `addToFavorites()` | `favorite` | "Menambahkan {title} ke Favorit" | `{tmdb_id, movie_title}` |

> `removeFromWatchlist()` dan `removeFromFavorites()` tidak dicatat (user minta create/update only).

### DiaryService

| Method | type | description | metadata |
|--------|------|-------------|----------|
| `createEntry()` | `diary` | "Menambahkan diary untuk {title}" | `{tmdb_id, movie_title, notes, mood, is_rewatch}` |
| `updateEntry()` | `diary` | "Memperbarui diary untuk {title}" | `{tmdb_id, movie_title, notes, mood, is_rewatch}` |

### ReviewService

| Method | type | description | metadata |
|--------|------|-------------|----------|
| `upsertReview()` (create) | `review` | "Menulis review untuk {title}" | `{tmdb_id, movie_title, rating, body}` |
| `upsertReview()` (update) | `review` | "Memperbarui review untuk {title}" | `{tmdb_id, movie_title, rating, body}` |

### ProfileController update()

Deteksi field berubah dengan `$user->isDirty()` / `$user->getChanges()` setelah save:

| type | description | metadata |
|------|-------------|----------|
| `profile_update` | "Mengubah nama menjadi {new}" | `{field: "name", old_value, new_value}` |
| `profile_update` | "Mengubah username menjadi {new}" | `{field: "username", old_value, new_value}` |
| `profile_update` | "Mengubah foto profil" | `{field: "avatar"}` |
| `profile_update` | "Mengubah bio" | `{field: "bio", old_value, new_value}` |

Jika banyak field berubah dalam 1 request, buat 1 log per field.

---

## View: history.blade.php

### Layout

```
┌──────────────────────────────────────┐
│ ← Your Space                         │
│ HISTORY                              │
│ Pusat Aktivitas — semua aktivitas mu  │
├──────────────────────────────────────┤
│ (navbar space)                       │
│ (tab bar)                            │
├──────────────────────────────────────┤
│                                      │
│ [Month Year]                         │
│                                      │
│  🎬 Menontan Inception               │
│     ├─ Poster ┤  2 jam lalu          │
│                                      │
│  📝 Menambahkan diary untuk Tenet    │
│     ├─ Poster ┤  "Mind-blowing..."   │
│                  Kemarin             │
│                                      │
│  👤 Mengubah foto profil             │
│     ├─ Avatar ┤  3 hari lalu         │
│                                      │
│  ⭐ Menulis review untuk Dunkirk     │
│     ├─ Poster ┤  ★★★★☆              │
│                  "Masterpiece..."    │
│                                      │
│  📋 Menambahkan Interstellar ke      │
│     Watchlist                        │
│     ├─ Poster ┤  Minggu lalu         │
│                                      │
│ ...pagination...                     │
└──────────────────────────────────────┘
```

### Ikon per Type

| type | Ikon | Poster? |
|------|------|---------|
| `watch_status` | 🎬 (atau film icon) | Ya |
| `diary` | 📝 | Ya |
| `review` | ⭐ | Ya |
| `watchlist` | 📋 | Ya |
| `favorite` | ❤️ | Ya |
| `profile_update` | 👤 | Avatar user (jika ada) |

### Entry Card Design (per type)

Setiap entri: baris horizontal (icon + poster + body + timestamp).

- **watch_status**: ikon + poster + "Menontan {title}" + status badge (Watched/Watching/Dropped)
- **diary**: ikon + poster + "Menambahkan diary untuk {title}" + cuplikan notes dari metadata (max 100 char)
- **review**: ikon + poster + "Menulis review untuk {title}" + rating stars + cuplikan body
- **watchlist**: ikon + poster + "Menambahkan {title} ke Watchlist"
- **favorite**: ikon + poster + "Menambahkan {title} ke Favorit"
- **profile_update**: ikon + avatar user + deskripsi perubahan

Filter per bulan (sama seperti sekarang).

---

## Flow Baru

### Sebelum (Old History)

```
GET /your-space/history
    ↓
SpaceController@history
    ↓
SpaceService@getWatchHistoryEntries()
    — query WatchHistory
    — attach movie info from TMDB
    — attach user rating from Review
    ↓
View: history.blade.php
    — filter: Semua / Watched / Watching / Dropped
    — grouped by month
    — poster + title + status + rating
```

### Sesudah (Activity Feed)

```
GET /your-space/history
    ↓
SpaceController@history
    ↓
SpaceService@getActivityFeed()
    — query ActivityLog where user_id
    — latest() → paginate(20)
    — (tidak perlu fetch TMDB, title ada di metadata)
    ↓
View: history.blade.php
    — no filter (semua aktivitas)
    — grouped by month
    — icon + poster + description per type
```

---

## Controller & Service

### SpaceController::history()

```php
public function history(Request $request): View
{
    $user = $request->user();
    $entries = ActivityLog::where('user_id', $user->id)
        ->latest('created_at')
        ->paginate(20);

    return view('space.history', [
        'user' => $user,
        'entries' => $entries,
    ]);
}
```

### SpaceService — hapus method terkait

Hapus/hide: `getWatchHistoryEntries()`, `attachMovieInfoForWatchHistory()`, `getHistorySummaryStats()`.

Jika masih dipakai di tempat lain, simpan, jika tidak, hapus.

---

## Yang Perlu Dikerjakan

1. ✅ Buat migration `activity_logs`
2. ✅ Buat model `ActivityLog`
3. ✅ Logging di `UserActivityService` (watch_status, watchlist, favorite)
4. ✅ Logging di `DiaryService` (diary create/update)
5. ✅ Logging di `ReviewService` (review create/update)
6. ✅ Logging di `ProfileController` (profile_update)
7. ✅ Update `SpaceController::history()` → query ActivityLog
8. ✅ Update `SpaceService` — hapus method WatchHistory lama
9. ✅ Update view `history.blade.php` — render all activity types
10. ✅ Hapus filter status
11. ✅ Run tests + pint

---

## Catatan

- Judul film disimpan di `metadata.movie_title` — tidak perlu fetch TMDB saat render history
- `created_at` di set manual saat pembuatan log (pakai `now()`) agar bisa berbeda dengan timestamp aksi asli jika perlu
- Migration tidak menggunakan `timestamps()` karena hanya perlu `created_at`
