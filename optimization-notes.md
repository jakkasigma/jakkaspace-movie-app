# Optimization Notes — Jakka Space

## Done ✅

| # | Item | Status |
|---|------|--------|
| 1 | `CACHE_STORE=file` | ✅ |
| 2 | `SESSION_DRIVER=file` | ✅ |
| 3 | Composite indexes (activity_logs) | ✅ |
| 4 | Fix recursive `getAllReplies()` → flat query | ✅ |
| 5 | Tabel `movies` — kolom caching + `findMovie()` DB-first | ✅ |
| 6 | Cache activity feed 5 menit | ✅ |
| 7 | Redis Railway (CACHE, SESSION, QUEUE) | ✅ |
| 8 | Batch query `Movie::whereIn()` di 6 service | ✅ |
| 9 | Cache stats + diary summary + enriched feed 5 menit | ✅ |
| 10 | Opcache + `artisan optimize` di Dockerfile | ✅ |
| 11 | Home page: 3 section × 10 film | ✅ |
| 12 | Poster card `w500` → `w342` | ✅ |
| 13 | Preconnect TMDB CDN | ✅ |
| 14 | Audio intro `preload=none` | ✅ |
| 15 | Inter font 300 → 400 (hemat ~100KB) | ✅ |

## Todo 🟡

| # | Item | Impact | Notes |
|---|------|--------|-------|
| A | Split intro CSS dari welcome.css | ⭐⭐ First paint lebih cepat | Pisah ke `intro.css`, load async |
| B | Ganti Peace Sans → Bebas Neue | ⭐ Hemat ~50KB | 3 tempat di CSS + hapus cdnfonts |
| C | Hapus Lora italic (1,400) | ⭐ Hemat ~50KB | Jarang kepake |

---

### A. Split Intro CSS

**Tujuan:** Pisah CSS animasi intro (~30KB) dari welcome.css, load async.

**CSS yang dipindah ke `resources/css/intro.css`:**
- `#pre-splash`, `#splash-text`, `#splash-start`
- `#intro-overlay`, `#intro-logo`, `#jakka-word`, `#space-word`, `.space-letter`
- `@keyframes overlayFadeOut`, `barSweep`, `jakkaZoomOut`, `spaceRollIn`, `homeReveal`
- `.anim-started`, `body.intro-complete` rules
- Bagian intro di media queries

**File yang diubah:**
1. Buat `resources/css/intro.css`
2. Edit `resources/css/welcome.css` — hapus CSS intro
3. Edit `resources/views/layouts/movie.blade.php` — load intro.css async
4. Edit `resources/views/layouts/guest.blade.php` — load intro.css async (kalau ada intro)

**Hasil:**
- `welcome.css` 200KB → ~170KB (sync, blocking)
- `intro.css` ~30KB (async, non-blocking)
- First paint lebih cepat

### B. Peace Sans → Bebas Neue

**File:**
- `resources/css/welcome.css:175,343` — ganti `Peace Sans` jadi `Bebas Neue`
- `resources/views/layouts/movie.blade.php:13` — hapus `<link ...peace-sans>`
- `resources/views/layouts/guest.blade.php:11` — hapus `<link ...peace-sans>`

### C. Lora italic

**File:**
- `layouts/movie.blade.php:12` — `1,400` hapus
- `layouts/guest.blade.php:10` — sama
