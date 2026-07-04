# Phase 6 — Big Redesign: Workflow

> Created: 2026-07-03
> Status: Planning
> Prerequisite: Phase 5 ✅

---

## Visi

Jakkaspace berevolusi dari personal movie diary menjadi **platform sosial film** — lebih hidup, lebih komunitas, dengan navigasi yang jelas seperti Instagram.

---

## Perubahan Besar

### 1. Navigasi Mobile — Bottom Nav 5 Item

Ganti space tab-bar dan hamburger menjadi satu **bottom nav global** yang ada di semua halaman:

```
[ 🏠 ] [ 🔍 ] [ 📋 ] [ 💬 ] [ 👤 ]
 Home  Search Timeline  Inbox  Profil
```

| Tab | Route | Auth |
|---|---|---|
| 🏠 Home | `/` | Publik |
| 🔍 Search | `/search` | Publik |
| 📋 Timeline | `/timeline` | Publik |
| 💬 Inbox | `/inbox` | Login |
| 👤 Profil | `/@username` atau `/profile` | Login |

**Desktop navbar** tetap ada di atas tapi disederhanakan:
```
JAKKA SPACE  [search input]  TIMELINE  [🔔 notif]  [avatar dropdown]
```
HOME dan DISCOVER tidak perlu di navbar — logo = link ke home.

---

### 2. Home + Discover Digabung

**URL:** `/` (home tetap, discover dihapus)

Halaman ini punya:
- Animasi intro + hero tetap ada
- Search bar di atas sections
- Section "Untukmu" (personalized) — kalau sudah login dan ada history
- Section Trending, Film Baru, Indonesia, dll — seperti sekarang
- Filter genre/tahun/sort di bawah search — kalau filter aktif, tampilkan grid hasil filter
- Pagination tetap ada

**Yang dihapus:** `/discover` route (redirect ke `/`)
**Yang dipertahankan:** semua section dan filter logic

---

### 3. Timeline — Halaman Baru `/timeline`

Halaman publik dengan 3 tab:

#### Tab: Semua (default)
Campuran trending global + aktivitas komunitas Jakkaspace:
- Film trending TMDB minggu ini (dengan card film + badge "🔥 Trending #N")
- Review terpopuler minggu ini (dengan preview teks + foto user)
- Film paling banyak ditambahkan ke watchlist 7 hari ini
- Film paling banyak di-review 7 hari ini

#### Tab: Trending
Murni trending — hanya film dari TMDB trending + data komunitas Jakkaspace:
- Film trending TMDB
- Review dengan likes terbanyak minggu ini
- Film dengan jumlah diary terbanyak minggu ini

#### Tab: Following
Menggantikan `/feed` yang sekarang — aktivitas teman yang difollow.

Tipe aktivitas yang masuk (lebih lengkap dari feed sekarang):
- Menonton film baru
- Menulis review
- Menambah ke watchlist
- Menandai sebagai favorit
- Membuat list baru
- Menambahkan film ke list
- Menyematkan film ke profil (pinned)
- Follow user baru

**Yang dihapus:** `/feed` route (redirect ke `/timeline?tab=following`)

---

### 4. Search — Halaman Baru `/search`

Satu halaman untuk cari semua:

**Tab: Film**
- Cari film berdasarkan judul (sudah ada, pindahkan dari navbar)
- Tampilkan sebagai grid poster

**Tab: User**
- Cari user berdasarkan username atau nama
- Tampilkan avatar + nama + @username + jumlah followers + tombol Follow

**Tab: List**
- Cari list publik berdasarkan nama
- Tampilkan nama list + owner + jumlah film

---

### 5. Inbox — Halaman Baru `/inbox`

Dua jenis conversation dalam satu inbox:

#### Direct Message (1-1)
- Kirim pesan teks ke teman
- Bisa share film (kirim poster + judul + link)
- Bisa share review
- Tidak real-time dulu (refresh untuk lihat pesan baru)

#### Group List (kolaboratif)
List film yang sudah ada diubah menjadi bisa **kolaboratif**:
- Owner bisa invite anggota ke list
- Semua anggota bisa tambah/hapus film
- Ada chat di dalam group list
- Group list muncul di Inbox sebagai conversation

**Tabel baru yang dibutuhkan:**
```
conversations
  - id, type (direct/group_list), created_by, created_at

conversation_members
  - conversation_id, user_id, joined_at

messages
  - id, conversation_id, user_id, type (text/film_share), body, tmdb_id, created_at

movie_list_members (baru — extend movie_lists)
  - movie_list_id, user_id, role (owner/member), joined_at
```

**Update tabel `movie_lists`:**
- Tambah `is_collaborative` boolean
- Kalau `is_collaborative = true`, otomatis punya conversation

---

## Urutan Pengerjaan

### Tahap 1 — Search Page (paling independen)
```
1. SearchController + route /search
2. View search/index.blade.php (tab Film, User, List)
3. UserSearch di ProfileService atau SearchService baru
4. CSS search page
5. Tests
```

### Tahap 2 — Timeline Page
```
6. TimelineService (trending section, community section, following section)
7. TimelineController + route /timeline
8. View timeline/index.blade.php (3 tab)
9. Enrich ActivityFeedService dengan tipe aktivitas baru (pinned, list_movie_add)
10. CSS timeline + card komunitas
11. Tests
```

### Tahap 3 — Home + Discover Merge
```
12. Update MovieController@index — gabungkan filter logic dari Discover
13. Update view welcome.blade.php — tambah filter bar + personalized section
14. Redirect /discover → /
15. Update navbar — hapus DISCOVER link
16. Tests
```

### Tahap 4 — Bottom Nav Mobile
```
17. Buat component resources/views/components/bottom-nav.blade.php
18. Tambahkan ke movie.blade.php layout
19. Update CSS — ganti space-tab-bar dengan global bottom nav
20. Hapus space-tab-bar dari semua space views
```

### Tahap 5 — Inbox (paling kompleks, terakhir)
```
21. Migrasi: conversations, conversation_members, messages, movie_list_members
22. Model: Conversation, Message, MovieListMember
23. Update MovieList model — tambah is_collaborative + members relation
24. InboxService — get conversations, send message, get messages
25. InboxController + routes
26. Update MovieListController — tambah invite member
27. View inbox/index.blade.php + inbox/show.blade.php
28. CSS inbox/chat
29. Tests
```

---

## Catatan Arsitektur

### Search
- Film: pakai TMDB `/search/movie` yang sudah ada
- User: query `users` table by `username` LIKE atau `name` LIKE
- List: query `movie_lists` where `is_public = true` by `name` LIKE

### Timeline
- **Semua & Trending**: data di-cache 1 jam — query berat tapi jarang berubah
- **Following**: sama dengan feed sekarang, cache lebih pendek 5 menit
- Tab switching via `?tab=all|trending|following` — URL shareable

### Inbox
- **Tidak real-time dulu** — polling manual (refresh) atau auto-refresh tiap 30 detik via `<meta refresh>` atau simple JS
- **Real-time nanti** via Laravel Reverb (WebSocket) di Phase 7 kalau diputuskan
- **Group list conversation** otomatis terbuat saat `is_collaborative` diaktifkan

### Bottom Nav
- Tampil di semua halaman yang pakai `layouts/movie.blade.php`
- Gantikan `space-tab-bar` yang sekarang hanya ada di space pages
- Profile icon: kalau login tampil avatar user, kalau belum tampil icon user biasa → link ke login

### Feed → Timeline
- Route `/feed` di-keep tapi redirect ke `/timeline?tab=following`
- `ActivityFeedController` tetap ada, cukup redirect
- `ActivityFeedService::getFeed()` dipanggil oleh `TimelineService`

## Konfirmasi & Keputusan

### Asumsi default yang akan dipakai saat eksekusi:
1. `/discover` → redirect ke `/`, tidak dihapus total (backward compatible)
2. Bottom nav hanya di halaman utama app (bukan auth pages)
3. Inbox — mulai dari **DM dulu**, Group List menyusul

---
