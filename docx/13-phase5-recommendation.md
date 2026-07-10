# Phase 5 — Recommendation: Workflow

> Created: 2026-07-03
> Status: Ready to Execute
> Prerequisite: Phase 4B ✅ Notifikasi ✅

---

## Tujuan Phase 5

Memberikan rekomendasi film yang relevan dan personal berdasarkan data yang sudah ada — watch history, genre favorit, dan aktivitas following.

---

## Tiga Fitur yang Dibangun

### Fitur 1 — "Karena Kamu Menonton..."

Di halaman detail film (`/movies/{id}`), setelah section "Film Serupa" yang sudah ada, tambahkan section baru: **"Karena kamu menonton film ini"** — menampilkan film dari genre yang sama yang belum pernah ditonton user.

**Logic:**
1. Ambil genre dari film yang sedang dilihat
2. Fetch film populer dari genre tersebut via TMDB `/discover/movie`
3. Filter: hapus film yang sudah ada di `watch_histories` user
4. Tampilkan maksimal 10 film

Hanya tampil kalau user sudah login dan sudah pernah nonton setidaknya 1 film.

---

### Fitur 2 — Personalized Discover

Di halaman `/discover`, kalau user sudah login, tambahkan section **"Untukmu"** di atas filter biasa — berisi film yang direkomendasikan berdasarkan genre favorit user.

**Logic:**
1. Hitung genre paling sering muncul di `watch_histories` + `favorites` user (via join ke TMDB data yang di-cache)
2. Ambil top 3 genre
3. Fetch film populer dari genre-genre itu
4. Filter: hapus yang sudah ditonton
5. Tampilkan sebagai horizontal row, maks 12 film

Kalau user belum punya watch history cukup (< 3 film), section ini tidak muncul.

---

### Fitur 3 — "Trending di Following"

Di halaman Feed (`/feed`), tambahkan section di atas activity stream: **"Lagi ditonton orang yang kamu ikuti"** — film yang paling banyak ditambahkan ke watch history oleh following dalam 30 hari terakhir.

**Logic:**
1. Ambil semua `following_ids` user
2. Query `watch_histories` WHERE `user_id` IN following_ids AND `created_at` >= 30 hari lalu
3. Group by `tmdb_id`, hitung jumlah, ambil top 6
4. Fetch detail film dari TMDB
5. Tampilkan sebagai grid poster di atas feed

---

## Yang Perlu Dibuat

### 1 — RecommendationService

**File:** `app/Services/Movie/RecommendationService.php`

```php
// Fitur 1d
getGenreRecommendations(User $user, array $movieGenreIds, int $watchedTmdbId): array
  → fetch dari TMDB by genre, filter sudah ditonton, return maks 10

// Fitur 2
getPersonalizedMovies(User $user): array
  → hitung top genres dari history, fetch film, filter sudah ditonton, return maks 12

// Fitur 3
getTrendingAmongFollowing(User $user): array
  → query watch_histories following 30 hari, group, fetch detail, return maks 6
```

Caching:
- Personalized: `recommendation.{userId}.personal` — 2 jam
- Trending following: `recommendation.{userId}.following_trending` — 1 jam
- Genre recommendations: tidak di-cache (tergantung film yang dilihat)

### 2 — Update MovieController::show()

Inject `RecommendationService`. Kalau user login dan `watch_status === 'watched'`, fetch genre recommendations dan pass ke view.

```php
$genreRecommendations = [];
if ($user !== null && $movieDetail !== null) {
    $genreIds = $this->extractGenreIds($movieDetail);
    if (! empty($genreIds)) {
        $genreRecommendations = $this->recommendationService
            ->getGenreRecommendations($user, $genreIds, $movie);
    }
}
```

### 3 — Update DiscoverController::index()

Inject `RecommendationService`. Pass `$personalizedMovies` ke view.

```php
$personalizedMovies = [];
if (auth()->check()) {
    $personalizedMovies = $this->recommendationService->getPersonalizedMovies(auth()->user());
}
```

### 4 — Update ActivityFeedController::index()

Inject `RecommendationService`. Pass `$trendingFollowing` ke view.

```php
$trendingFollowing = $this->recommendationService->getTrendingAmongFollowing($request->user());
```

### 5 — Update Views

**`movies/show.blade.php`** — tambah section setelah "Film Serupa":
```blade
@if (! empty($genreRecommendations))
    <section>
        <h3>Karena kamu menonton film ini</h3>
        <div class="movie-row">...</div>
    </section>
@endif
```

**`movies/discover.blade.php`** — tambah section "Untukmu" di atas filter:
```blade
@if (! empty($personalizedMovies))
    <section class="discover-personal-section">
        <h2>Untukmu</h2>
        <div class="movie-row">...</div>
    </section>
@endif
```

**`feed/index.blade.php`** — tambah section di atas feed list:
```blade
@if ($trendingFollowing->isNotEmpty())
    <section class="feed-trending-section">
        <h2>Lagi ditonton orang yang kamu ikuti</h2>
        <div class="movie-row">...</div>
    </section>
@endif
```

### 6 — Tests

```
tests/Feature/RecommendationTest.php
```

Cover:
- Genre recommendations tidak include film yang sudah ditonton
- Personalized returns empty kalau < 3 film di history
- Trending following hanya include film dari 30 hari terakhir

---

## Urutan Eksekusi

```
1. RecommendationService (3 methods)
2. Update MovieController + view (Fitur 1)
3. Update DiscoverController + view (Fitur 2)
4. Update ActivityFeedController + view (Fitur 3)
5. CSS (minimal — pakai class yang sudah ada)
6. Tests
7. Pint
```

---

## Catatan Arsitektur

- **Tidak ada ML** — rekomendasi berbasis genre dan aktivitas sederhana, bukan collaborative filtering
- **TMDB sebagai sumber** — semua rekomendasi dari TMDB `/discover/movie`, bukan dari database internal
- **Filter ditonton** — cukup cek `watch_histories` table, tidak perlu join kompleks
- **Graceful degradation** — kalau data tidak cukup, section tidak muncul sama sekali (tidak error)
- **Cache agresif** — personalized di-cache 2 jam karena data user jarang berubah drastis
