# Script Presentasi Jakkaspace — Demo Fitur (5-10 menit)

## Slide 1: Pembuka (~30 detik)

**Narasi:**

> Assalamualaikum / Selamat pagi, perkenalkan saya **[Nama]**.
>
> Projek yang saya buat adalah **Jakkaspace**, sebuah platform sosial untuk para penggemar film — seperti Letterboxd, tapi dibangun dari nol dengan Laravel.
>
> Di Jakkaspace, pengguna bisa mencatat film yang ditonton, memberi rating dan review, membuat watchlist, berinteraksi dengan pengguna lain, hingga membuat daftar film kolaboratif dengan chat real-time.

---

## Slide 2: Tech Stack (~30 detik)

**Narasi:**

> Teknologi yang digunakan:
>
> - **Backend**: Laravel 12 (PHP 8.3)
> - **Database**: MySQL
> - **Frontend**: Blade + Alpine.js 3 + Tailwind CSS 4
> - **Real-time**: Laravel Reverb + Echo (WebSocket)
> - **Data Film**: TMDB API (The Movie Database)
> - **Auth**: Laravel Breeze + Google OAuth (Socialite)
> - **Testing**: Pest PHP

---

## Slide 3: Demo — Registrasi & Profil (~1 menit)

**Aksi:**

1. Buka halaman utama `/`
2. Klik "Register" → isi form (nama, username, email, password)
3. Atau klik "Login with Google" (demo OAuth)
4. Setelah login, buka halaman `/settings` → edit bio, upload avatar

**Narasi:**

> User bisa daftar secara manual atau langsung pakai Google OAuth.
> Setelah login, setiap user punya halaman profil pribadi di `@{username}` yang bisa diakses publik.
> Di sini bisa edit bio, upload avatar dengan cropping, dan ganti password.

---

## Slide 4: Demo — Cari & Jelajah Film (~1 menit)

**Aksi:**

1. Buka `/search` → ketik judul film (misal "Inception") → enter
2. Lihat hasil dari TMDB
3. Klik salah satu film → lihat detail (poster, sinopsis, rating TMDB, genre, tahun)
4. Buka `/discover` → lihat trending/populer
5. Filter berdasarkan genre

**Narasi:**

> Data film diambil secara real-time dari TMDB API dan di-cache di database.
> Movie catalog ini jadi pusat dari semua fitur — setiap interaksi user selalu merujuk ke TMDB ID yang sama.

---

## Slide 5: Demo — Diary, Review, Watchlist (~1.5 menit)

**Aksi:**

1. Di halaman detail film, klik "Tandai sudah ditonton" (✔)
2. Atau klik "Add to Diary" → pilih tanggal, tulis catatan, mood, centang rewatch
3. Buka `/diary` → lihat semua catatan nonton (kalender/list view)
4. Kembali ke film → klik "Write Review" → beri rating 1-10 + teks + spoiler tag → submit
5. Toggle spoiler → lihat review disembunyikan
6. Klik "Add to Watchlist" → centang ikon bookmark
7. Klik "Add to Favorites" → bintang
8. Buka `/space` → lihat tab Diary, Watchlist, Favorites, History

**Narasi:**

> Fitur inti dari aplikasi ini adalah pencatatan aktivitas nonton.
> **Diary** mencatat kapan dan bagaimana user menonton film.
> **Review** bisa diberi rating 1-10 dan teks, serta bisa di-tandai sebagai spoiler yang otomatis disembunyikan.
> **Watchlist** untuk film yang ingin ditonton, **Favorites** untuk favorit.
> Semua ini bisa diakses dari halaman "Your Space" dan juga dari profil publik.

---

## Slide 6: Demo — Sosial (Follow & Timeline) (~1 menit)

**Aksi:**

1. Cari user lain via `/search?type=users`
2. Buka profil user lain
3. Klik "Follow"
4. Buka `/home` → lihat Timeline (aktivitas dari user yang diikuti)
5. Klik tab "Discover" → lihat semua aktivitas global
6. Di timeline, klik salah satu review → lihat komentar/like
7. Klik "Like" review

**Narasi:**

> Fitur sosial membuat aplikasi ini hidup.
> User bisa follow pengguna lain, dan aktivitas mereka — seperti menulis review, menambah diary, atau menambahkan film ke favorites — muncul di Timeline.
> Setiap review bisa di-like dan dikomentari, bahkan dengan nested comments.
> Semua interaksi ini menghasilkan notifikasi yang bisa dilihat di bell icon pojok kanan atas.

---

## Slide 7: Demo — Movie Lists & Real-time Chat (~1.5 menit)

**Aksi:**

1. Buka `/lists` → klik "Buat List Baru"
2. Isi nama, deskripsi, set publik/privat → submit
3. Tambah film ke list (search & add)
4. Klik "Bagikan" → copy link/kode undangan
5. Buka tab lain / user lain → buka link list
6. Klik "Join List" (via kode)
7. Buka tab "Chat" di dalam list → ketik pesan → lihat real-time di kedua tab

**Narasi:**

> Fitur unggulan adalah **Movie Lists** — koleksi film tematik yang bisa dibuat kolaboratif.
> User bisa membuat list (misal "Film Horor Terbaik 2024"), mengundang teman via link atau kode unik, dan member bisa menambahkan film.
> Setiap list memiliki **real-time chat** yang menggunakan Laravel Reverb + WebSocket — pesan terkirim dan muncul secara instan tanpa reload halaman.
> Member memiliki role: owner, admin, dan member.

---

## Slide 8: Demo — Notifikasi Real-time (30 detik)

**Aksi:**

1. Minta audiens lihat bell icon
2. Dari akun lain, like review atau kirim komentar
3. Notifikasi muncul real-time (counter bell, dropdown)

**Narasi:**

> Semua interaksi — like, komentar, follow, undangan list — mengirim notifikasi real-time via WebSocket.
> Ada 8 tipe notifikasi: `ReviewLiked`, `ReviewCommented`, `DiaryLiked`, `NewFollower`, `ListInvitation`, `ListJoinRequest`, `MentionedInComment`, `SubscriptionExpiring`.

---

## Slide 9: Demo — Subscription (User Side) (~30 detik)

**Aksi:**

1. Buka `/settings/subscription` → lihat paket Plus & Plus+
2. Klik "Subscribe" → pilih durasi (30/90/180/365 hari) → checkout simulasi
3. Lihat fitur yang ter-unlock

**Narasi:**

> Jakkaspace punya sistem **subscription berbayar** dengan dua tier:
> - **Plus**: batasan fitur lebih longgar
> - **Plus+**: unlock semua fitur (12 pinned movies, cover photo list, export data)
>
> User bisa memilih durasi dan melakukan checkout simulasi. Ini masih menggunakan simulasi — ke depannya akan diintegrasikan dengan payment gateway riil.

---

## Slide 10: Demo — Admin Panel (2 menit)

### 10a — Dashboard (~15 detik)

**Aksi:**

1. Buka `/admin` (login sebagai admin)
2. Lihat kartu statistik: total users, subscriptions aktif, revenue simulasi

**Narasi:**

> Admin panel diakses via `/admin`. Dashboard menampilkan ringkasan platform: jumlah user, subscription aktif, dan pendapatan simulasi.

### 10b — Users (~20 detik)

**Aksi:**

1. Klik menu "Users"
2. Lihat tabel: name, email, subscription tier, status banned
3. Cari user → klik "Ban" → konfirmasi → user terban
4. Klik "Unban" → user aktif kembali

**Narasi:**

> **User Management** menampilkan semua pengguna terdaftar. Admin bisa melakukan ban/unban jika ada user yang melanggar aturan. User yang di-ban tidak bisa login sampai di-unban.

### 10c — Plans (~30 detik)

**Aksi:**

1. Klik menu "Plans"
2. Lihat daftar paket: Plus 30/90/180/365 hari, Plus+ 30/90/180/365 hari
3. Klik "Edit" pada salah satu paket → ubah harga/nama → save
4. Seret (drag) untuk reorder → urutan berubah
5. Klik toggle "Active" → plan nonaktif, tidak muncul di halaman subscribe

**Narasi:**

> **Plan Management** memungkinkan admin mengelola paket subscription secara penuh: menambah paket baru (misal trial 7 hari), mengubah harga, drag-and-drop reorder, dan menonaktifkan paket tanpa menghapusnya — berguna saat seasonal promotion.

### 10d — Subscriptions (~25 detik)

**Aksi:**

1. Klik menu "Subscriptions"
2. Lihat list user + tier + expiry date
3. Klik tab "Transactions" → lihat history transaksi (user, plan, amount, status)
4. Klik "Grant" → pilih user + plan + notes → submit → akses langsung aktif
5. Cari user → "Extend" → tambah durasi 30 hari
6. Atau "Cancel" → subscription berakhir

**Narasi:**

> **Subscription Management** menampilkan semua user yang sedang berlangganan dan riwayat transaksi. Admin bisa memberikan akses gratis (grant), memperpanjang (extend), atau membatalkan (cancel) subscription — sangat berguna untuk customer support atau kompensasi.

### 10e — Themes (~20 detik)

**Aksi:**

1. Klik menu "Themes"
2. Lihat daftar tema yang tersedia
3. Klik "Create" → isi nama, avatar border CSS, accent color, badge icon → save
4. Lihat tema baru muncul di daftar

**Narasi:**

> **Themes** memungkinkan admin membuat opsi kustomisasi tampilan profil. Setiap tema bisa mengatur: border avatar (CSS), warna accent (hex), dan ikon badge. User nantinya bisa memilih tema ini di halaman settings profil mereka.

### 10f — Promo & Redeem Codes (~20 detik)

**Aksi:**

1. Klik menu "Promo & Redeem"
2. Tab "Promo" → klik "Buat Promo" → isi kode, diskon %/nominal, target plan, masa berlaku → save
3. Tab "Redeem Codes" → klik "Buat" → isi kode, target plan, durasi akses, max uses → save
4. Lihat daftar kode yang aktif

**Narasi:**

> Ada dua jenis kode:
> - **Promo Code**: memberikan diskon persen atau nominal saat user checkout subscription.
> - **Redeem Code**: memberikan akses langsung ke plan tanpa pembayaran — cocok untuk giveaway, kolaborasi reviewer film, atau beta tester.
>
> Admin bisa mengaktifkan/menonaktifkan kode kapan saja dan melihat statistik pemakaian.

**Narasi Keseluruhan Admin:**

> Admin panel ini memberikan kontrol penuh atas operasional platform tanpa perlu menyentuh database langsung. Semua dikelola lewat UI — dari manajemen user, subscription, paket, hingga tema dan promo.

---

## Slide 11: Arsitektur & Highlight Teknis (1 menit)

**Narasi:**

> Dari sisi arsitektur, beberapa hal yang saya banggakan:
>
> **1. Service Layer Pattern** — Semua logic bisnis dipisah ke service class di `app/Services/User/` dan `app/Services/Movie/`, bukan di controller. Ada 14 service class untuk user, 3 untuk movie.
>
> **2. TMDB API Integration + Caching** — Data film di-cache di tabel `movies` untuk mengurangi request API dan mempercepat loading.
>
> **3. Broadcasting Real-time** — Laravel Reverb untuk chat list dan notifikasi. Menggunakan private & presence channels dengan otorisasi.
>
> **4. Queue untuk Notifikasi** — Semua notifikasi di-queue via database driver, tidak blocking response.
>
> **5. Database Design** — 45 migration, tabel denormalized `activity_logs` untuk performa timeline.
>
> **6. Authorization** — Policy (UserPolicy), middleware (CheckAdmin, CheckBanned), dan gate-based permission per fitur.

---

## Slide 12: Penutup (~30 detik)

**Narasi:**

> Sekian demo dari saya. Beberapa rencana pengembangan ke depan:
> - Integrasi payment gateway riil (Midtrans/Xendit)
> - Mobile app (React Native / Flutter)
> - Rekomendasi film berbasis AI
> - Deployment ke production (Laravel Cloud / Railway)
>
> Terima kasih. Silakan bertanya.

---

## Lampiran: Urutan Fitur per Scene Demo

| Urutan | Halaman | Aksi |
|--------|---------|------|
| 1 | `/register` | Register user baru |
| 2 | `/settings` | Edit profil + avatar |
| 3 | `/search?q=inception` | Cari film dari TMDB |
| 4 | `/movies/{tmdb_id}` | Detail film → Diary, Review, Watchlist, Fav |
| 5 | `/diary` | Lihat catatan nonton |
| 6 | `/space` | Dashboard personal |
| 7 | `/search?type=users` | Cari user → Follow |
| 8 | `/home` | Timeline + Discover |
| 9 | `/lists/create` | Buat movie list |
| 10 | `/lists/{id}` | Join + chat real-time |
| 11 | `/notifications` | Notifikasi masuk |
| 12 | `/settings/subscription` | Subscribe Plus |
| 13 | `/admin` | Admin Dashboard |
| 14 | `/admin/users` | User Management — ban/unban |
| 15 | `/admin/plans` | Plan CRUD — edit, reorder, toggle |
| 16 | `/admin/subscriptions` | Subscription list + grant/cancel/extend |
| 17 | `/admin/subscriptions/transactions` | Transaction history |
| 18 | `/admin/themes` | Theme CRUD |
| 19 | `/admin/promo-redeem` | Promo & Redeem Codes |

---

## Catatan Persiapan Demo

**Sebelum demo:**
1. Siapkan **2 akun**: 1 user biasa + 1 admin (`is_admin = true`)
2. Pastikan ada beberapa data: minimal 2-3 user, beberapa review, diary, list
3. Jalankan `php artisan queue:work` di terminal terpisah agar notifikasi terkirim
4. Jalankan `php artisan reverb:start` jika demo real-time chat
5. Buka 2 browser/tab untuk demo real-time (chat + notifikasi)
6. Siapkan halaman admin sudah login sebelumnya agar tidak buang waktu

**Tips demo:**
- Urutkan dari user flow (registrasi → cari film → interaksi) baru admin
- Untuk demo real-time, buka 2 window berdampingan agar visible
- Jika TMDB API lambat, siapkan beberapa film yang sudah di-cache
