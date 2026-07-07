# Tutorial Deploy Jakkaspace ke Railway — Lengkap dari Nol

> Panduan ini mencakup seluruh proses: dari persiapan lokal, setup Railway, sampai aplikasi bisa diakses publik. Estimasi waktu: 30–60 menit.

---

## Daftar Isi

1. [Prasyarat](#1-prasyarat)
2. [Persiapan Lokal](#2-persiapan-lokal)
3. [Buat Akun & Project Railway](#3-buat-akun--project-railway)
4. [Setup Database MySQL](#4-setup-database-mysql)
5. [Setup Service Web (Main)](#5-setup-service-web-main)
6. [Setup Environment Variables](#6-setup-environment-variables)
7. [Setup Service Queue Worker](#7-setup-service-queue-worker)
8. [Setup Service Reverb (WebSocket)](#8-setup-service-reverb-websocket)
9. [Setup Service Scheduler](#9-setup-service-scheduler)
10. [Deploy & Verifikasi](#10-deploy--verifikasi)
11. [Google OAuth (Opsional)](#11-google-oauth-opsional)
12. [Custom Domain (Opsional)](#12-custom-domain-opsional)
13. [Troubleshooting](#13-troubleshooting)

---

## 1. Prasyarat

Sebelum mulai, pastikan kamu sudah punya:

- [ ] **Akun GitHub** — repo harus di-push ke GitHub
- [ ] **Akun Railway** — daftar di [railway.app](https://railway.app) (bisa pakai GitHub)
- [ ] **TMDB API Key** — daftar di [themoviedb.org](https://www.themoviedb.org/settings/api)
- [ ] **Google OAuth credentials** (opsional, untuk login Google)
- [ ] **Git** terinstall di komputer lokal

### Cek versi minimum:
```bash
php --version    # >= 8.3
node --version   # >= 20
npm --version    # >= 10
```

---

## 2. Persiapan Lokal

### 2.1 Generate Reverb Key

Jalankan di terminal lokal untuk mendapatkan credentials WebSocket:

```bash
php artisan reverb:key
```

Catat outputnya — akan ada 3 nilai:
```
REVERB_APP_ID=123456
REVERB_APP_KEY=abcdef123456
REVERB_APP_SECRET=secretxyz789
```

Simpan ketiga nilai ini, akan dipakai di Railway nanti.

### 2.2 Push ke GitHub

Pastikan semua perubahan sudah di-commit dan di-push:

```bash
git add .
git commit -m "chore: persiapan deploy Railway"
git push origin main
```

Pastikan file-file ini ada di repo:
- `railway.json` ✓
- `nixpacks.toml` ✓
- `database/seeders/ProductionSeeder.php` ✓

---

## 3. Buat Akun & Project Railway

### 3.1 Daftar / Login

1. Buka [railway.app](https://railway.app)
2. Klik **Login** → pilih **Login with GitHub**
3. Authorize Railway untuk akses GitHub

### 3.2 Buat Project Baru

1. Dari dashboard Railway, klik **New Project**
2. Pilih **Deploy from GitHub repo**
3. Cari dan pilih repo `jakkaspace`
4. Railway akan otomatis mendeteksi `railway.json` dan memulai build pertama

> **Catatan:** Build pertama kemungkinan **gagal** karena environment variables belum diisi. Itu normal — kita set nanti.

---

## 4. Setup Database MySQL

### 4.1 Tambah Plugin MySQL

1. Di dalam project Railway, klik tombol **+ New** (pojok kanan atas)
2. Pilih **Database** → pilih **MySQL**
3. Tunggu beberapa detik hingga database provisioned (status berubah jadi hijau)

### 4.2 Ambil Credentials

1. Klik service **MySQL** yang baru dibuat
2. Klik tab **Variables**
3. Catat nilai-nilai berikut (atau klik **Copy** lalu paste langsung nanti):
   - `MYSQLHOST` → nilai untuk `DB_HOST`
   - `MYSQLPORT` → nilai untuk `DB_PORT`
   - `MYSQLDATABASE` → nilai untuk `DB_DATABASE`
   - `MYSQLUSER` → nilai untuk `DB_USERNAME`
   - `MYSQLPASSWORD` → nilai untuk `DB_PASSWORD`

> Railway juga menyediakan `DATABASE_URL` (MySQL URL format), tapi kita pakai variabel individual agar lebih jelas.

---

## 5. Setup Service Web (Main)

Service Web adalah entry point utama aplikasi yang bisa diakses publik.

### 5.1 Generate Domain

1. Klik service **Web** (service dari GitHub repo)
2. Klik tab **Settings**
3. Di bagian **Networking** → klik **Generate Domain**
4. Railway akan memberikan URL seperti `jakkaspace-production.up.railway.app`
5. **Catat URL ini** — ini adalah `APP_URL` kamu

### 5.2 Verifikasi railway.json

Railway membaca `railway.json` di root project. File yang sudah kita siapkan:

```json
{
  "build": {
    "buildCommand": "composer install --no-dev ... && npm run build && php artisan config:cache ..."
  },
  "deploy": {
    "startCommand": "php artisan migrate --force && php artisan db:seed --class=ProductionSeeder --force && php artisan serve --host=0.0.0.0 --port=$PORT",
    "healthcheckPath": "/up"
  }
}
```

---

## 6. Setup Environment Variables

Ini bagian terpenting. Set semua variabel di service **Web**.

### 6.1 Cara Set Variables

1. Klik service **Web**
2. Pilih tab **Variables**
3. Klik **+ New Variable** atau gunakan **Raw Editor** untuk paste sekaligus

### 6.2 Daftar Lengkap Variables

Copy blok di bawah ke **Raw Editor**, lalu sesuaikan nilainya:

> **Penting — Build Variable:** Tambahkan `NIXPACKS_NODE_VERSION=20` agar Railway tidak pakai Node versi terbaru yang belum tersedia di nixpkgs. Ini harus diset **sebelum** build pertama.

```env
# ── Build (WAJIB diset sebelum deploy pertama) ─
NIXPACKS_NODE_VERSION=20

# ── Aplikasi ──────────────────────────────────
APP_NAME=Jakkaspace
APP_ENV=production
APP_DEBUG=false
APP_URL=https://DOMAIN_KAMU.up.railway.app
APP_KEY=

# ── Database (dari MySQL plugin) ──────────────
DB_CONNECTION=mysql
DB_HOST=DARI_MYSQLHOST
DB_PORT=3306
DB_DATABASE=DARI_MYSQLDATABASE
DB_USERNAME=DARI_MYSQLUSER
DB_PASSWORD=DARI_MYSQLPASSWORD

# ── Session / Cache / Queue ───────────────────
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

# ── Broadcasting (Reverb) ─────────────────────
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=DARI_REVERB_KEY_LOKAL
REVERB_APP_KEY=DARI_REVERB_KEY_LOKAL
REVERB_APP_SECRET=DARI_REVERB_KEY_LOKAL
REVERB_HOST=DOMAIN_SERVICE_REVERB.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https

# ── Vite (untuk frontend) ─────────────────────
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
VITE_APP_NAME=${APP_NAME}

# ── TMDB API ──────────────────────────────────
TMDB_API_KEY=API_KEY_DARI_TMDB
TMDB_BASE_URL=https://api.themoviedb.org/3

# ── Google OAuth ──────────────────────────────
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://DOMAIN_KAMU.up.railway.app/auth/google/callback

# ── Email ─────────────────────────────────────
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@jakkaspace.app
MAIL_FROM_NAME=Jakkaspace

# ── Lainnya ───────────────────────────────────
FILESYSTEM_DISK=local
LOG_CHANNEL=stderr
LOG_LEVEL=error
```

> **Penting:** `APP_KEY` biarkan **kosong** — Railway akan mendeteksinya dan Anda bisa generate dari shell setelah deploy pertama, atau isi dengan `base64:` + output dari `php artisan key:generate --show` di lokal.

### 6.3 Generate APP_KEY

Jalankan di lokal untuk mendapatkan key yang valid:

```bash
php artisan key:generate --show
# Output: base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=
```

Paste hasilnya ke variable `APP_KEY` di Railway.

---

## 7. Setup Service Queue Worker

Queue worker memproses notifikasi, email, dan job background lainnya.

### 7.1 Buat Service Baru

1. Di project Railway, klik **+ New** → **Empty Service**
2. Beri nama: `queue`

### 7.2 Hubungkan ke Repo

1. Klik service `queue` → tab **Settings**
2. Di bagian **Source** → klik **Connect Repo**
3. Pilih repo `jakkaspace` yang sama
4. Branch: `main`

### 7.3 Set Start Command

1. Masih di tab **Settings** → bagian **Deploy**
2. Isi **Custom Start Command**:
   ```
   php artisan queue:work --tries=3 --sleep=3 --max-time=3600
   ```

### 7.4 Set Environment Variables

Service queue butuh akses database dan config yang sama. Copy variables ini dari service Web:

```env
APP_ENV=production
APP_KEY=SAMA_DENGAN_WEB
DB_CONNECTION=mysql
DB_HOST=DARI_MYSQLHOST
DB_PORT=3306
DB_DATABASE=DARI_MYSQLDATABASE
DB_USERNAME=DARI_MYSQLUSER
DB_PASSWORD=DARI_MYSQLPASSWORD
QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_CHANNEL=stderr
LOG_LEVEL=error
```

> **Tip:** Railway memiliki fitur **Shared Variables** di level project. Kalau kamu set variabel di sana, semua service bisa menggunakannya.

---

## 8. Setup Service Reverb (WebSocket)

Reverb adalah WebSocket server untuk fitur real-time (chat, notifikasi live).

### 8.1 Buat Service Baru

1. Klik **+ New** → **Empty Service**
2. Beri nama: `reverb`

### 8.2 Hubungkan ke Repo

Sama seperti queue — connect ke repo `jakkaspace`, branch `main`.

### 8.3 Set Start Command

```
php artisan reverb:start --host=0.0.0.0 --port=$PORT --no-interaction
```

### 8.4 Generate Domain untuk Reverb

1. Klik service `reverb` → tab **Settings**
2. Di **Networking** → klik **Generate Domain**
3. Catat domain ini (contoh: `reverb-production.up.railway.app`)

### 8.5 Set Environment Variables untuk Reverb

```env
APP_ENV=production
APP_KEY=SAMA_DENGAN_WEB
APP_URL=https://DOMAIN_WEB.up.railway.app
DB_CONNECTION=mysql
DB_HOST=DARI_MYSQLHOST
DB_PORT=3306
DB_DATABASE=DARI_MYSQLDATABASE
DB_USERNAME=DARI_MYSQLUSER
DB_PASSWORD=DARI_MYSQLPASSWORD
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=SAMA_DENGAN_WEB
REVERB_APP_KEY=SAMA_DENGAN_WEB
REVERB_APP_SECRET=SAMA_DENGAN_WEB
REVERB_HOST=DOMAIN_REVERB.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=$PORT
LOG_CHANNEL=stderr
```

### 8.6 Update REVERB_HOST di Service Web

Setelah dapat domain reverb, kembali ke service **Web** dan update:
```env
REVERB_HOST=reverb-production.up.railway.app
VITE_REVERB_HOST=reverb-production.up.railway.app
```

Lalu trigger **Redeploy** pada service Web agar build ulang dengan VITE config yang benar.

---

## 9. Setup Service Scheduler

Scheduler menjalankan `php artisan schedule:run` setiap menit (cron job Laravel).

### 9.1 Buat Service Baru

1. Klik **+ New** → **Empty Service**
2. Beri nama: `scheduler`

### 9.2 Hubungkan ke Repo & Set Start Command

Connect ke repo yang sama, lalu isi start command:

```bash
while true; do php artisan schedule:run --no-interaction >> /dev/null 2>&1; sleep 60; done
```

### 9.3 Environment Variables untuk Scheduler

```env
APP_ENV=production
APP_KEY=SAMA_DENGAN_WEB
DB_CONNECTION=mysql
DB_HOST=DARI_MYSQLHOST
DB_PORT=3306
DB_DATABASE=DARI_MYSQLDATABASE
DB_USERNAME=DARI_MYSQLUSER
DB_PASSWORD=DARI_MYSQLPASSWORD
QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_CHANNEL=stderr
```

---

## 10. Deploy & Verifikasi

### 10.1 Trigger Deploy Service Web

Setelah semua variabel diisi:

1. Klik service **Web**
2. Klik tab **Deployments**
3. Klik **Deploy** atau **Redeploy**

Railway akan menjalankan:
1. `composer install --no-dev ...`
2. `npm install && npm run build`
3. `php artisan config:cache && route:cache && view:cache`
4. Saat start: `php artisan migrate --force`
5. Saat start: `php artisan db:seed --class=ProductionSeeder --force`
6. Start server: `php artisan serve ...`

### 10.2 Pantau Build Log

Klik **Deployments** → klik deployment aktif → lihat **Build Logs** dan **Deploy Logs**.

Build sukses jika melihat:
```
✓ npm run build completed
✓ php artisan config:cache completed
...
INFO  Application is ready to serve.
```

### 10.3 Checklist Verifikasi

Buka URL Railway kamu dan test satu per satu:

- [ ] **Home page** terbuka → `https://DOMAIN.up.railway.app`
- [ ] **Health check** → `https://DOMAIN.up.railway.app/up` → harus return `{"status":"UP"}`
- [ ] **Register** akun baru → form berfungsi
- [ ] **Login** dengan email/password → berhasil masuk
- [ ] **Browse film** → data TMDB tampil (pastikan `TMDB_API_KEY` sudah diisi)
- [ ] **Notifikasi real-time** → WebSocket connect (lihat browser console, tidak ada error Reverb)
- [ ] **Queue** → cek Railway logs di service `queue` ada output `Processing:`

### 10.4 Buat Admin Pertama

Setelah register, jadikan akun kamu sebagai admin via Railway Shell:

1. Klik service **Web** → tab **Settings** → **Open Shell** (atau klik ikon terminal)
2. Jalankan:
   ```bash
   php artisan make:admin email@kamu.com
   ```

---

## 11. Google OAuth (Opsional)

Agar login via Google berfungsi di production:

### 11.1 Setup Google Cloud Console

1. Buka [console.cloud.google.com](https://console.cloud.google.com)
2. Buat project baru atau pilih yang ada
3. Pergi ke **APIs & Services** → **Credentials**
4. Klik **Create Credentials** → **OAuth 2.0 Client ID**
5. Application type: **Web application**
6. Tambahkan **Authorized redirect URIs**:
   ```
   https://DOMAIN_KAMU.up.railway.app/auth/google/callback
   ```
7. Klik **Create** → catat **Client ID** dan **Client Secret**

### 11.2 Update Variables di Railway

Di service **Web**, update:
```env
GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxx
GOOGLE_REDIRECT_URI=https://DOMAIN_KAMU.up.railway.app/auth/google/callback
```

Trigger redeploy.

---

## 12. Custom Domain (Opsional)

Agar bisa diakses via domain sendiri (misal `jakkaspace.com`):

### 12.1 Tambah Custom Domain di Railway

1. Klik service **Web** → **Settings** → **Networking**
2. Klik **+ Custom Domain**
3. Masukkan domain kamu (misal `jakkaspace.com` atau `app.jakkaspace.com`)

### 12.2 Setup DNS

Railway akan menampilkan nilai CNAME yang perlu ditambahkan:

| Type | Name | Value |
|------|------|-------|
| CNAME | `@` atau `app` | `XXXX.railway.app` |

Tambahkan record ini di provider DNS kamu (Cloudflare, Namecheap, GoDaddy, dll).

Tunggu propagasi DNS (biasanya 5–30 menit).

### 12.3 Update APP_URL

Setelah domain aktif, update di Railway:
```env
APP_URL=https://jakkaspace.com
GOOGLE_REDIRECT_URI=https://jakkaspace.com/auth/google/callback
SESSION_DOMAIN=.jakkaspace.com
```

---

## 13. Troubleshooting

### "Unable to locate file in Vite manifest"

Build Vite gagal atau `public/build` tidak ada. 

**Solusi:** Pastikan `VITE_REVERB_*` variables sudah diset **sebelum** build. Trigger redeploy setelah set variables.

---

### Login gagal / session hilang terus

**Cek:**
- `SESSION_DRIVER=database` ✓
- `SESSION_SECURE_COOKIE=true` ✓
- Migration sudah jalan (`sessions` table ada) ✓

---

### WebSocket / chat tidak connect

**Cek di browser console** — biasanya ada error seperti:
```
WebSocket connection to 'wss://...' failed
```

**Solusi:**
- Pastikan `REVERB_HOST` di service Web = domain dari service Reverb
- Pastikan service Reverb sudah punya public domain
- `REVERB_PORT=443` dan `REVERB_SCHEME=https` di service Web
- `REVERB_SERVER_PORT=$PORT` di service Reverb

---

### Notifikasi / queue tidak terproses

**Cek logs** di service `queue`. Jika service mati:
- Pastikan env vars terisi lengkap
- Pastikan `QUEUE_CONNECTION=database`

---

### Build gagal: "PHP extension not found"

Nixpacks mungkin tidak menginstall extension yang dibutuhkan. Pastikan `NIXPACKS_NODE_VERSION=20` sudah diset di environment variables Railway.

---

### Build gagal: "undefined variable 'nodejs_24'" atau "undefined variable 'nodejs_XX'"

Railway Nixpacks auto-detect versi Node terbaru yang belum tentu tersedia di nixpkgs snapshot yang dipakai.

**Solusi:** Set environment variable di service Web:
```
NIXPACKS_NODE_VERSION=20
```
Lalu trigger **Redeploy**.

---

### Migration gagal: "Table already exists"

Aman diabaikan jika sudah deploy sebelumnya. Migration Laravel bersifat idempotent — hanya jalankan migration yang belum dijalankan.

---

### Disk penuh / storage hilang

Railway menggunakan **ephemeral filesystem** — file yang di-upload user (avatar, dll) akan **hilang saat redeploy**. Untuk production serius, gunakan **S3-compatible storage** (misal Cloudflare R2 atau AWS S3) dan set `FILESYSTEM_DISK=s3`.

---

## Ringkasan Arsitektur

```
Internet
    │
    ▼
┌─────────────────────────────────────────┐
│           Railway Project               │
│                                         │
│  ┌─────────┐  ┌───────┐  ┌──────────┐  │
│  │   Web   │  │ Queue │  │  Reverb  │  │
│  │  :$PORT │  │worker │  │  :$PORT  │  │
│  └────┬────┘  └───┬───┘  └────┬─────┘  │
│       │           │            │        │
│       └───────────┴────────────┘        │
│                   │                     │
│           ┌───────┴───────┐             │
│           │  MySQL Plugin  │             │
│           └───────────────┘             │
└─────────────────────────────────────────┘
```

| Service | Port Publik | Fungsi |
|---------|-------------|--------|
| Web | Ya (HTTPS) | Serve HTTP request, build Vite |
| Queue | Tidak | Process background jobs |
| Reverb | Ya (WSS) | WebSocket real-time |
| Scheduler | Tidak | Cron job Laravel |
| MySQL | Internal | Database |

---

*Tutorial ini dibuat untuk deploy Jakkaspace ke Railway. Diperbarui: Juli 2026.*
