# Deploy Notes

> Tutorial lengkap ada di [`docx/DEPLOY-RAILWAY.md`](docx/DEPLOY-RAILWAY.md)

## Quick Reference

### File konfigurasi Railway
- `railway.json` — build & start command untuk service Web
- `nixpacks.toml` — PHP extensions dan Node version untuk Nixpacks
- `database/seeders/ProductionSeeder.php` — seed data wajib (subscription plans + themes)

### Struktur Services Railway

| Service | Start Command | Port Publik |
|---------|---------------|-------------|
| Web | *(dari railway.json)* | Ya |
| Queue | `php artisan queue:work --tries=3 --sleep=3 --max-time=3600` | Tidak |
| Reverb | `php artisan reverb:start --host=0.0.0.0 --port=$PORT --no-interaction` | Ya (WSS) |
| Scheduler | `while true; do php artisan schedule:run --no-interaction >> /dev/null 2>&1; sleep 60; done` | Tidak |

### Variables Minimal untuk Service Web

```env
# ── WAJIB: pin Node version untuk Nixpacks ────
NIXPACKS_NODE_VERSION=20

# ── Aplikasi ──────────────────────────────────
APP_NAME=Jakkaspace
APP_ENV=production
APP_DEBUG=false
APP_URL=https://DOMAIN.up.railway.app
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=DOMAIN_REVERB.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY=${REVERB_APP_KEY}
VITE_REVERB_HOST=${REVERB_HOST}
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https

TMDB_API_KEY=...
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://DOMAIN.up.railway.app/auth/google/callback

LOG_CHANNEL=stderr
LOG_LEVEL=error
```

### Generate keys sebelum deploy

```bash
# APP_KEY
php artisan key:generate --show

# Reverb credentials
php artisan reverb:key
```

### Post-deploy: buat admin

Buka Railway Shell → service Web:
```bash
php artisan make:admin email@kamu.com
```
