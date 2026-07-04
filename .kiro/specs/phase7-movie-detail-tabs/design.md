# Design Document

## Overview

Redesign halaman detail film dari satu halaman panjang menjadi tab-based layout dengan
tiga tab: **Info**, **Diskusi**, dan **Serupa**. Perubahan murni di presentation layer —
tidak ada database schema baru. Controller `MovieController::show()` diupdate untuk
membaca `?tab` dan `?sort`, menghitung community rating (cached), dan mengambil reviews
secara paginated untuk tab Diskusi.

## Architecture

### Komponen yang berubah
```
app/Http/Controllers/MovieController.php   ← update show()
resources/views/movies/show.blade.php      ← major restructure
resources/css/welcome.css                  ← tambah CSS tab + community rating
```

### Tidak berubah
```
Database schema, migrations, models, routes
Semua controller lain
ReviewController, ReviewLikeController, ReviewCommentController
```

### Flow request

```
GET /movies/{id}?tab=diskusi&sort=popular
  → MovieController::show()
      → baca $tab = 'diskusi', $sort = 'popular'
      → load $communityRating (cached 1 jam)
      → load $reviewCount (COUNT query)
      → load $communityReviews (paginated, sorted by likes_count)
      → render movies.show dengan semua data
```

## Components and Interfaces

### MovieController::show() — parameter baru

```php
public function show(int $movie, Request $request): View
{
    $tab  = $request->string('tab', 'info')->value();   // 'info' | 'diskusi' | 'serupa'
    $sort = $request->string('sort', 'recent')->value(); // 'popular' | 'recent'

    // ... existing code ...

    // Community rating — selalu di-load, cached 1 jam
    $communityRating = Cache::remember(
        "movie.community_rating.{$movie}",
        3600,
        fn () => Review::where('tmdb_id', $movie)
            ->selectRaw('ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as review_count')
            ->whereNotNull('rating')
            ->first()
    );

    // Total review count untuk badge tab — tidak perlu cache
    $reviewCount = Review::where('tmdb_id', $movie)->count();

    // Community reviews — hanya load saat tab diskusi
    $communityReviews = null;
    if ($tab === 'diskusi') {
        $reviewQuery = Review::where('tmdb_id', $movie)
            ->with('user')
            ->withCount(['likes', 'comments']);

        if ($sort === 'popular') {
            $reviewQuery->orderByDesc('likes_count');
        } else {
            $reviewQuery->latest();
        }

        $communityReviews = $reviewQuery->paginate(10)->withQueryString();
    }

    return view('movies.show', [
        'movie'                => $movieDetail,
        'tab'                  => $tab,
        'sort'                 => $sort,
        'reviewCount'          => $reviewCount,
        'communityRating'      => $communityRating,
        'communityReviews'     => $communityReviews,
        'similarMovies'        => $similarMovies,
        'userActivity'         => $userActivity,
        'userLists'            => $userLists,
        'movieInLists'         => $movieInLists,
        'isPinned'             => $isPinned,
        'pinnedCount'          => $pinnedCount,
        'genreRecommendations' => $genreRecommendations,
        'errorMessage'         => $errorMessage ?? 'Detail film tidak ditemukan.',
    ]);
}
```

### View skeleton baru (`movies/show.blade.php`)

```
<div id="movie-detail">
  <a KEMBALI />

  <div class="detail-container">
    backdrop

    ╔═══════════════════════════════╗
    ║  HERO (selalu visible)        ║
    ║  poster | judul               ║
    ║          | meta               ║
    ║          | TMDB ★ | Kom ★    ║
    ║          | [Trailer][Bagikan] ║
    ║          | user-actions-wrap  ║
    ╚═══════════════════════════════╝

    [Info][Diskusi(N)][Serupa]  ← tab bar

    ╔═══════════════════════════════╗
    ║  TAB CONTENT                  ║
    ║  @if tab=info  → sinopsis,    ║
    ║                  crew, cast,  ║
    ║                  forms        ║
    ║  @elseif diskusi → reviews   ║
    ║  @elseif serupa → similar    ║
    ╚═══════════════════════════════╝

    story-modal
  </div>
</div>
```

### Tab bar HTML pattern

```html
<nav class="detail-tab-bar" aria-label="Navigasi konten film">
    <a href="{{ route('movies.show', $movieId) }}?tab=info"
       class="detail-tab-link @if($tab === 'info') tab-active @endif">
        Info
    </a>
    <a href="{{ route('movies.show', $movieId) }}?tab=diskusi"
       class="detail-tab-link @if($tab === 'diskusi') tab-active @endif">
        Diskusi@if($reviewCount > 0) ({{ $reviewCount }})@endif
    </a>
    <a href="{{ route('movies.show', $movieId) }}?tab=serupa"
       class="detail-tab-link @if($tab === 'serupa') tab-active @endif">
        Serupa
    </a>
</nav>
```

### Community rating HTML pattern

Ditempatkan setelah rating TMDB yang sudah ada:

```html
<div class="detail-ratings-row">
    <div class="detail-star-rating">
        <span class="star-icon" aria-hidden="true">&#9733;</span>
        <span class="score-text">{{ $movie['rating'] }}</span>
        <span class="score-label">Rating TMDB</span>
    </div>
    @if ($communityRating && $communityRating->avg_rating)
        <span class="detail-ratings-divider" aria-hidden="true"></span>
        <div class="detail-community-rating">
            <span class="star-icon" aria-hidden="true">&#9733;</span>
            <span class="score-text">{{ $communityRating->avg_rating }}</span>
            <span class="score-label">Komunitas ({{ $communityRating->review_count }} review)</span>
        </div>
    @endif
</div>
```

### Review card pattern (Tab Diskusi)

```html
<article class="detail-review-card">
    <div class="detail-review-header">
        <div class="detail-review-author">
            {{-- avatar --}}
            <div>
                <a href="{{ route('profile.show', $review->user->username) }}">
                    {{ $review->user->name }}
                </a>
                <span class="detail-review-date">{{ $review->created_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="detail-review-meta">
            @if ($review->rating)
                <span class="detail-review-rating">★ {{ $review->rating }}/10</span>
            @endif
        </div>
    </div>
    @if ($review->body)
        @if ($review->has_spoiler)
            <p class="detail-review-spoiler">⚠ Mengandung spoiler</p>
        @endif
        <p class="detail-review-body">{{ Str::limit($review->body, 150) }}</p>
    @endif
    <div class="detail-review-footer">
        <span class="detail-review-likes">♡ {{ $review->likes_count }}</span>
        <span class="detail-review-comments">💬 {{ $review->comments_count }}</span>
        <a href="{{ route('reviews.show', $review) }}" class="detail-review-link">Lihat review penuh →</a>
    </div>
</article>
```

## Data Models

### Data yang sudah ada — tidak berubah

| Model | Relasi yang dipakai |
|---|---|
| `Review` | `user()` (BelongsTo), `likes()` (HasMany), `comments()` (HasMany) |
| `ReviewLike` | — |
| `ReviewComment` | — |

### Cache keys baru

| Key | TTL | Isi |
|---|---|---|
| `movie.community_rating.{tmdbId}` | 3600 detik | Object: `avg_rating`, `review_count` |

## Correctness Properties

### Property 1: Tab param validation
`$tab` selalu salah satu dari `['info', 'diskusi', 'serupa']` — nilai lain di-fallback ke `'info'`.

**Validates: Requirements R1, R6**

### Property 2: Sort param validation
`$sort` selalu salah satu dari `['popular', 'recent']` — nilai lain di-fallback ke `'recent'`.

**Validates: Requirements R3, R6**

### Property 3: Lazy loading community reviews
`$communityReviews` adalah `null` saat `$tab !== 'diskusi'` — view tidak merender paginator jika null.

**Validates: Requirements R6**

### Property 4: Pagination query string preservation
Pagination dengan `withQueryString()` memastikan `?tab=diskusi&sort=popular` tetap terbawa saat ganti halaman.

**Validates: Requirements R1, R3**

### Property 5: Community rating calculation
Community rating hanya dihitung dari review yang memiliki `rating` (`whereNotNull('rating')`).

**Validates: Requirements R5**

## Testing Strategy

Test file: `tests/Feature/MovieDetailTabsTest.php` (dibuat via `php artisan make:test --pest MovieDetailTabsTest`).

Pola mock TMDB: gunakan `Http::fake()` atau mock `MovieService` — ikuti pola dari existing feature tests di proyek.

Test cases:
1. Tab info default — GET tanpa param, assert sinopsis + cast terlihat
2. Tab diskusi menampilkan reviews paginated — buat reviews via factory, assert items + paginator
3. Sort popular — review dengan likes terbanyak muncul pertama
4. Sort recent — review terbaru muncul pertama
5. Tab serupa — assert section film serupa
6. Community rating tampil saat ada reviews berrating
7. Community rating tidak tampil saat belum ada review berrating
8. Guest di tab diskusi melihat prompt login
9. Auth user di tab info melihat form diary dan review

## Error Handling

- Jika TMDB API gagal: `$movieDetail` null, view menampilkan error state — tidak berubah dari perilaku saat ini
- Jika cache `community_rating` gagal: `Cache::remember` akan fallback ke query langsung — tidak perlu handling khusus
- Review dengan `user` yang dihapus: gunakan `with('user')` + pastikan view handle `$review->user` bisa null (optional chaining)
- Pagination page melebihi total: Laravel otomatis menampilkan halaman terakhir

## Notes

- "Tulis Review" di Tab Diskusi mengarah ke `?tab=info#review-form` (anchor ke form di Tab Info). Tambahkan `id="review-form"` pada `<details>` form Review.
- `$recentReviews` lama (limit 5) dihapus sepenuhnya. Data review kini di-handle oleh `$communityReviews`.
- Import `Illuminate\Support\Facades\Cache` perlu ditambahkan di `MovieController`.
- CSS baru ditambahkan di `resources/css/welcome.css` — ikuti pola CSS yang sudah ada (CSS custom properties untuk warna, bukan hardcode).
