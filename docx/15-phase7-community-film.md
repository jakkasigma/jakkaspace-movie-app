# Phase 7 — Community Film & Redesign Halaman Detail

> Created: 2026-07-04
> Status: Planning
> Prerequisite: Phase 6 ✅

---

## Visi

Setiap film di Jakkaspace punya "ruang diskusi" sendiri — tempat komunitas bisa review, debat,
dan rekomendasikan film bersama. Timeline jadi cerminan percakapan yang hidup, bukan sekadar
statistik agregat yang kosong.

---

## Perubahan Besar

### 1. Redesign Halaman Detail Film `/movies/{id}`

#### Masalah sekarang
- Satu halaman panjang tanpa tab — informasi teknis (cast, crew, facts) dicampur dengan
  aksi user (diary, review, watchlist) dan konten komunitas (recent reviews).
- Di mobile sangat panjang, tidak nyaman.
- Section review hanya 5 item terbaru, tidak ada cara untuk lihat semua atau filter.

#### Struktur baru — Tab-based layout

```
┌────────────────────────────────────────────────────────────┐
│  HERO: backdrop full-width + gradient overlay              │
│  ┌──────────┐  Judul Film                                  │
│  │          │  2024 · Action, Drama · 2j 15m               │
│  │  Poster  │  ★ 8.7 TMDB  |  ★ 8.1 Komunitas (24 review) │
│  │          │  [ Trailer ] [ Bagikan ]                     │
│  │          │  [ ✓ Ditonton ] [ 🔖 Watchlist ] [ ♡ Favorit ] │
│  └──────────┘  [ 📌 Sematkan ] [ 📋 Tambah ke List ]      │
└────────────────────────────────────────────────────────────┘

[ Info ] [ Diskusi (24) ] [ Serupa ]
  ^tab       ^tab            ^tab
```

**Tab: Info** (default)
- Sinopsis + tagline
- Pembuat (sutradara, penulis)
- Info film (rating usia, negara, studio, runtime, dll)
- Cast grid
- Form Diary & Review (untuk user login)

**Tab: Diskusi**
- Statistik komunitas: rata-rata rating, total review, total yang menonton
- Filter: Terpopuler / Terbaru
- List semua review + komentar (preview 2 baris, expand ke `/reviews/{id}`)
- Tombol "Tulis Review" di atas

**Tab: Serupa**
- Film serupa dari TMDB
- Rekomendasi genre (kalau sudah ditonton)

#### Keuntungan
- Info teknis tidak tertimbun di bawah aksi user
- Diskusi komunitas punya ruang sendiri yang proper
- Mobile lebih nyaman — tiap tab pendek
- Mudah dikembangkan (misal tab "Daftar" untuk show lists yang punya film ini)

---

### 2. Halaman Diskusi Film `/movies/{id}/community`

Halaman dedicated untuk diskusi komunitas tentang satu film.
Ini redirect-target dari tombol "Lihat semua diskusi" di halaman film.

**Layout:**
```
┌─────────────────────────────────────────────────────────┐
│  [Poster kecil]  Judul Film (2024)                      │
│  ★ 7.8 TMDB  |  ★ 8.2 avg komunitas  |  24 review      │
└─────────────────────────────────────────────────────────┘

[ Terpopuler | Terbaru ]  ← filter

[ + Tulis Review ]  ← hanya kalau login

┌──────────────────────────────────────────────────────┐
│ [Avatar] Nama User                        ★ 9/10     │
│ 2 jam lalu                                           │
│ "Ini salah satu film terbaik yang pernah aku..."     │
│ ♡ 24 likes  💬 8 komentar    [Lihat review penuh →] │
└──────────────────────────────────────────────────────┘
(dst, paginated)
```

**URL:** `/movies/{id}/community`
**Auth:** Publik (baca), Login (tulis review/komentar)

---

### 3. Revisi Timeline — Tab "Semua"

#### Sekarang (masalah)
- "Review Terpopuler 7 Hari" → card kaku, tidak ada konteks film
- "Paling Banyak Diulas" → row poster tanpa teks, tidak menarik
- Kalau DB kosong, semua section hilang → timeline kelihatan mati

#### Sesudah
```
Tab Semua:
1. 🔥 Trending TMDB                          ← tetap
2. 💬 Review Terbaru Komunitas               ← BARU (feed vertikal)
   - Poster film + judul + nama user + rating + snippet review
   - Link ke /reviews/{id}
3. 🗣️ Diskusi Terpanas                       ← BARU
   - Review dengan komentar terbanyak minggu ini
   - Tampil sebagai card film + badge "N komentar"
4. 📋 Paling Banyak Masuk Watchlist          ← tetap
```

**"Review Terbaru Komunitas"** — ini yang jadi tulang punggung timeline.
Diambil dari tabel `reviews` order by `created_at desc`, limit 6.
Cache 5 menit (data cepat berubah).

---

### 4. Revisi Timeline — Tab "Trending"

```
Tab Trending:
1. 🔥 Film Trending TMDB                     ← tetap
2. 🏆 Film Paling Banyak Diulas              ← ganti nama + perbaiki display
   - Sekarang: row poster biasa
   - Nanti: poster + badge "N review minggu ini" + avg rating komunitas
3. ⭐ Review Paling Viral                    ← BARU
   - Review dengan (likes + komentar) terbanyak minggu ini
   - Tampil lebih besar dari card biasa
4. 📓 Paling Banyak Dicatat di Diary         ← tetap
```

---

## File yang Berubah

### BARU
```
app/Http/Controllers/MovieCommunityController.php
resources/views/movies/community.blade.php
resources/views/movies/show-new.blade.php  (atau update show.blade.php langsung)
```

### DIUPDATE
```
app/Services/User/TimelineService.php
  + latestCommunityReviews(int $limit): Collection
  + hottestDiscussions(int $limit): Collection
  + getAllSections() — tambah dua section baru
  + getTrendingSections() — revisi section viral

resources/views/timeline/index.blade.php
  - Tab Semua: tambah section review terbaru + diskusi terpanas
  - Tab Trending: tambah review viral

resources/views/movies/show.blade.php
  - Tambah tab bar (Info / Diskusi / Serupa)
  - Pindah cast/crew/facts ke tab Info
  - Pindah recent reviews ke tab Diskusi
  - Simpan hero + aksi user di atas tab

resources/css/welcome.css
  + CSS tab film detail
  + CSS community page
  + CSS review feed item (untuk timeline)

routes/web.php
  + Route::get('/movies/{movie}/community', ...)
```

### TIDAK BERUBAH
```
Database schema        — tidak ada migrasi baru
ReviewController       — tidak perlu diubah
ReviewLikeController   — tidak perlu diubah
ReviewCommentController — tidak perlu diubah
Review model           — tidak perlu diubah
```

---

## Urutan Pengerjaan

### Tahap 1 — Redesign halaman detail film
```
1. Update movies/show.blade.php — tambah tab bar (Info, Diskusi, Serupa)
2. Pindahkan cast/crew/facts ke dalam tab Info
3. Pindahkan similar movies ke tab Serupa
4. Buat tab Diskusi — tampilkan review dengan filter + pagination
5. Update CSS detail film — tab bar + layout baru
6. Tests
```

### Tahap 2 — Halaman Community per film
```
7. MovieCommunityController + route /movies/{id}/community
8. View movies/community.blade.php
9. CSS community page
10. Tambah tombol "Lihat semua diskusi" di halaman film
11. Tests
```

### Tahap 3 — Update Timeline
```
12. TimelineService: tambah latestCommunityReviews() + hottestDiscussions()
13. Update getAllSections() — feed review terbaru + diskusi terpanas
14. Update getTrendingSections() — review viral
15. Update timeline/index.blade.php — tampilkan section baru
16. CSS review feed item untuk timeline
17. Tests
```

---

## Catatan Arsitektur

### Tab detail film
- Tab switching via `?tab=info|diskusi|serupa` (URL-shareable, SEO-friendly)
- Default: `info`
- Tab Diskusi menampilkan semua review film (bukan hanya 5) dengan pagination
- Filter Terpopuler/Terbaru via query param `?tab=diskusi&sort=popular|recent`

### Timeline feed review
- Bukan real-time — cache 5 menit
- Review ditampilkan sebagai mini-card: poster + user + snippet + rating
- Klik → ke `/reviews/{id}` (halaman review detail yang sudah ada)

### Community page
- Sama dengan tab Diskusi, tapi standalone page
- Berguna untuk share URL langsung ke diskusi film
- Bisa dijadikan entrypoint dari notifikasi ("X commented on your review of [Film]")

### Avg rating komunitas
- Dihitung dari `reviews` table: `avg(rating)` per `tmdb_id`
- Cache 1 jam per film
- Ditampilkan di header film berdampingan dengan rating TMDB

---

## Mockup Layout Desktop — Halaman Detail Film (Baru)

```
╔═══════════════════════════════════════════════════════════════╗
║  [← KEMBALI]                                                  ║
║                                                               ║
║  ████████████████████████████████ BACKDROP ████████████████  ║
║  ████████████████████████████████████████████████████████    ║
║                                              ▓▓▓▓▓▓▓▓▓▓▓▓   ║
║  ┌─────────┐  JUDUL FILM DALAM HURUF BESAR  ▓            ▓   ║
║  │         │  2024 · Action, Thriller · 2j  ▓   POSTER   ▓   ║
║  │ POSTER  │  ★ 8.7 TMDB  ★ 8.1 Komunitas ▓            ▓   ║
║  │         │                               ▓▓▓▓▓▓▓▓▓▓▓▓   ║
║  └─────────┘  [▶ Trailer] [Bagikan]                         ║
║               [✓ Ditonton][🔖 Watchlist][♡ Favorit]...     ║
║                                                               ║
╠═══════════════════════════════════════════════════════════════╣
║  [  INFO  ]  [  DISKUSI (24)  ]  [  SERUPA  ]               ║
╠═══════════════════════════════════════════════════════════════╣
║                                                               ║
║  (konten tab yang dipilih)                                    ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝
```

## Mockup Layout Mobile — Halaman Detail Film (Baru)

```
┌─────────────────────────┐
│ BACKDROP                │
│ ┌───────┐ JUDUL FILM   │
│ │       │ 2024 · Action│
│ │POSTER │ ★8.7  ★8.1  │
│ │       │ [Trailer]    │
│ └───────┘ [✓][🔖][♡]  │
├─────────────────────────┤
│[Info][Diskusi(24)][Serupa]
├─────────────────────────┤
│ (konten tab)            │
│ ...                     │
└─────────────────────────┘
```

---
