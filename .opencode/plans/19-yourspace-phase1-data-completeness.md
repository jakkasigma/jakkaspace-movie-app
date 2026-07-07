# Phase 1 — Your Space: Data Completeness

## Goal
Lengkapi data & tampilan di semua halaman Your Space agar informatif, profesional, dan "serius".

---

## Task List

### Task 1 — Migration: `movie_title` di `diary_entries`
- Migration baru `add_movie_title_to_diary_entries_table`
- Kolom `movie_title VARCHAR(255) NOT NULL AFTER tmdb_id`
- Update `DiaryEntry` model `$fillable`
- Saat create diary entry, simpan title dari TMDB

### Task 2 — `SpaceService`: Method Baru & Refactor
- `getRecentDiaryEntries(User $user, int $limit = 5)` — ambil diary terbaru + movie info + rating
- `getRecentReviews(User $user, int $limit = 5)` — ambil review terbaru + movie info
- `getDiaryEntries` — tambah param `?year`, `?sort` (newest/oldest/rating), inject rating
- `getWatchHistoryEntries` — return data **grouped by month** (Letterboxd-style) + rating per entry
- `getStats` — tambah `estimated_hours`
- Simpan `movie_title` ke DB setelah fetch dari TMDB (cache di DB)

### Task 3 — `SpaceController`: Update Semua Method
- `index()` — passing `recentDiary`, `recentReviews`, `stats` dengan `estimated_hours`
- `diary()` — passing `year`, `sort` dari request
- `history()` — grouping logic
- Baru: `editDiary()`, `updateDiary()` — edit diary entry

### Task 4 — Routes
- `GET /your-space/diary/{entry}/edit` → `SpaceController@editDiary` → `your-space.diary.edit`
- `PUT /your-space/diary/{entry}` → `SpaceController@updateDiary` → `your-space.diary.update`

### Task 5 — View: Dashboard (`space/index.blade.php`)
- Tambah `total_favorites` & `estimated_hours` di stats bar
- Section "Diary Terbaru" — 5 card entry dengan poster mini + rating
- Section "Review Terbaru" — 5 card review dengan rating stars + body preview

### Task 6 — View: Diary (`space/diary.blade.php`)
- **Rewrite layout**: tampilkan poster film di setiap entry
- Tampilkan rating bintang dari Review
- Filter tahun (dropdown)
- Sort dropdown (Terbaru / Terlama / Rating Tertinggi)
- Edit button → link ke edit page
- Stats summary header: total entries, streak, rata-rata per bulan

### Task 7 — View: Diary Edit (`space/diary-edit.blade.php`)
- Form: notes, mood, is_rewatch, watched_at
- Back to diary link

### Task 8 — View: History (`space/history.blade.php`)
- **Rewrite layout**: group by month (Letterboxd-style)
- Tampilkan poster film + rating + status badge
- Header tiap bulan: "Juli 2026"
- Total "X jam ditonton" di atas
- Filter by status (existing, OK)

### Task 9 — View: Watchlist & Favorites
- Header info: "X film dalam watchlist" / "X film favorit"
- Rata-rata rating (opsional)

### Task 10 — Cleanup
- Hapus `resources/views/your-space.blade.php` (legacy)

### Task 11 — Tests
- Update `SpaceTest` — assertions untuk section baru di setiap halaman
- Baru: `SpaceServiceTest` — unit test method baru

---

## Files Changed

| File | Type |
|------|------|
| `database/migrations/xxxx_add_movie_title_to_diary_entries.php` | **New** |
| `app/Models/DiaryEntry.php` | Edit |
| `app/Services/User/SpaceService.php` | Edit |
| `app/Http/Controllers/SpaceController.php` | Edit |
| `routes/web.php` | Edit |
| `resources/views/space/index.blade.php` | Edit |
| `resources/views/space/diary.blade.php` | **Rewrite** |
| `resources/views/space/diary-edit.blade.php` | **New** |
| `resources/views/space/history.blade.php` | **Rewrite** |
| `resources/views/space/watchlist.blade.php` | Edit |
| `resources/views/space/favorites.blade.php` | Edit |
| `resources/views/your-space.blade.php` | **Delete** |
| `tests/Feature/SpaceTest.php` | Edit |
| `tests/Unit/SpaceServiceTest.php` | **New** |
