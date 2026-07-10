# Performance Optimization Guide — Jakka Space

> **Masalah utama:** Web terasa berat karena 3 akar masalah:
> 1. **Cache malah nambah beban DB** (P0)
> 2. **Loop HTTP request ke TMDB di tiap halaman** (P0)
> 3. **Query DB tidak efisien — N+1 & missing index** (P1)

---

## 1. CACHE STORE = DATABASE (KRITIS)

### Kenapa berat
Di `.env` lo pake `CACHE_STORE=database`. Artinya setiap `Cache::remember()`:
1. SELECT cache dari DB (1 query)
2. Kalau miss: SELECT ulang + INSERT ke DB (2 query)
3. Kalau hit: tinggal SELECT (1 query)

Jadi **cache malah nambah beban DB**, bukan ngurangin. Aplikasi punya ~20+ `Cache::remember()` per page load — masing-masing nambah 1-2 query DB.

### Fix cepat (tanpa Redis)
```diff
- CACHE_STORE=database
+ CACHE_STORE=file
```
File cache lokal, zero dependency, langsung cepat.

### Fix ideal (kalau Railway support Redis)
Add Redis add-on di Railway, lalu:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```
Redis juga nyelesain masalah session & queue yang sekarang juga pake database.

---

## 2. LOOP HTTP REQUEST KE TMDB (N+1 API CALLS)

### Kenapa berat

**File: `app/Services/User/SpaceService.php:323-335`**
```php
foreach ($tmdbIds as $tmdbId) {
    [$detail] = $this->movieService->findMovie((int) $tmdbId);
    // ...
}
```
Anggap user punya 20 film di watchlist → 20 HTTP request ke TMDB.
Profil page manggil: `getRecentWatched(10)` + `getFavoriteMovies(20)` + `getRecentReviews(5)` = 35 HTTP request **per page load**.

Kalau pakai `CACHE_STORE=database`, tiap request malah:
1. Cek cache DB
2. Miss → HTTP ke TMDB
3. Simpan hasil ke cache DB

**Total per page load: 35 HTTP ke TMDB + 70 query DB cuma buat cache.**

**Lokasi lain dengan pola sama:**
| File | Method | Line |
|---|---|---|
| `app/Services/User/ProfileService.php` | `fetchMovies()` | 121-133 |
| `app/Services/User/ProfileService.php` | `getReviewedMovies()` | 100-111 |
| `app/Services/User/AnalyticsService.php` | `topGenres()` | 103-115 |
| `app/Services/User/AnalyticsService.php` | `mostRewatched()` | 143-150 |
| `app/Services/User/PinnedMovieService.php` | `getPinnedMovies()` | foreach |

### Fix cepat
Pindahin `CACHE_STORE` ke `file` dulu — setidaknya setelah hit pertama, request berikutnya gak perlu HTTP ke TMDB.

### Fix jangka panjang
Simpan data film yang udah di-fetch ke **tabel `movies` lokal**:

```php
// app/Models/Movie.php
class Movie extends Model
{
    protected $fillable = [
        'tmdb_id', 'title', 'poster_url', 'release_year',
        'genres', 'overview', 'rating', 'cached_at'
    ];
}
```

Buat migration:
```php
Schema::create('movies', function (Blueprint $table) {
    $table->id();
    $table->integer('tmdb_id')->unique();
    $table->string('title');
    $table->string('poster_url')->nullable();
    $table->year('release_year')->nullable();
    $table->string('genres')->nullable();
    $table->text('overview')->nullable();
    $table->decimal('rating', 3, 1)->nullable();
    $table->timestamp('cached_at');
    $table->timestamps();
});
```

Ubah `MovieService::findMovie()` — check DB lokal dulu, baru ke TMDB:

```php
public function findMovie(int $movieId): array
{
    $movie = Movie::where('tmdb_id', $movieId)->first();

    if ($movie && $movie->cached_at->diffInHours(now()) < 24) {
        return [$movie->toArray(), null];
    }

    [$data, $error] = $this->tmdb->get("/movie/{$movieId}", [
        'append_to_response' => 'videos,credits,release_dates',
    ]);

    if ($error !== null) {
        return [$movie?->toArray(), $error];
    }

    $transformed = $this->transformer->transformDetail($data);

    Movie::updateOrCreate(
        ['tmdb_id' => $movieId],
        [
            'title' => $transformed['title'],
            'poster_url' => $transformed['poster_url'],
            'release_year' => $transformed['release_year'],
            'genres' => $transformed['genres'],
            'overview' => $transformed['overview'],
            'rating' => $transformed['rating'],
            'cached_at' => now(),
        ]
    );

    return [$transformed, null];
}
```

Dengan ini: 1 query SELECT batch (`WHERE tmdb_id IN (...)`) ganti 20-35 query individu.

---

## 3. RECURSIVE N+1 DI REVIEW COMMENTS

### Kenapa berat

**File: `app/Models/ReviewComment.php:43-52`**
```php
public function getAllReplies()
{
    $replies = collect();
    foreach ($this->replies()->with('user')->get() as $reply) {
        $replies->push($reply);
        $replies = $replies->merge($reply->getAllReplies());
    }
    return $replies->sortBy('created_at');
}
```
Ini recursive. Comment chain depth 3 dengan masing-masing 5 replies:
- Level 0: 1 query
- Level 1: 5 queries
- Level 2: 25 queries
- Level 3: 125 queries

**Total: ~156 queries** untuk 1 diskusi.

**Dipanggil di Blade:** `resources/views/movies/partials/tab-diskusi.blade.php:104`

### Fix
Ganti pake eager load semua replies sekaligus, bukan recursive:

```php
// Di model Review
public function comments(): HasMany
{
    return $this->hasMany(ReviewComment::class);
}

public function allComments()
{
    return $this->comments()
        ->with('user')
        ->with('replies.user')
        ->orderBy('created_at')
        ->get()
        ->groupBy('parent_id');
}
```

Atau batasin depth pake Nested Set (package `baum` / `laravel-nestedset`).

**Fix minimal:**
```php
public function getAllReplies(): Collection
{
    return $this->replies()->with('user')->get()->sortBy('created_at');
}
```
Ini jadi flat — 1 level doang, 1 query. Gak recursive.

---

## 4. MISSING DATABASE INDEXES

### Kenapa berat
Query `WHERE user_id = N ORDER BY created_at DESC` tanpa composite index → MySQL full scan.

### Tabel bermasalah

| Tabel | Missing Index | Contoh Query |
|---|---|---|
| `reviews` | `(tmdb_id)` | `WHERE tmdb_id = N` |
| `reviews` | `(user_id, created_at)` | Profile page, activity feed |
| `watch_histories` | `(user_id, status, created_at)` | Space page stats |
| `watchlists` | `(user_id, created_at)` | Watchlist page |
| `favorites` | `(user_id, created_at)` | Favorites page |
| `diary_entries` | `(user_id, watched_at)` | Diary page |
| `review_comments` | `(review_id)`, `(parent_id)` | Diskusi tab |
| `activity_logs` | `(user_id, type, created_at)` | Activity feed |

### Fix
Buat migration baru:

```php
public function up(): void
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->index(['user_id', 'created_at']);
        $table->index('tmdb_id');
    });

    Schema::table('watch_histories', function (Blueprint $table) {
        $table->index(['user_id', 'status', 'created_at']);
    });

    Schema::table('watchlists', function (Blueprint $table) {
        $table->index(['user_id', 'created_at']);
    });

    Schema::table('favorites', function (Blueprint $table) {
        $table->index(['user_id', 'created_at']);
    });

    Schema::table('diary_entries', function (Blueprint $table) {
        $table->index(['user_id', 'watched_at']);
    });

    Schema::table('review_comments', function (Blueprint $table) {
        $table->index('review_id');
        $table->index('parent_id');
    });

    Schema::table('activity_logs', function (Blueprint $table) {
        $table->index(['causer_id', 'type', 'created_at']);
    });
}
```

---

## 5. SESSION & QUEUE JUGA PAKAI DATABASE

### Kenapa berat
```
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```
- Setiap request: read + write session ke DB
- Background job: queue worker polling DB terus

### Fix
```diff
- SESSION_DRIVER=database
+ SESSION_DRIVER=redis   // atau file

- QUEUE_CONNECTION=database
+ QUEUE_CONNECTION=redis   // atau sync buat dev, atau pakai database tapi kurangi polling
```

Kalau di Railway gak ada Redis, paling gampang:
```
SESSION_DRIVER=file
QUEUE_CONNECTION=database     // masih ok kalau gak heavy
```

---

## 6. MULTIPLE DB QUERIES DI ACTIVITY FEED

### Kenapa berat

**File: `app/Services/User/ActivityFeedService.php:30-141`**
Ada 5+ query terpisah (diaries, reviews, watchlists, favorites, lists) yang masing-masing `latest()->limit()->get()`, lalu digabung di PHP.

### Fix
Cache hasil feed:
```php
Cache::remember("feed.{$userId}", 300, function () use ($userId) {
    // ... 5 queries
});
```

Atau pake **queue job** yang generate feed setiap ada aktivitas baru dan simpan di cache.

---

## Priority Action Plan

| # | Action | Impact | Effort | Timeline |
|---|---|---|---|---|
| 1 | **Ganti `CACHE_STORE=file`** | ⭐⭐⭐ Tertinggi | 1 menit | Sekarang |
| 2 | **Tambah composite indexes** | ⭐⭐⭐ Tertinggi | 15 menit | Sekarang |
| 3 | **Fix recursive `getAllReplies()`** | ⭐⭐ Tinggi | 10 menit | Sekarang |
| 4 | **Ganti `SESSION_DRIVER=file`** | ⭐⭐ Tinggi | 1 menit | Sekarang |
| 5 | **Buat tabel `movies` lokal** | ⭐⭐⭐ Tertinggi | 1-2 jam | Minggu ini |
| 6 | **Cache activity feed** | ⭐ Sedang | 30 menit | Minggu ini |
| 7 | **Redis (kalau support Railway)** | ⭐⭐⭐ Tertinggi | 30 menit | Kalau support |

---

## TL;DR — Root Cause

**Masalah #1 bikin #2 makin parah.** Cache pake database mengubah sistem caching jadi sistem **anti-cache**: tiap Cache::remember() malah nambah query. Ditambah loop TMDB call di tiap halaman — tanpa cache efektif, tiap page load bisa trigger 20-35 HTTP request ke TMDB + puluhan query DB.

**Fix paling berdampak:** `CACHE_STORE=file` (1 menit, zero dependency).
**Fix paling fundamental:** Tabel `movies` lokal + composite indexes.
