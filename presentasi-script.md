# Presentasi — Fitur Baru Jakka Space

---

## 1. Tombol Bagikan 3-in-1

**Lokasi:** Halaman detail film → Hero section

**Cara:**
- Buka film apa saja
- Klik tombol **"Bagikan"** di samping tombol Trailer
- Muncul modal popup dengan layout kiri-kanan (poster template + pilihan)

**Ada 4 pilihan:**
| Pilihan | Fungsi |
|---------|--------|
| **👤 Ke User** | Kirim film lewat pesan langsung ke teman |
| **📋 Ke List** | Kirim film ke list chat grup |
| **📸 Story Instagram** | Buat template poster keren, bagikan ke IG/WA/Telegram |
| **🔗 Salin Tautan** | Copy link film |

**Demo:** Buka film → klik Bagikan → tunjukin modal → klik masing-masing opsi

---

## 2. Poster Template untuk Story

**Lokasi:** Modal Bagikan → tab Story & Simpan → [Story Instagram]

**Tampilan template:**
- Backdrop film dengan efek blur + gradient gelap
- Poster film di tengah dengan shadow
- Judul, tahun, rating TMDB
- Watermark "FILM DIARY BY JAKKA SPACE"

**Hasil:** Canvas 1080×1920 → klik **Story Instagram** → otomatis buka native share → bisa kirim ke Instagram Story, WhatsApp, Telegram, dll. Atau klik **Unduh PNG** → langsung download gambarnya.

**Demo:**
1. Klik Bagikan → pilih tab "Story & Simpan"
2. Klik "Story Instagram"
3. Tunjukin hasil canvas + native share sheet

---

## 3. Inbox DM — Chat Real-time

**Lokasi:** `/inbox`

**Fitur:**
- Kirim pesan teks ke sesama user
- Share film langsung dari detail film (via Bagikan → Ke User)
- Avatar tampil untuk pesan dari orang lain
- Film_share card dengan poster + judul + tahun
- Real-time via Laravel Reverb (WebSocket)
- Unread badge di navbar

**Demo:**
1. Login akun A → buka inbox
2. Buka chat room → tunjukin bubble chat + avatar
3. Tunjukin film_share card (poster, judul, tahun)

---

## 4. List Chat — Kolaborasi Film

**Lokasi:** Halaman list film → tab Chat

**Fitur:**
- Chat grup dalam list film
- Share film ke list via Bagikan → Ke List
- Film_share card sama persis dengan inbox DM (poster 52×78px + rating)
- **Avatar tampil untuk SEMUA pesan** (punya sendiri maupun orang lain) — berbeda dengan inbox DM
- Nama pengirim tampil di setiap pesan

**Demo:**
1. Buka list film → klik Chat
2. Tunjukin avatar di setiap bubble
3. Tunjukin film_share card (poster besar + rating)

---

## 5. Google Linking Popup

**Lokasi:** `/your-space` — setelah daftar manual

**Fitur:**
- Saat user daftar manual (pakai email + password), pas masuk `/your-space` muncul modal "Hubungkan Akun Google"
- Klik tombol "Hubungkan Google" → OAuth → selesai
- Setelah link, user bisa login pakai Google ATAU password
- Ada opsi "Nanti" (hilang sementara) dan "Jangan tampilkan lagi" (permanent dismiss)

**Demo:**
1. Daftar pake email baru (manual)
2. Masuk /your-space → tunjukin modal hubungkan Google
3. Klik Hubungkan → Google OAuth → berhasil
4. Tunjukin Google login (logout → login Google → langsung masuk)

---

## 6. Daftar & Login — Tanpa Hambatan Verifikasi Email

**Fitur:**
- Daftar manual → langsung masuk dashboard, gak perlu konfirmasi email
- Login Google → langsung masuk
- Login biasa → langsung masuk
- **Gak ada** halaman "Cek email kamu"
- **Gak ada** SMTP timeout (karena gak pakai email)

**Demo:**
1. Klik Daftar
2. Isi form → submit
3. Langsung masuk ke /your-space ✅

---

## 7. Midtrans — Beli Plus / Plus+

**Lokasi:** `/plus`

**Fitur:**
- Dua paket: Plus (Rp15rb/bln) dan Plus+ (Rp25rb/bln)
- Pembayaran via Midtrans Snap popup
- Metode: GoPay, QRIS, Virtual Account, Convenience Store, Kartu Kredit
- Promo code bisa dimasukkan sebelum bayar
- Aktivasi otomatis via webhook

**Demo:**
1. Buka `/plus`
2. Klik "Langganan" di paket Plus
3. Klik "Bayar"
4. Tunjukin Midtrans Snap popup
5. Pilih GoPay → "Sukses" (sandbox)
6. Tunjukin modal "Selamat, Plus Aktif!"

---

## Timeline Perubahan

| Fitur | Status |
|-------|--------|
| Bagikan modal (3 opsi) | ✅ Selesai |
| Poster template story | ✅ Selesai |
| Inbox DM chat real-time | ✅ Selesai |
| List chat + avatar semua pesan | ✅ Selesai |
| Film share card (poster + rating) | ✅ Selesai |
| Google linking popup | ✅ Selesai |
| Hapus verifikasi email | ✅ Selesai |
| Share ke User & List | ✅ Selesai |
| Bug fix: MovieListService, ReferenceError, undefined key | ✅ Selesai |
| Midtrans payment | ✅ Selesai |
