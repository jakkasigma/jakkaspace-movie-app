# Requirements Document

## Introduction

Halaman detail film (`/movies/{id}`) saat ini adalah satu halaman panjang tanpa pemisah
yang jelas antara informasi teknis film, aksi pengguna, dan konten komunitas. Redesign ini
memperkenalkan **tab-based layout** agar tiap kategori konten punya ruangnya sendiri,
terutama di mobile.

Perubahan ini merupakan **Tahap 1 dari Phase 7** (Community Film & Redesign). Tidak ada
database schema baru — semua data sudah tersedia, hanya perubahan pada controller dan view.

## Requirements

### R1 — Tab Bar (Info / Diskusi / Serupa)

**User Story:** Sebagai pengunjung halaman film, saya ingin bisa berpindah antar bagian
konten (info teknis, diskusi, film serupa) tanpa harus scroll jauh.

**Acceptance Criteria:**
- [ ] Tab bar dengan tiga tab ditampilkan di bawah hero section: **Info**, **Diskusi**, **Serupa**
- [ ] Tab aktif ditentukan oleh query parameter `?tab=info|diskusi|serupa`
- [ ] Default tab adalah `info` jika parameter tidak ada
- [ ] Tab Diskusi menampilkan badge jumlah total review untuk film tersebut, contoh: `Diskusi (24)`
- [ ] URL bisa di-share dan bookmark (state tab tersimpan di URL)
- [ ] Tab switching tidak memerlukan JavaScript — navigasi via link `?tab=...`

---

### R2 — Tab Info

**User Story:** Sebagai pengunjung, saya ingin melihat semua informasi teknis film di satu
tempat yang bersih tanpa tercampur konten lain.

**Acceptance Criteria:**
- [ ] Tab Info memuat: sinopsis, tagline, pembuat (sutradara + penulis), info film (facts grid), cast grid
- [ ] Form Diary dan form Review (untuk user yang login) ditampilkan di Tab Info
- [ ] User action buttons (Ditonton, Watchlist, Favorit, Sematkan, Tambah ke List) tetap di hero section (selalu visible, bukan di dalam tab)
- [ ] Jika pengguna belum login, prompt login ditampilkan di Tab Info

---

### R3 — Tab Diskusi

**User Story:** Sebagai pengunjung, saya ingin membaca semua review komunitas untuk sebuah
film, dengan kemampuan filter dan pagination.

**Acceptance Criteria:**
- [ ] Tab Diskusi menampilkan **semua** review untuk film tersebut (bukan hanya 5)
- [ ] Pagination diterapkan — default 10 review per halaman
- [ ] Filter tersedia: **Terpopuler** (sort by `likes_count desc`) dan **Terbaru** (sort by `created_at desc`)
- [ ] Filter aktif ditentukan oleh query parameter `?tab=diskusi&sort=popular|recent`
- [ ] Default sort adalah `recent`
- [ ] Setiap review card menampilkan: avatar user, nama user (link ke profil), waktu relatif, rating (jika ada), snippet body 150 karakter, badge spoiler (jika ada), jumlah likes, jumlah komentar, link "Lihat review penuh"
- [ ] Tombol "Tulis Review" (link ke `?tab=info#review-form`) ditampilkan di atas list — hanya untuk user login, guest melihat prompt login
- [ ] Jika belum ada review, tampilkan empty state dengan call-to-action

---

### R4 — Tab Serupa

**User Story:** Sebagai pengunjung yang selesai membaca info film, saya ingin mudah
menemukan film lain yang mirip.

**Acceptance Criteria:**
- [ ] Tab Serupa memuat: film serupa dari TMDB (grid poster)
- [ ] Jika pengguna sudah menonton film ini, tampilkan juga section "Karena kamu menonton film ini" (rekomendasi genre)
- [ ] Jika tidak ada film serupa dari TMDB, tampilkan empty state singkat
- [ ] Komponen `<x-movie.card>` yang sudah ada digunakan kembali

---

### R5 — Hero Section (selalu visible, di atas semua tab)

**User Story:** Sebagai pengunjung, saya ingin melihat informasi kunci film dan avg rating
komunitas tanpa harus masuk ke tab tertentu.

**Acceptance Criteria:**
- [ ] Hero section menampilkan: backdrop full-width, poster, judul + tahun, meta (genre, runtime), rating TMDB, avg rating komunitas, tombol Trailer + Bagikan
- [ ] Avg rating komunitas dihitung dari `avg(rating)` pada tabel `reviews` di-filter `tmdb_id`
- [ ] Avg rating komunitas ditampilkan sebagai `★ X.X Komunitas (N review)` di sebelah rating TMDB
- [ ] Jika belum ada review berrating, section rating komunitas tidak ditampilkan
- [ ] Avg rating di-cache per film dengan TTL 1 jam menggunakan Laravel Cache
- [ ] User action buttons (Ditonton, Watchlist, Favorit, Sematkan, Tambah ke List) tetap di hero section

---

### R6 — Perubahan Controller

**Acceptance Criteria:**
- [ ] `MovieController::show()` membaca parameter `?tab` dan `?sort` dan meneruskannya ke view
- [ ] Untuk tab Diskusi: query review menggunakan pagination (10 per halaman) dan support sort `popular` dan `recent`
- [ ] Untuk tab Diskusi: review di-load beserta `likes_count` dan `comments_count` via `withCount`
- [ ] Avg rating komunitas di-cache dengan key `movie.community_rating.{tmdbId}` TTL 1 jam
- [ ] `$recentReviews` lama (limit 5) diganti dengan `$communityReviews` (paginated, null jika bukan tab diskusi)
- [ ] `$reviewCount` (total review film) selalu di-load untuk badge tab Diskusi

---

### R7 — CSS & Styling

**Acceptance Criteria:**
- [ ] Tab bar di-style mengikuti design language yang sudah ada di project
- [ ] Tab aktif memiliki visual indicator yang jelas (underline atau background)
- [ ] Layout responsif: di mobile tab bar horizontal scroll jika overflow
- [ ] Review card di tab Diskusi konsisten dengan `detail-review-card` yang sudah ada, dengan tambahan `comments_count`
- [ ] Rating komunitas di hero section tidak merusak layout rating TMDB yang sudah ada

---

### R8 — Tests

**Acceptance Criteria:**
- [ ] Feature test memverifikasi tab `info` (default) merender sinopsis dan cast
- [ ] Feature test memverifikasi tab `diskusi` merender reviews dengan pagination
- [ ] Feature test memverifikasi sort `popular` dan `recent` menghasilkan urutan berbeda
- [ ] Feature test memverifikasi tab `serupa` merender section film serupa
- [ ] Feature test memverifikasi avg rating komunitas muncul di hero saat reviews ada
- [ ] Feature test memverifikasi avg rating komunitas tidak muncul saat belum ada review berrating

---

## Glossary

- **Tab Info** — tab default berisi sinopsis, cast, crew, facts, dan form diary/review
- **Tab Diskusi** — tab berisi semua review komunitas dengan filter dan pagination
- **Tab Serupa** — tab berisi film serupa dari TMDB dan rekomendasi genre
- **Community rating** — rata-rata rating dari semua review pengguna di tabel `reviews`
- **Hero section** — bagian atas halaman (backdrop + poster + judul + aksi) yang selalu tampil di semua tab
