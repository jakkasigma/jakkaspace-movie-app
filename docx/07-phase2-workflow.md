# Phase 2 — Personal Movie Diary: Workflow

> Created: 2026-07-02
> Status: Ready to Execute
> Prerequisite: Phase 0 ✅ Phase 1 ✅

---

## Tujuan Phase 2

Mengubah Jakkaspace dari sekedar movie discovery menjadi tempat pengguna mencatat perjalanan menonton mereka secara personal.

Setelah phase ini selesai, pengguna yang sudah login bisa:
- Menandai film sudah ditonton
- Menulis catatan dan mood saat menonton
- Memberi rating 1–10
- Menyimpan film ke watchlist
- Menandai film favorit
- Melihat semua riwayat tontonan mereka
- Melihat statistik personal sederhana

---

## Kondisi Awal

Yang sudah tersedia dan siap dipakai:

| Tersedia | Keterangan |
|---|---|
| Auth (Breeze) | Login, register, email verify sudah jalan |
| Tabel `diary_entries` | user_id, tmdb_id, watched_at, notes, mood, is_rewatch |
| Tabel `reviews` | user_id, tmdb_id, rating, body, has_spoiler |
| Tabel `watch_histories` | user_id, tmdb_id, status (watched/watching/dropped) |
| Tabel `watchlists` | user_id, tmdb_id |
| Tabel `favorites` | user_id, tmdb_id |
| Model semua domain | DiaryEntry, Review, WatchHistory, Watchlist, Favorite |
| User relasi | sudah ada semua hasMany |
| MovieService | findMovie(), genres(), dll. sudah ada |
| Halaman `/your-space` | sudah ada tapi kosong, ini yang akan jadi dashboard |

---

## Urutan Pengerjaan

### Langkah 1 — UserActivityService

Buat service baru khusus untuk semua aktivitas pengguna terhadap film.

**File:** `app/Services/User/UserActivityService.php`

**Method yang akan ada:**
```
markAsWatched(User $user, int $tmdbId): WatchHistory
markAsWatching(User $user, int $tmdbId): WatchHistory
markAsDropped(User $user, int $tmdbId): WatchHistory
removeFromHistory(User $user, int $tmdbId): void
getWatchStatus(User $user, int $tmdbId): ?string

addToWatchlist(User $user, int $tmdbId): Watchlist
removeFromWatchlist(User $user, int $tmdbId): void
isOnWatchlist(User $user, int $tmdbId): bool

addToFavorites(User $user, int $tmdbId): Favorite
removeFromFavorites(User $user, int $tmdbId): bool
isFavorited(User $user, int $tmdbId): bool
```

---

### Langkah 2 — DiaryService

Service terpisah untuk logika diary karena lebih kompleks dari sekadar toggle.

**File:** `app/Services/User/DiaryService.php`

**Method yang akan ada:**
```
createEntry(User $user, int $tmdbId, array $data): DiaryEntry
updateEntry(DiaryEntry $entry, array $data): DiaryEntry
deleteEntry(DiaryEntry $entry): void
getUserEntries(User $user, int $page = 1): array
getEntriesForMovie(User $user, int $tmdbId): Collection
```

---

### Langkah 3 — ReviewService

**File:** `app/Services/User/ReviewService.php`

**Method yang akan ada:**
```
upsertReview(User $user, int $tmdbId, array $data): Review
deleteReview(Review $review): void
getUserReview(User $user, int $tmdbId): ?Review
```

---

### Langkah 4 — Form Requests

Validasi untuk semua input pengguna.

```
app/Http/Requests/DiaryEntryRequest.php
app/Http/Requests/ReviewRequest.php
```

---

### Langkah 5 — Controllers

```
app/Http/Controllers/WatchHistoryController.php   — toggle watched/watching/dropped
app/Http/Controllers/WatchlistController.php      — toggle watchlist
app/Http/Controllers/FavoriteController.php       — toggle favorite
app/Http/Controllers/DiaryController.php          — CRUD diary entry
app/Http/Controllers/ReviewController.php         — upsert & delete review
app/Http/Controllers/SpaceController.php          — halaman your-space (dashboard)
```

Semua controller di atas hanya bisa diakses oleh user yang sudah login (`auth` middleware).

---

### Langkah 6 — Routes

Tambah ke `routes/web.php` dalam group `middleware('auth')`:

```
POST   /movies/{movie}/watch           → WatchHistoryController@store
DELETE /movies/{movie}/watch           → WatchHistoryController@destroy

POST   /movies/{movie}/watchlist       → WatchlistController@store
DELETE /movies/{movie}/watchlist       → WatchlistController@destroy

POST   /movies/{movie}/favorite        → FavoriteController@store
DELETE /movies/{movie}/favorite        → FavoriteController@destroy

POST   /movies/{movie}/diary           → DiaryController@store
PUT    /diary/{entry}                  → DiaryController@update
DELETE /diary/{entry}                  → DiaryController@destroy

POST   /movies/{movie}/review          → ReviewController@store
DELETE /review/{review}                → ReviewController@destroy

GET    /your-space                     → SpaceController@index (sudah ada, perlu diupdate)
GET    /your-space/diary               → SpaceController@diary
GET    /your-space/history             → SpaceController@history
GET    /your-space/watchlist           → SpaceController@watchlist
GET    /your-space/favorites           → SpaceController@favorites
```

---

### Langkah 7 — Update Movie Detail Page

Tambah tombol aksi ke halaman `/movies/{id}` yang sebelumnya hanya placeholder:

- **Sudah Ditonton** — toggle, tampilkan status jika sudah
- **Watchlist** — toggle simpan/hapus
- **Favorit** — toggle
- **Tulis Review** — form rating + teks
- **Tulis Diary** — form tanggal + catatan + mood

Jika user belum login → tombol redirect ke `/login`.

---

### Langkah 8 — Your Space Dashboard

Isi halaman `/your-space` yang sekarang masih kosong.

**Layout dashboard:**
```
┌─────────────────────────────────────┐
│  Header: nama user + statistik      │
│  (total ditonton, review, diary)    │
├──────────┬──────────────────────────┤
│  Nav     │  Content Area            │
│  ─────── │  ─────────────────────── │
│  Diary   │  [sesuai tab aktif]      │
│  History │                          │
│  Watch-  │                          │
│  list    │                          │
│  Favorit │                          │
└──────────┴──────────────────────────┘
```

**Halaman-halaman:**
- `/your-space` — ringkasan: film terbaru ditonton, diary terbaru, watchlist
- `/your-space/diary` — semua diary entry dengan pagination
- `/your-space/history` — watch history dengan filter status
- `/your-space/watchlist` — semua film di watchlist
- `/your-space/favorites` — semua film favorit

---

### Langkah 9 — Statistik Personal

Tambah ke dashboard:
- Total film ditonton
- Total diary entry
- Total review
- Total watchlist
- Genre favorit (dari watch history, paling banyak ditonton)

Statistik diambil dari database, bukan TMDB.

---

### Langkah 10 — Tests

```
tests/Feature/WatchHistoryTest.php
tests/Feature/WatchlistTest.php
tests/Feature/FavoriteTest.php
tests/Feature/DiaryEntryTest.php
tests/Feature/ReviewTest.php
tests/Feature/SpaceTest.php
```

---

## Catatan Desain UI

- Tombol aksi di movie detail mengikuti gaya existing: Bebas Neue, border style
- Dashboard `/your-space` menggunakan layout yang sama dengan halaman lain (extend `layouts.movie`)
- Mobile: tab navigasi di bawah layar untuk dashboard, bukan sidebar
- Semua aksi (watch, watchlist, favorit) menggunakan form POST biasa — tidak pakai JavaScript fetch dulu, bisa ditambah later untuk UX yang lebih smooth
- Status aktif ditampilkan dengan perubahan visual pada tombol (warna berbeda, teks berubah)

---

## Aturan Tambahan Phase 2

- Semua route baru wajib pakai middleware `auth`
- User hanya bisa edit/delete milik sendiri — cek ownership di controller atau policy
- `tmdb_id` selalu disimpan sebagai integer, bukan string
- Tidak menyimpan data film ke tabel `movies` dulu — hanya `tmdb_id` sebagai referensi
- Semua response setelah aksi menggunakan `redirect()->back()` atau redirect ke halaman yang relevan

---

## Status Phase 2 — ✅ SELESAI

Semua 14 langkah dalam workflow ini sudah dikerjakan:

| Langkah | Status |
|---|---|
| 1. UserActivityService | ✅ |
| 2. DiaryService | ✅ |
| 3. ReviewService | ✅ |
| 4. Form Requests | ✅ |
| 5. WatchHistory/Watchlist/Favorite Controllers | ✅ |
| 6. Routes | ✅ |
| 7. Movie Detail — tombol aksi + form diary/review | ✅ |
| 8. SpaceController | ✅ |
| 9. Views: index, diary, history, watchlist, favorites | ✅ |
| 10. Statistik personal | ✅ |
| 11. Tests (73 total, semua passing) | ✅ |

```
1. UserActivityService
2. DiaryService
3. ReviewService
4. Form Requests (DiaryEntryRequest, ReviewRequest)
5. WatchHistoryController + route + test
6. WatchlistController + route + test
7. FavoriteController + route + test
8. Update movie detail page — tambah tombol aksi
9. DiaryController + route + test
10. ReviewController + route + test
11. SpaceController + halaman your-space
12. Halaman diary, history, watchlist, favorites
13. Statistik personal
14. Test SpaceController
```

---

*Dokumen ini adalah workflow operasional untuk Phase 2. Update setiap langkah selesai dikerjakan.*
