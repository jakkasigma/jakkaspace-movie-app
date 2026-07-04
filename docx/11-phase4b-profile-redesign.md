# Phase 4B — Profile Redesign: Workflow

> Created: 2026-07-03
> Status: Ready to Execute
> Prerequisite: Phase 4 ✅

---

## Tujuan Phase 4B

Redesign halaman profil publik `/@username` menjadi seperti profil sosmed (Instagram/Letterboxd) — header dengan foto, nama, stats, follow button, lalu tab konten berisi film dalam bentuk grid poster.

---

## Tampilan Target

```
┌─────────────────────────────────────────────────────┐
│  [ Avatar ]   Nama Lengkap                          │
│               @username                             │
│               Bio singkat di sini                   │
│                                                     │
│   123        45        67        89                 │
│  Ditonton   Review  Followers  Following            │
│                                                     │
│  [ Edit Profil ]  atau  [ Follow ] / [ Following ]  │
└─────────────────────────────────────────────────────┘

[ Film Pilihan ] [ Reviews ] [ Lists ] [ Favorit ]
──────────────────────────────────────────────────
[ poster ][ poster ][ poster ][ poster ][ poster ]
[ poster ][ poster ][ poster ][ poster ][ poster ]
```

Tab **Film Pilihan** adalah tab default yang pertama tampil saat profil dibuka.

---

## Tab Konten

| Tab | Konten | Sumber Data |
|---|---|---|
| **Film Pilihan** *(default)* | Grid poster film yang dipilih user untuk dipamerkan, maks 6 | `pinned_movies` |
| **Reviews** | Grid poster film yang direview — klik buka review page | `reviews` |
| **Lists** | Card list publik (nama, jumlah film) | `movie_lists` is_public=true |
| **Favorit** | Grid poster film favorit | `favorites` |

Grid poster: 3 kolom di mobile, 5-6 kolom di desktop. Rapat seperti Instagram. Hover tampilkan judul + rating. Klik film buka detail, klik review buka review page.

---

## Film Pilihan (Pinned Films)

Section terpisah di atas tab, selalu tampil di profil publik.

- User bisa pilih **maksimal 6 film** untuk dipajang di profil
- Bisa diurutkan (sort_order)
- Tampil sebagai grid poster di bagian paling atas konten, sebelum tab
- Hanya owner yang bisa edit — ada tombol **"Atur Film Pilihan"** di profil sendiri
- Pengunjung lain hanya bisa lihat (tidak bisa edit)

### Tabel baru: `pinned_movies`

```
id
user_id     — FK ke users, cascadeOnDelete
tmdb_id     — integer
sort_order  — integer
timestamps
```

Unique constraint: `(user_id, tmdb_id)`

### PinnedMovieController

```
GET    /profile/pinned               → form pilih film pinned (auth)
POST   /profile/pinned/{movie}       → tambah film ke pinned
DELETE /profile/pinned/{movie}       → hapus dari pinned
```

### PinnedMovieService

```php
getPinnedMovies(User $user): array    ← fetch poster dari TMDB + cache
addPinnedMovie(User $user, int $tmdbId): void   ← max 6, tolak kalau sudah penuh
removePinnedMovie(User $user, int $tmdbId): void
isPinned(User $user, int $tmdbId): bool
```

### Cara user menambah film pinned

Di halaman detail film (`/movies/{id}`), kalau user login, ada opsi **"Sematkan ke Profil"** di user actions (seperti tombol watchlist/favorit). Klik → film ditambah ke pinned. Kalau sudah 6, tombol disabled dengan tooltip "Profil penuh (6/6)".

Di profil sendiri, tiap poster pinned ada tombol ✕ untuk hapus.

---

## Yang Perlu Diubah

### 1 — ProfileService: method baru

Tambah method untuk fetch TMDB data + format untuk grid:

```php
// Ambil tmdb_ids dari watch_histories, lalu fetch poster via MovieService
getWatchedMovies(User $profile): array   ← array of movie arrays (id, title, poster_url, rating)
getFavoritedMovies(User $profile): array
getReviewedMovies(User $profile): array  ← include review rating + review id
```

Caching: tiap user profile di-cache 10 menit. Key: `profile.{userId}.watched`, dll.

> Catatan: fetch TMDB satu per satu untuk setiap film. Batasi maksimal 24 film per tab untuk menghindari terlalu banyak request.

### 2 — ProfileController: update method `show()`

Tambah tab query parameter: `?tab=watched|reviews|lists|favorites`

```php
$activeTab = $request->query('tab', 'watched');

// Fetch data hanya untuk tab yang aktif
$tabData = match ($activeTab) {
    'reviews'  => $this->profileService->getReviewedMovies($profile),
    'lists'    => $this->profileService->getPublicLists($profile),
    'favorites'=> $this->profileService->getFavoritedMovies($profile),
    default    => $this->profileService->getWatchedMovies($profile),
};
```

Pass ke view: `$activeTab`, `$tabData`, `$stats`.

### 3 — View: `profile/show.blade.php` — full redesign

**Header section:**
```
profile-page-header
├── profile-avatar (besar, 96px)
├── profile-identity
│   ├── nama
│   ├── @username
│   └── bio
├── profile-stats (row: ditonton | review | followers | following)
└── profile-actions (Edit Profil / Follow / Following)
```

**Tab bar:**
```
profile-tabs
├── tab: Ditonton (default)
├── tab: Reviews
├── tab: Lists
└── tab: Favorit
```

**Konten tab:**
- Watched, Reviews, Favorit → `profile-grid` (grid poster rapat)
- Lists → `lists-grid` (card yang sudah ada)

**Profile grid item:**
```
profile-grid-item
├── poster image (aspect-ratio 2/3)
├── overlay (hover): judul + rating
└── link ke movie detail (atau review page untuk tab Reviews)
```

### 4 — CSS baru

Class yang perlu ditambah:
```css
.profile-page          — layout page baru, tanpa space-page
.profile-page-header   — header profil
.profile-avatar-lg     — avatar besar (96px)
.profile-identity      — nama + username + bio
.profile-stats-row     — stats horizontal
.profile-actions       — tombol follow/edit
.profile-tabs          — tab bar horizontal
.profile-tab-item      — tiap tab
.profile-tab-item.active
.profile-grid          — grid poster rapat
.profile-grid-item     — satu item grid
.profile-grid-overlay  — overlay hover
```

### 5 — Polish lainnya sekaligus

- **Feed**: tampilkan judul film dari TMDB (cache, fetch saat build feed)
- **Review page di movie detail**: tambah link "Lihat review →" ke `/reviews/{id}` di section review yang sudah ada di `movies/show.blade.php`
- **Diary like button**: tambah tombol like ♡ di tiap diary card di profil publik

---

## Urutan Eksekusi

```
1. ProfileService — tambah getWatchedMovies(), getFavoritedMovies(), getReviewedMovies()
2. ProfileController — update show() dengan tab logic
3. View profile/show.blade.php — full redesign
4. CSS — tambah profile page styles
5. Polish: feed judul film, review link di movie detail, diary like di profil
6. Tests — update ProfileTest
7. Pint
```

---

## Catatan Arsitektur

- **Tidak ada infinite scroll** — cukup 24 film per tab, tampil semua sekaligus
- **Tab switching via URL query param** (`?tab=reviews`) — shareable, tidak perlu JS
- **TMDB fetch di-cache per user** — menghindari terlalu banyak request saat profile dibuka berulang
- **Edit Profil** → redirect ke `/profile` (halaman settings yang sudah ada)
- **Stats di header**: Ditonton + Review + Followers + Following (tanpa Lists — terlalu banyak)
