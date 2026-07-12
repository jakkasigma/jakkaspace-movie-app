# Android APK — Setup Capacitor WebView

## Tujuan
Membungkus web app Laravel (Jakka Space) sebagai aplikasi Android menggunakan **Capacitor WebView mode URL**.

APK berisi WebView yang langsung loading `https://domainkamu.com`. **Update fitur cukup deploy web** — APK tidak perlu dibangun ulang kecuali ada perubahan native plugin.

## Prasyarat

| Komponen | Status |
|---|---|
| Java 21 | ✅ Terinstall |
| Flutter 3.44.1 (include Android SDK) | ✅ Terinstall |
| Capacitor npm packages | ❌ Belum |
| Gradle (dari Android SDK) | ✅ Nanti otomatis |
| Android Studio | ❌ Tidak diperlukan — build via Gradle CLI |

## Tahapan

### 1. Install Capacitor

```bash
npm install @capacitor/core @capacitor/cli @capacitor/android
```

### 2. Init Project Android

```bash
npx cap init com.jakkaspace.app "Jakka Space"
npx cap add android
```

### 3. Konfigurasi Server URL

Buat/ubah `capacitor.config.json`:

```json
{
  "appId": "com.jakkaspace.app",
  "appName": "Jakka Space",
  "server": {
    "url": "https://jakkaspace-movie-app-production.up.railway.app",
    "allowNavigation": ["jakkaspace-movie-app-production.up.railway.app"]
  },
  "android": {
    "allowMixedContent": true
  }
}
```

**Catatan:** `allowMixedContent: true` agar gambar HTTP tetap bisa dimuat dari server.

### 4. Build Web + Sync ke Android

```bash
npm run build              # build Vite (CSS/JS)
npx cap sync              # copy web assets ke android/
```

### 5. Build APK via Gradle CLI

```bash
cd android
./gradlew assembleDebug
```

Hasil: `android/app/build/outputs/apk/debug/app-debug.apk`

### 6. Host APK

```bash
mkdir -p public/apk
cp android/app/build/outputs/apk/debug/app-debug.apk public/apk/jakkaspace.apk
```

### 7. Tombol Download di Settings

Tambah di halaman settings/profile — link download APK.

### 8. Update Kedepan

Setiap kali ada perubahan fitur:

```bash
npm run build              # rebuild Vite
npx cap sync              # sync ke android
cd android && ./gradlew assembleDebug  # rebuild APK
cp app/build/outputs/apk/debug/app-debug.apk ../../public/apk/jakkaspace.apk
```

Atau jika hanya perubahan Blade / backend — cukup **deploy web** saja. APK tetap loading URL terbaru.

## PWA (Progressive Web App) — Alternatif iOS/Windows/macOS

PWA bekerja tanpa Play Store / App Store. Cukup buka dari browser → "Add to Home Screen" / "Install".

### File PWA

| File | Fungsi |
|---|---|
| `public/manifest.json` | Konfigurasi nama, icon, `display: standalone`, `theme_color` |
| `public/icons/icon-192.png` | Ikon 192x192 — copy dari `public/assets/logo.png` |
| `public/icons/icon-512.png` | Ikon 512x512 — copy dari `public/assets/logo.png` |
| `public/sw.js` | Service Worker — cache CSS/font/logo |

### Meta Tags

Di `resources/views/layouts/movie.blade.php` `<head>` tambah:

```html
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#050505">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
```

### Register Service Worker

Di `resources/js/app.js` atau inline:

```js
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}
```

### Settings Page — Multi-Platform

Di `resources/views/profile/edit.blade.php`:

| Platform | Yang Muncul |
|---|---|
| Android Chrome | Tombol Download APK (`.apk`) |
| iPhone/iPad Safari | Panduan "Add to Home Screen" |
| Windows Chrome/Edge | Info install PWA |
| macOS Chrome/Safari | Info install PWA |

## Catatan

- APK debug bisa langsung diinstall di HP (aktifkan "Install dari sumber tidak dikenal")
- Untuk publish ke Play Store: butuh release keystore + signed APK/AAB
- Mode debug menampilkan banner "Debug" di atas app — tidak mengganggu fungsi
