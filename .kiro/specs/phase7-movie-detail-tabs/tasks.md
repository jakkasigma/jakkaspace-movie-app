# Implementation Plan: Phase 7 Tahap 1 — Redesign Halaman Detail Film

## Overview

Update `MovieController::show()` dan `movies/show.blade.php` untuk tab-based layout
(Info / Diskusi / Serupa), community rating di hero section, dan paginated reviews
di tab Diskusi. Tidak ada migrasi atau model baru.

## Tasks

- [x] 1. Update MovieController::show() untuk tab, sort, community rating, dan paginated reviews
  - Tambah pembacaan query param `$tab` (default `'info'`) dan `$sort` (default `'recent'`)
  - Tambah import `Illuminate\Support\Facades\Cache`
  - Tambah `$reviewCount = Review::where('tmdb_id', $movie)->count()`
  - Tambah `$communityRating` via `Cache::remember("movie.community_rating.{$movie}", 3600, ...)` — hitung `ROUND(AVG(rating), 1)` dan `COUNT(*)` dari reviews berrating (whereNotNull)
  - Tambah `$communityReviews` — paginate(10) dengan withCount(['likes','comments']), sort by likes_count (popular) atau created_at (recent), hanya load saat `$tab === 'diskusi'`, pakai `withQueryString()`
  - Hapus `$recentReviews` (limit 5) lama
  - Kirim semua variabel baru ke view: `$tab`, `$sort`, `$reviewCount`, `$communityRating`, `$communityReviews`

- [x] 2. Restructure heroes section di movies/show.blade.php — tab bar dan community rating
  - Pastikan user-actions-wrap tetap di dalam hero section (bukan di dalam tab)
  - Ganti blok `.detail-score-row` menjadi `.detail-ratings-row` yang menampung rating TMDB + community rating berdampingan
  - Tampilkan community rating `★ X.X Komunitas (N review)` hanya jika `$communityRating->avg_rating` ada
  - Tambah tab bar `<nav class="detail-tab-bar">` di bawah hero section dengan tiga link (Info, Diskusi dengan badge, Serupa) dan class `tab-active` sesuai `$tab`
  - Bungkus seluruh konten tab dalam `<div class="detail-tab-content">`

- [x] 3. Implementasi Tab Info di movies/show.blade.php
  - Buat blok `@if ($tab === 'info')` dengan wrapper `<div class="detail-tab-info">`
  - Pindahkan sinopsis (tagline + overview) ke dalam blok ini
  - Pindahkan section Pembuat (crew: director, writers) ke Tab Info
  - Pindahkan section Info Film (facts grid) ke Tab Info
  - Pindahkan section Cast ke Tab Info
  - Pindahkan `<div class="user-forms">` (form Diary + Review) ke Tab Info, di dalam `@auth ... @else login prompt @endauth`
  - Tambah `id="review-form"` pada `<details>` form Review untuk anchor dari tombol "Tulis Review" di tab Diskusi
  - Hapus referensi `$recentReviews` lama dari view

- [x] 4. Implementasi Tab Diskusi di movies/show.blade.php
  - Buat blok `@elseif ($tab === 'diskusi')` dengan wrapper `<div class="detail-tab-diskusi">`
  - Tambah header diskusi: filter pills (Terbaru / Terpopuler) dengan class `filter-active`, dan tombol "Tulis Review" untuk auth user (link ke `?tab=info#review-form`) atau prompt login untuk guest
  - Loop `@forelse ($communityReviews as $review)` dengan review card: avatar, nama user (link profil), waktu relatif, rating, badge spoiler, snippet body 150 karakter, likes_count, comments_count, link "Lihat review penuh"
  - `@empty`: empty state dengan pesan dan CTA menarik
  - Render paginator `{{ $communityReviews->links() }}`

- [x] 5. Implementasi Tab Serupa di movies/show.blade.php
  - Buat blok `@elseif ($tab === 'serupa')` dengan wrapper `<div class="detail-tab-serupa">`
  - Pindahkan section film serupa (`$similarMovies`) ke Tab Serupa, dengan empty state jika kosong
  - Pindahkan section rekomendasi genre (`$genreRecommendations`) ke Tab Serupa (hanya tampil jika tidak kosong)

- [x] 6. Update CSS di resources/css/welcome.css
  - Tambah `.detail-tab-bar`, `.detail-tab-link`, `.tab-active` dengan indicator aktif (underline) — ikuti CSS custom properties yang sudah ada
  - Tambah media query mobile (max-width 640px) untuk overflow-x scroll pada `.detail-tab-bar`
  - Tambah `.detail-ratings-row`, `.detail-community-rating`, `.detail-ratings-divider` untuk layout rating berdampingan
  - Tambah `.diskusi-filters`, `.filter-active` untuk filter pills di Tab Diskusi
  - Update `.detail-review-footer` untuk menampung likes + komentar + link berdampingan
  - Tambah `.diskusi-empty` untuk empty state Tab Diskusi

- [ ] 7. Buat dan jalankan feature test MovieDetailTabsTest
  - Jalankan `php artisan make:test --pest MovieDetailTabsTest`
  - Tulis test: tab info default — GET tanpa param tab, assert sinopsis terlihat
  - Tulis test: tab diskusi menampilkan reviews — buat reviews via factory, assert review items muncul
  - Tulis test: sort popular — reviews dengan likes terbanyak muncul pertama
  - Tulis test: sort recent — review terbaru muncul pertama
  - Tulis test: tab serupa — assert section film serupa (mock similarMovies)
  - Tulis test: community rating tampil saat ada reviews berrating
  - Tulis test: community rating tidak tampil saat belum ada review berrating
  - Tulis test: guest di tab diskusi melihat prompt login
  - Tulis test: auth user di tab info melihat form diary dan review
  - Jalankan `php artisan test --compact --filter=MovieDetailTabsTest`, pastikan semua pass
  - Jalankan `vendor/bin/pint --dirty --format agent` untuk format semua file PHP yang diubah

## Task Dependency Graph

```json
{
  "waves": [
    { "wave": 1, "tasks": ["1"] },
    { "wave": 2, "tasks": ["2", "6"] },
    { "wave": 3, "tasks": ["3", "4", "5"] },
    { "wave": 4, "tasks": ["7"] }
  ]
}
```

## Notes

- Mock TMDB calls di tests: gunakan pola dari existing tests (Http::fake atau mock MovieService)
- `withQueryString()` pada paginator penting agar `?tab=diskusi&sort=popular` terbawa di semua link pagination
- Validasi `$tab` dan `$sort` cukup di view dengan `$tab === 'info'` check — tidak perlu throw exception untuk nilai tidak valid, fallback ke default
