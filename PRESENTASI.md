# Skrip Presentasi Jakkaspace — Demo 10 Menit

> Presentasi murni demo — ngelir dari satu langkah ke langkah berikutnya.
> Teks **tebal** = yang diucapkan presenter.

---

## Persiapan Sebelum Mulai

- [ ] Migration running, DB seeded
- [ ] Akun demo login: punya history, diary, review, follow, pinned movies
- [ ] Inbox: ada beberapa percakapan dengan pesan
- [ ] Subscription Plus+ aktif + tema Marvel dipilih
- [ ] Guest window (incognito) siap
- [ ] TMDB API key aktif
- [ ] WebSocket sudah connect (cek browser console)
- [ ] Tab browser siap: guest window (homepage), main window (login + your space)

---

## Demo Langkah demi Langkah

---

### 1. Homepage — Movie Discovery (1 menit)

> Buka guest window.
> Tunjukin homepage.

**"Ini Jakkaspace — platform personal movie diary dan social buat pecinta film. Bukan clone Letterboxd atau IMDb, kita punya identitas sendiri."**

> Scroll hero section, tunjukin trending, popular, now playing, upcoming.

**"Halaman utama nampilin film trending, popular, now playing, upcoming. Semua data dari TMDB API — di-cache biar cepet. Redisain pake Tailwind CSS v4 dengan tema dark cinematic."**

> Klik salah satu film.

**"Halaman detail film: sinopsis, cast, trailer embed, similar movies. Ada tombol aksi buat user yang udah login — nanti kita demo."**

---

### 2. Discover & Search (1 menit)

> Klik Discover atau filter bar.

**"Discover page: filter genre, tahun, rating, sort. URL-nya shareable — lo bisa bookmark filter favorit. Tech stack di belakang: Laravel 12, PHP 8.3, arsitektur 3-layer. Controller tipis — semua business logic di Service layer."**

> Tunjukin search.

**"Search: cari film real-time lewat TMDB."**

---

### 3. Login & Your Space (1 menit)

> Login dengan akun demo.
> Buka `/your-space`.

**"Sekarang kita login. Ini **Your Space** — dashboard personal pengguna. Ada ringkasan statistik: total film ditonton, diary entry, review, genre favorit."**

> Klik tab History, Diary, Watchlist, Favorit.

**"Tab History: riwayat nonton. Tab Diary: catatan personal pas nonton — tanggal, mood, notes. Tab Watchlist: film yang pengen ditonton. Tab Favorit: film favorit."**

---

### 4. Aksi di Detail Film (1 menit)

> Buka detail film yang belum ditonton.

**"Di halaman detail film, user bisa: tandai sudah ditonton, tambah ke watchlist, favorit, tulis diary, atau kasih review."**

> Demo mark as watched.

**"Satu klik — film tercatat di watch history."**

> Demo tulis diary.

**"Bisa tambah catatan pribadi: mood, tanggal nonton, notes."**

> Demo review + rating.

**"Rating 1-10, review teks, spoiler toggle."**

---

### 5. Movie Collection — Custom Lists (1 menit)

> Buka `/your-space/lists`.

**"User bisa bikin custom list — public atau private. Contoh: 'Film Favorit 2024', 'Nobar Sama Temen'."**

> Buka salah satu list.

**"Bisa tambah/hapus film langsung dari halaman detail film. Plus+ user bisa upload cover list custom."**

---

### 6. Profil Publik & Sosial (1 menit)

> Buka `/@username-akun-demo`.

**"Profil publik — Instagram-style. Header: avatar, nama, bio, stats. Ada **pinned movies** — film yang mau dipajang, maksimal 6."**

> Klik tab Reviews, Lists, Favorit.

**"Tab: Film Pilihan, Reviews, Lists, Favorit. Grid poster rapat, hover nunjukkin judul + rating."**

> Scroll ke follow button.

**"User bisa follow/unfollow. Ini fondasi fitur sosial."**

---

### 7. Feed & Interaksi (1 menit)

> Buka `/feed`.

**"Activity Feed: lihat aktivitas orang yang di-follow — nonton film baru, nulis review, nambah ke watchlist. Juga ada **rekomendasi**: 'Karena kamu menonton...' di detail film, personalized di discover, trending di following."**

> Buka halaman review publik.

**"Setiap review bisa di-like dan dikomentari. Interaksi sosial yang sederhana tapi bikin komunitas hidup."**

---

### 8. Inbox — Real-time Chat (1 menit)

> Buka `/inbox`.

**"Ini Inbox — direct messaging. Kirim pesan teks atau share film. Real-time pake **Laravel Reverb** + Echo via WebSocket."**

> Kirim pesan, tunjukin muncul real-time di window lain.

**"Ada unread badge di navbar, date separator, private channel authorization. Pesan masuk instan tanpa refresh."**

---

### 9. Subscription — Plus & Plus+ (1.5 menit)

> Buka `/plus`.

**"Monetisasi: 2-tier subscription."**

| Fitur | Free | Plus Rp15K | Plus+ Rp30K |
|---|---|---|---|
| Movie lists | 1 | 7 | 15 |
| Film/list | 50 | 100 | Unlimited |
| Pinned movies | 6 | 6 | 12 |
| Theme packs | Default | Border + aksen | Ekspresif |
| Export CSV | ❌ | ✅ | Batch all |
| Analytics | ❌ | Streak + rating | + Genre/director |
| Cover list | ❌ | ❌ | ✅ |

> Ganti tema di settings.

**"**Theme packs** — kaya Discord Nitro. Ada Marvel, Ghibli, Cyberpunk, Star Wars, Horror, Retro 80s. Begitu dipilih, langsung kelihatan efeknya: border avatar gradient, username warna aksen, badge di profil, border card review."**

> Tunjukin avatar border, username warna, badge.

---

### 10. Admin Panel & Penutup (30 detik)

> Buka `/admin`.

**"Admin panel: manajemen subscription, redeem codes, promo campaign diskon, CRUD theme, user management. Semua terpusat."**

---

**"**— **Deployment:** Railway — 4 services (Web, Queue, Reverb, Scheduler + MySQL). Build pake Nixpacks, environment variable via dashboard.

**Roadmap selanjutnya — Phase 6:** Bottom nav global, Timeline halaman baru, Search dedicated, Inbox group list. **Phase 7:** Tab-based movie detail, halaman diskusi per film.

Prinsip kami: kualitas > kecepatan, maintainability > shortcut, konsistensi > preferensi pribadi. Jakkaspace dibangun buat jangka panjang.

Terima kasih. Siap menjawab pertanyaan."**

---

## Ringkasan Waktu

| Langkah | Durasi |
|---|---|
| 1. Homepage — Discovery | 1 menit |
| 2. Discover & Search | 1 menit |
| 3. Login & Your Space | 1 menit |
| 4. Aksi Detail Film | 1 menit |
| 5. Custom Lists | 1 menit |
| 6. Profil Publik & Sosial | 1 menit |
| 7. Feed & Interaksi | 1 menit |
| 8. Inbox Real-time Chat | 1 menit |
| 9. Subscription Plus/Plus+ | 1.5 menit |
| 10. Admin Panel & Penutup | 30 detik |
| **Total** | **~10 menit** |

## Catatan

> - **Teks bold** = narasi yang diucapkan presenter
> - Teks biasa = instruksi demo (tidak dibaca)
> - Buka guest window di awal biar transisi discovery → login terasa natural
> - Pastikan akun demo sudah punya data yang cukup biar gak kelihatan kosong
