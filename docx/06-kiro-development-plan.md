selanjutnya apa# Jakkaspace — Development Plan

> Created: 2026-07-02
> Last Updated: 2026-07-02
> Status: Active
> Based on: 00-awalmula.md, 01-product-foundation.md, 02-panduan-claudecode.md, 03-system-architecture.md, 04-domain-design.md, 05-roadmap.md

---

## Status Terkini

| Aspek | Kondisi |
|---|---|
| Framework | Laravel 12 + PHP 8.2 ✅ |
| Auth | Laravel Breeze sudah terpasang ✅ |
| Database | 9 tabel domain inti sudah ada ✅ |
| TMDB Integration | TmdbClient terpisah, bersih ✅ |
| Service Layer | MovieService + MovieTransformer ✅ |
| MovieController | Tipis, 3 method, ~60 baris ✅ |
| Design System | Layout + komponen movie dasar ✅ |
| Tests | 73 tests passing ✅ |
| Routing | Redirect ke `/your-space` sudah dikonfigurasi ✅ |

**Posisi sekarang: Phase 1, 2, 3 & 4 selesai, siap masuk Phase 5.**

---

## Phase 0 — Foundation ✅ SELESAI

### 0.1 — TMDB Client ✅

**Yang dibuat:**
```
app/Services/Tmdb/TmdbClient.php
```
- `TmdbClient` handle semua HTTP request ke TMDB
- Method `get()`, `listing()`, `image()` — bersih, tidak ada business logic
- Image proxy logic masuk ke dalam `TmdbClient::image()`

> Catatan: `TmdbImageService` digabung ke `TmdbClient` karena scope-nya kecil dan tidak perlu dipisah.

---

### 0.2 — Movie Service ✅

**Yang dibuat:**
```
app/Services/Movie/MovieService.php
app/Services/Movie/MovieTransformer.php
```

**MovieController sekarang:**
- `index()`, `show()`, `image()` — 3 method, ~60 baris total
- Tidak ada business logic, tidak ada HTTP request langsung

---

### 0.3 — Database Foundation ✅

**Migrasi yang sudah dijalankan:**
```
add_profile_fields_to_users_table   — username, bio, avatar, is_private
create_movies_table                 — tmdb_id, title, poster_path, dll.
create_diary_entries_table          — user_id, tmdb_id, watched_at, notes, mood, is_rewatch
create_reviews_table                — user_id, tmdb_id, rating, body, has_spoiler
create_movie_lists_table            — user_id, name, description, is_public
create_watch_histories_table        — user_id, tmdb_id, status
create_favorites_table              — user_id, tmdb_id
create_watchlists_table             — user_id, tmdb_id
create_list_movies_table            — movie_list_id, tmdb_id, sort_order
```

**Model yang dibuat:** `Movie`, `DiaryEntry`, `Review`, `WatchHistory`, `Watchlist`, `Favorite`, `MovieList`, `ListMovie`

Setiap model punya factory dengan state yang relevan.

---

### 0.4 — User Domain Extension ✅

**Ditambah ke `User` model:**
- Field: `username`, `bio`, `avatar`, `is_private`
- Relasi: `diaryEntries()`, `reviews()`, `watchHistories()`, `watchlists()`, `favorites()`, `movieLists()`

---

### 0.5 — Design System ✅

**Layout dibuat:**
```
resources/views/layouts/movie.blade.php   — layout tunggal, handle font + Vite
```

**Komponen movie dibuat:**
```
resources/views/components/movie/navbar.blade.php
resources/views/components/movie/hero.blade.php
resources/views/components/movie/card.blade.php
resources/views/components/movie/section.blade.php
```

**Direfactor:**
- `welcome.blade.php` — dari ~130 baris HTML mentah → ~45 baris bersih
- `movies/show.blade.php` — boilerplate duplikat dihapus, extend layout

Mobile design akan disesuaikan organik seiring perkembangan fitur.

---

### 0.6 — Testing Foundation ✅

**Tests yang dibuat:**
```
tests/Unit/MovieTransformerTest.php    — 8 tests
tests/Feature/MovieServiceTest.php    — 8 tests
```

Total: **46 tests passing, 191 assertions.**

---

## Phase 1 — Movie Discovery

**Tujuan:** Pengalaman terbaik menemukan film. Baru bisa dimulai setelah Phase 0 selesai.

### Halaman yang akan dibangun:

**Home (`/`)**
- Hero section dengan film featured
- Section: Trending Today, Popular, Now Playing, Upcoming
- Data dari `MovieService` via cache

**Discover (`/discover`)**
- Filter by genre, year, rating, sort
- Infinite scroll atau pagination
- URL parameters untuk shareable filters

**Search (`/search`)**
- Real-time search atau form submit
- Hasil film + orang (aktor/sutradara)

**Movie Detail (`/movies/{id}`)**
- Hero dengan backdrop
- Info lengkap: rating, runtime, genre, tagline
- Cast & crew
- Trailer embed
- Film serupa
- Tombol aksi: Mark as Watched, Add to Watchlist, Rate (placeholder jika user belum login)

**Genre (`/genre/{id}`)**
- List film berdasarkan genre

### Caching Strategy untuk Phase 1:
```
trending          — cache 1 jam
popular           — cache 6 jam
genres            — cache 24 jam
movie detail      — cache 24 jam
search results    — tidak di-cache (hasil real-time)
```

---

## Phase 2 — Personal Movie Diary

**Tujuan:** Mengubah app menjadi tempat pengguna mencatat perjalanan menonton.

**Fitur yang akan dibangun:**
- Mark as Watched — catat film yang sudah ditonton
- Diary Entry — tambah catatan personal, mood, tanggal nonton
- Rewatch — tandai film yang ditonton ulang
- Rating — beri nilai 1–5 atau 1–10
- Watchlist — simpan film yang ingin ditonton
- Favorites — tandai film favorit
- Watch History page — riwayat semua tontonan
- Personal Statistics — total film, genre favorit, film paling sering ditonton ulang

---

## Phase 3 — Movie Collection

**Tujuan:** Pengguna bisa kelola koleksi film dengan bebas.

**Fitur yang akan dibangun:**
- Custom Lists — buat list dengan nama dan deskripsi bebas
- Public/Private List — pilih apakah list bisa dilihat orang lain
- List sharing — share URL list ke luar
- Tags — labeli film dalam collection

---

## Phase 4 — Community

**Tujuan:** Interaksi antar pengguna.

**Fitur yang akan dibangun:**
- Public Profile (`/@username`)
- Follow / Unfollow
- Activity Feed — lihat aktivitas orang yang di-follow
- Likes pada review dan diary
- Comments pada review
- Review page publik

---

## Phase 5 — Recommendation

**Tujuan:** Rekomendasi yang personal.

**Fitur yang akan dibangun:**
- "Because you watched..." — based on watch history
- Personalized Discover — filter berdasarkan genre favorit pengguna
- "Trending among people you follow"

---

## Phase 6 — Ecosystem

**Tujuan:** Platform yang matang dan lengkap.

**Fitur yang akan dibangun:**
- Notification system
- Achievements / badges
- Analytics dashboard untuk pengguna
- Public API (opsional)

---

## Urutan Eksekusi Konkret

```
1.  TmdbClient                        ✅ selesai
2.  TmdbImageService                  ✅ (digabung ke TmdbClient)
3.  MovieTransformer                  ✅ selesai
4.  MovieService                      ✅ selesai
5.  Refactor MovieController          ✅ selesai
6.  Tests untuk service baru          ✅ selesai
7.  Migrasi database domain inti      ✅ selesai
8.  Model + Factory                   ✅ selesai
9.  User domain extension             ✅ selesai
10. Design system components          ✅ selesai
11. Home page + caching               ✅ selesai
12. Discover page                     ✅ selesai (filter genre, tahun, sort, pagination)
13. Search page                       ✅ (sudah ada di home via query param)
14. Movie Detail page (diperkaya)     ✅ selesai (tambah similar movies)
15. Genre page                        ✅ selesai
--- Phase 1 selesai ---
16. Auth gates untuk fitur personal   ✅ selesai
17. Mark as Watched flow              ✅ selesai
18. Diary Entry                       ✅ selesai
19. Rating / Review                   ✅ selesai
20. Watchlist & Favorites             ✅ selesai
21. Watch History page                ✅ selesai
22. Your Space dashboard + semua tab  ✅ selesai
--- Phase 2 selesai ---
23. MovieListService                   ✅ selesai
24. MovieListRequest                   ✅ selesai
25. MovieListController + routes       ✅ selesai
26. ListMovieController + routes       ✅ selesai
27. Views (index, create, edit, show)  ✅ selesai
28. Update movie detail — "Tambah ke List" ✅ selesai
29. Update space nav + tab bar         ✅ selesai
30. Tests (MovieListTest, ListMovieTest) ✅ selesai (21 tests)
--- Phase 3 selesai ---
41. Migrasi + Model (Follow, ReviewLike, DiaryLike, ReviewComment) ✅ selesai
32. Update relasi User, Review, DiaryEntry  ✅ selesai
33. ProfileService                     ✅ selesai
34. FollowService                      ✅ selesai
35. InteractionService                 ✅ selesai
36. ActivityFeedService                ✅ selesai
37. Controllers + Routes               ✅ selesai
38. Views (profile, review page, feed) ✅ selesai
39. Update navbar (link Feed)          ✅ selesai
40. CSS Phase 4                        ✅ selesai
41. Tests (31 tests Phase 4)           ✅ selesai
--- Phase 4 selesai ---
42. PinnedMovie tabel + model + service ✅ selesai
43. Profile redesign (Instagram-style)  ✅ selesai
44. Upload foto profil                  ✅ selesai
45. Notifikasi (follow, like, comment)  ✅ selesai (11 tests)
--- Phase 4B selesai ---
46. RecommendationService               ✅ selesai
47. Genre recs di movie detail          ✅ selesai
48. Personalized di Discover            ✅ selesai
49. Trending following di Feed          ✅ selesai
50. Tests Phase 5 (6 tests)            ✅ selesai
--- Phase 5 selesai ---
51. Phase 6 — Ecosystem                ← berikutnya
```

**Posisi sekarang: Phase 1–6 selesai.**
---

## Aturan yang Diikuti Selama Development

- Tidak ada business logic di Controller
- Tidak ada HTTP request ke TMDB di luar `TmdbClient`
- Tidak ada query di Blade
- Setiap service punya test
- Setiap perubahan dijalankan `vendor/bin/pint --dirty` setelahnya
- Tidak menambah dependency baru tanpa persetujuan
- Tidak membuat folder struktur baru tanpa persetujuan
- Setiap fitur hanya dikerjakan jika domainnya sudah jelas
- Struktur service: per domain (`app/Services/Movie/`, `app/Services/Tmdb/`, dst.)

## Phase 6 — Account System Revision (Current)

### Latar Belakang

Setelah audit sistem akun, ditemukan beberapa celah dan missing feature:

| Issue | Detail |
|---|---|
| Username tidak diminta saat registrasi | Form register cuma name + email + password. `username` nullable, padahal URL profil adalah `/@{username}`. |
| Tidak ada halaman Account Settings dedicated | Ganti password, ubah email, manage Google account, privasi — belum terpusat. |
| Privacy toggle (`is_private`) tidak bisa diakses user | Field di DB, tidak ada UI. |
| Google-only user tidak bisa set password | `has_password = false`, tidak ada flow untuk set password. |
| Email verification UX kurang mulus | Register → redirect `/your-space` → kena blok `verified` middleware. |
| Username tidak required di ProfileUpdateRequest | Bisa disimpan null. |
| Tidak ada Policy | Ownership cek manual di controller. |
| Tidak ada rate limiting di register | Rawan spam registrasi. |

### Phase 6A — Perbaikan Registrasi ✅ SELESAI

| Langkah | Status |
|---|---|
| A1. Tambah field username + validasi di register | ✅ |
| A2. Auto-suggest username dari name (JS) | ✅ |
| A3. Fix redirect flow — ke verification.notice | ✅ |
| A4. Redesign verify-email page (UI Jakkaspace + Bahasa) | ✅ |

**File yang diubah:**
- `resources/views/auth/register.blade.php` — tambah field username + auto-suggest
- `app/Http/Controllers/Auth/RegisteredUserController.php` — validasi username, redirect fix
- `resources/views/auth/verify-email.blade.php` — redesign UI

### Phase 6B — Account Settings

| Langkah | Status |
|---|---|
| B1. Toggle is_private di settings | ✅ |
| B2. Set Password untuk Google-only users | ✅ |
| B3. Linked Accounts section (Google status) | ✅ |

**File yang diubah:**
- `resources/views/profile/partials/update-profile-information-form.blade.php` — toggle is_private
- `resources/views/profile/partials/update-password-form.blade.php` — conditional set password
- `resources/views/profile/partials/linked-accounts-form.blade.php` — baru
- `resources/views/profile/edit.blade.php` — include partial linked accounts
- `app/Http/Controllers/Auth/PasswordController.php` — handle set password tanpa current_password

### Phase 6C — Authorization & Security

| Langkah | Status |
|---|---|
| C1. UserPolicy | ✅ |
| C2. Rate limiting di register | ✅ |

**File yang diubah:**
- `app/Policies/UserPolicy.php` — baru
- `app/Providers/AppServiceProvider.php` — registrasi policy
- `app/Http/Controllers/Auth/RegisteredUserController.php` — rate limit

### Phase 6D — UX Enhancement

| Langkah | Status |
|---|---|
| D1. Onboarding flow (ditunda) | ⏳ |
| D2. Username required di ProfileUpdateRequest | ✅ |

**File yang diubah:**
- `app/Http/Requests/ProfileUpdateRequest.php` — username jadi required

### Urutan Eksekusi

```
A1 + A2 (username register)     → harus pertama, core flow
    ↓
A3 + A4 (verify email UX)       → tergantung A1 (redirect fix)
    ↓
B1 (privacy toggle)             → parallel dengan B2
B2 (set password Google user)   → parallel dengan B1
    ↓
B3 (linked accounts info)       → tergantung B2
    ↓
C1 (Policy)                     → improve code quality
C2 (rate limit register)        → security
    ↓
D2 (username required di edit)  → setelah semua user punya username
```

### Tests Phase 6

| Test | Status |
|---|---|
| RegistrationTest — tambah username field | ✅ |
| ProfileTest — username required, is_private toggle | ✅ |
| PasswordUpdateTest — set password Google user | ✅ |

---

*Dokumen ini akan diupdate seiring perkembangan project. Dapat dilanjutkan di AI tool apapun (Kiro, Claude Code, Cursor, dll.) selama mengacu pada dokumen ini dan folder docx sebagai source of truth.**
