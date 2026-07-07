# Deploy ke Railway

## Prasyarat

- [Akun Railway](https://railway.app)
- [Railway CLI](https://docs.railway.app/develop/cli) (opsional)
- Git repo sudah terhubung ke GitHub/GitLab

## Struktur Services

Buat **1 project** di Railway dengan **4 services** terpisah:

```
Project: jakkaspace
├── Service 1: Web        → php artisan serve --host=0.0.0.0 --port=$PORT
├── Service 2: Queue      → php artisan queue:work --tries=3 --sleep=3
├── Service 3: Reverb     → php artisan reverb:start --host=0.0.0.0 --port=$PORT
└── Service 4: Scheduler  → while true; do php artisan schedule:run --no-interaction & sleep 60; done
```

### 1. Service Web (main)
- Source: repo GitHub
- Build: otomatis pakai `railway.json`
- Start command: `php artisan serve --host=0.0.0.0 --port=$PORT`
- Health check: `/up`
- Expose port: `$PORT` (Railway set otomatis)

### 2. Service Queue
- Source: **repo yang sama**
- Start command: `php artisan queue:work --tries=3 --sleep=3`
- No HTTP port needed
- Build: pakai Nixpacks default (atau `composer install --no-dev` aja)

### 3. Service Reverb (WebSocket)
- Source: **repo yang sama**
- Start command: `php artisan reverb:start --host=0.0.0.0 --port=$PORT`
- Expose port sebagai public (biar client bisa connect)
- Set variable `REVERB_SERVER_PORT=$PORT`

### 4. Service Scheduler (Cron)
- Source: **repo yang sama**
- Start command: `while true; do php artisan schedule:run --no-interaction & sleep 60; done`
- No HTTP port needed

## Database / Plugins

Tambahkan **MySQL** plugin di Railway dashboard:
- Railway → New → Database → MySQL
- Setelah terbuat, Railway auto-generate `DATABASE_URL` / `MYSQL_URL`
- Copy **semua** kredensial (host, port, db, user, password) ke environment variables service Web

Opsional: tambahkan **Redis** jika mau (untuk cache/queue lebih cepat).

## Environment Variables

Set di **setiap service** yang membutuhkan (Web, Queue, Reverb, Scheduler):

### Wajib (semua service)
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<railway-domain>.up.railway.app
APP_KEY=base64:...   # Biarkan kosong → Railway auto-generate
```

### Database
Isi dari MySQL plugin Railway:
```
DB_CONNECTION=mysql
DB_HOST=<railway-mysql-host>
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=<password-dari-railway>
```

### Session, Cache, Queue
```
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Broadcasting (Reverb)
Set di service **Web + Reverb**:
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...            # generate pakai: php artisan reverb:key
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=<reverb-service-domain>.railway.app   # domain dari service Reverb
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=$PORT     # khusus service Reverb
```

Set di service **Web** untuk frontend Vite:
```
VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

### Google OAuth
```
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://<domain>/auth/google/callback
```

### TMDB API
```
TMDB_API_KEY=3506024687084c358f26c724d7279e74
TMDB_BASE_URL=https://api.themoviedb.org/3
```

### Email (opsional)
```
MAIL_MAILER=log     # atau SMTP production
```

### Session Cookie Domain (penting!)
Karena Reverb di subdomain berbeda, session cookie harus di-share:
```
SESSION_DOMAIN=.railway.app
SESSION_SECURE_COOKIE=true
```

## Langkah Deploy

### 1. Push ke GitHub
```bash
git add .
git commit -m "Persiapan deploy Railway"
git push origin main
```

### 2. Buat Project di Railway
1. Login ke [railway.app](https://railway.app)
2. New Project → Deploy from GitHub repo
3. Pilih repo → Railway auto-deploy

### 3. Tambah MySQL
1. Di project Railway → New → Database → MySQL
2. Tunggu provisioning selesai

### 4. Set Environment Variables
1. Pilih service Web → Variables
2. Tambah semua env vars dari tabel di atas
3. **Jangan lupa generate Reverb key**:

   Cara generate Reverb key di lokal:
   ```bash
   php artisan reverb:key
   ```
   Output-nya akan kasih 3 nilai: `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`

### 5. Tambah Service Queue
1. New → Empty Service → Start command: `php artisan queue:work --tries=3 --sleep=3`
2. Source → Same repo
3. Set env vars (sama dengan Web, kecuali APP_URL dan REVERB_HOST)

### 6. Tambah Service Reverb
1. New → Empty Service → Start command: `php artisan reverb:start --host=0.0.0.0 --port=$PORT`
2. Source → Same repo
3. Set env vars + tambah `REVERB_SERVER_PORT=$PORT`
4. Expose port → Generate domain → Catat domain-nya

### 7. Tambah Service Scheduler
1. New → Empty Service
2. Start command: `while true; do php artisan schedule:run --no-interaction & sleep 60; done`
3. Source → Same repo
4. Set env vars (minimal yang diperlukan)

### 8. Deploy Manual (pertama kali)
Setelah semua env vars terisi, trigger redeploy:
```bash
railway up
```
Atau dari dashboard Railway → Deploy → Trigger Deploy

### 9. Run Migration
Setelah web service running, buka Railway dashboard → Web Service → Connect → Open Shell:
```bash
php artisan migrate --force
```

### 10. Verifikasi
- Buka URL Railway → harusnya home page tampil
- Cek `/up` → harus return JSON `{"status": "UP"}`
- Test login → harus bisa
- Test WebSocket → harus connect

## Troubleshooting

### "Unable to locate file in Vite manifest"
Jalanin `npm install && npm run build` ulang, atau set `APP_ENV=production` sebelum build.

### Session tidak persist (login gagal)
- Pastikan `SESSION_DOMAIN=.railway.app` sudah set
- Pastikan `SESSION_SECURE_COOKIE=true`
- Session, cache, queue semua pakai database, jadi migration harus sudah jalan

### Reverb tidak connect
- Pastikan `REVERB_HOST` point ke domain service Reverb (bukan Web)
- Pastikan `REVERB_SERVER_PORT=$PORT` di service Reverb
- Pastikan `REVERB_PORT=443` dan `REVERB_SCHEME=https` di service Web

### Queue worker mati
Tambahkan restart policy di Railway atau set `restartPolicyType: always` di railway.json.
