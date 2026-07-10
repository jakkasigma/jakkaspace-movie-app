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
| 16 | Poster card `w500` → `w185` | ✅ |
| 17 | Backdrop `original` → `w1280` (hero turun 1.5MB→200KB) | ✅ |
| 18 | Detail poster `w500` → `w342` | ✅ |
| 19 | Split intro CSS → async (`intro.css` 4.5KB) | ✅ |
| 20 | Peace Sans → Bebas Neue (hemat ~50KB) | ✅ |
| 21 | Cast photo `w185` → `w92`, lingkaran 80px | ✅ |

## Todo 🟡

| # | Item | Impact | Notes |
|---|------|--------|-------|
| A | Home: kurangi section (sisakan Rekomendasi + Film Baru Rilis) | ⭐⭐ Hemat 67% poster | Guest: 30→10 film, Login: 40→20 film |
| B | Filter mobile collapsible | ⭐ UI lebih rapi | Tombol `⋮ Filter`, klik baru muncul |

---

### A. Home Page — Kurangi Section

**Sekarang:** 3 section (Trending TMDB, Film Baru Rilis, Animasi) + Rekomendasi (login)
**Sesudah:** 1 section (Film Baru Rilis) + Rekomendasi (login)

**File:**
- `app/Services/Movie/MovieService.php` — hapus Trending + Animasi dari `$categories`

**Hemat:**
| | Guest | Login |
|--|-------|-------|
| Poster | 900KB → **300KB** (↓ **67%**) | 1.2MB → **600KB** (↓ **50%**) |
| API call | 3× → **1×** (↓ **67%**) | 3× → **1×** (↓ **67%**) |

### B. Filter Mobile — Collapsible

**Sekarang:** Filter bar selalu kelihatan di mobile.
**Sesudah:** Tombol `⋮ Filter` → klik baru muncul dropdown.

**File:**
- `resources/css/welcome.css` — tambah aturan `.home-filter-wrap.collapsed`
- `resources/views/welcome.blade.php` — tambah toggle + Alpine/JS

## On-hold 🟢

| # | Item | Alasan |
|---|------|--------|
| C | Lora italic 1,400 hapus | Dipake di splash text intro |
| D | Hero `loading="lazy"` | Udah `w1280` 200KB, efek kecil |
| E | Audit/purge welcome.css 10rb baris | ~80% CSS dipake, hemat dikit effort besar |
