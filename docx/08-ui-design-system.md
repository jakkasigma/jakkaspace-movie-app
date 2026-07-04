# Jakkaspace — UI Design System

> Created: 2026-07-03
> Status: Active
> Referensi: shadcn/ui patterns, existing welcome.css design tokens

---

## Prinsip Desain

Jakkaspace menggunakan visual identity **dark cinematic** — gelap, bersih, typographic. Semua keputusan UI harus konsisten dengan karakter ini:

- Background hampir hitam (`#050505`, `#080808`)
- Text putih dengan opacity hierarchy (`100%`, `62%`, `42%`)
- Border sangat subtle (`rgba(255,255,255,0.08)`)
- Accent warna hanya di logo (multicolor SPACE) dan elemen interaktif sesekali
- Tidak ada warna brand tunggal — putih adalah aksen utama
- Typography: **Peace Sans** (logo), **Bebas Neue** (heading/label), **Inter** (body), **Lora** (kutipan/italic)

---

## Design Tokens

```css
/* Backgrounds */
--page-bg: #050505
--panel-bg: rgba(7, 7, 7, 0.86)
--panel-border: rgba(255, 255, 255, 0.08)

/* Text */
--text-main: #ffffff
--text-muted: rgba(255, 255, 255, 0.62)
--text-subtle: rgba(255, 255, 255, 0.42)

/* Shadows */
--shadow-soft: 0 18px 48px rgba(0, 0, 0, 0.34)
```

---

## Komponen UI (shadcn-inspired, dark cinematic)

### Button

Tiga varian, semua tanpa border-radius besar (max 4px):

**Primary** — putih solid, teks hitam
```
background: #fff
color: #000
border: none
padding: 0.8rem 2rem
font: Bebas Neue 1.05rem
hover: background #e0e0e0, translateY(-2px)
```

**Secondary (Outline)** — transparan, border putih tipis
```
background: transparent
color: #fff
border: 1px solid rgba(255,255,255,0.3)
hover: background rgba(255,255,255,0.1)
```

**Ghost** — hampir invisible, untuk aksi sekunder
```
background: rgba(255,255,255,0.06)
color: rgba(255,255,255,0.82)
border: 1px solid rgba(255,255,255,0.12)
hover: background rgba(255,255,255,0.12)
```

**Active state** (tombol yang sedang aktif/on)
```
background: rgba(255,255,255,0.14)
border: 1px solid rgba(255,255,255,0.7)
color: #fff
```

---

### Input / Form

Semua form element mengikuti satu pola konsisten:

```
background: rgba(255,255,255,0.05)
border: 1px solid rgba(255,255,255,0.15)
border-radius: 4px
color: #fff
font: Inter 0.88rem
padding: 8px 12px
placeholder: rgba(255,255,255,0.28)
focus: border-color rgba(255,255,255,0.45), no outline
```

Label: uppercase, 0.68–0.72rem, letter-spacing 0.1em, warna `--text-subtle`

---

### Card

Dipakai untuk movie card, diary card, stat card:

```
background: rgba(255,255,255,0.035)
border: 1px solid rgba(255,255,255,0.08)
border-radius: 8px
padding: 14–16px
```

Hover state pada movie card:
```
transform: translateY(-6px)
border-color: rgba(255,255,255,0.3)
```

---

### Badge / Chip

Untuk status, genre, label:

```
border: 1px solid rgba(255,255,255,0.15)
border-radius: 999px
padding: 2px 10px
font-size: 0.68–0.72rem
font-weight: 700
text-transform: uppercase
color: --text-muted
```

Status badge punya warna khusus:
- `watched` → hijau subtle
- `watching` → biru subtle  
- `dropped` → merah subtle

---

### Divider

```
border: none
border-top: 1px solid rgba(255,255,255,0.08)
```

---

## Navbar — User Menu

### Perubahan yang dibutuhkan

Navbar sekarang belum menampilkan state login user. Perlu ditambah:

**Jika belum login:**
```
[ Login ]  ← button ghost kecil di kanan
```

**Jika sudah login:**
```
[ avatar kecil + nama/initial ]  ← dropdown trigger di kanan
```

**Dropdown isi:**
```
┌─────────────────────┐
│  nama user          │
│  @username (jika ada)│
├─────────────────────┤
│  Your Space         │
│  Diary              │
│  History            │
│  Watchlist          │
│  Favorit            │
├─────────────────────┤
│  Logout             │
└─────────────────────┘
```

Dropdown style:
```
background: rgba(8,8,8,0.96)
border: 1px solid rgba(255,255,255,0.1)
border-radius: 8px
backdrop-filter: blur(16px)
padding: 8px
min-width: 200px
box-shadow: 0 20px 60px rgba(0,0,0,0.6)
```

Item dropdown:
```
padding: 8px 12px
border-radius: 4px
font-size: 0.84rem
color: --text-muted
hover: background rgba(255,255,255,0.06), color #fff
```

---

## Auth Pages (Login & Register)

### Layout yang sudah ada

Guest layout sudah bagus — split dua kolom, kiri panel branding, kanan form. **Tidak perlu diubah strukturnya.**

Yang perlu dipastikan konsisten:
- Font loading: Inter, Bebas Neue, Lora, Peace Sans harus ada di guest layout
- Background dan border sudah benar (`bg-zinc-950`, `border-white/10`)

### Tombol Google

Sudah ada. Pastikan styling konsisten:
- Border `rgba(255,255,255,0.15)`
- Background `rgba(255,255,255,0.05)`
- Hover: `rgba(255,255,255,0.10)`
- Font semibold, bukan Bebas Neue

---

## Your Space Pages

### Pola layout

Semua halaman space mengikuti struktur:

```
┌─────────────────────────────────────┐
│  Navbar (global, dengan user menu)  │
├─────────────────────────────────────┤
│  Space Header                       │
│  (avatar + nama + stats)            │
├─────────────────────────────────────┤
│  Space Nav (tab horizontal)         │
│  Ringkasan | Diary | History | ...  │
├─────────────────────────────────────┤
│  Content Area                       │
│                                     │
└─────────────────────────────────────┘
```

### Mobile Your Space

Di mobile, Space Nav jadi sticky di bawah layar (tab bar):
```
position: fixed
bottom: 0
left: 0
right: 0
display: flex
border-top: 1px solid rgba(255,255,255,0.08)
background: rgba(0,0,0,0.94)
backdrop-filter: blur(16px)
padding-bottom: env(safe-area-inset-bottom)
```

Setiap tab: icon + label kecil, stacked vertikal.

---

## Movie Detail — Action Buttons

Tombol aksi (watched, watchlist, favorite) harus terasa sebagai bagian natural dari halaman — bukan elemen asing. Gunakan Ghost button style, dengan Active state yang jelas saat sudah aktif.

Form diary/review menggunakan `<details>` collapse — sudah benar, perlu pastikan transisi smooth.

---

## Discover & Genre Pages

Filter bar mengikuti pola input yang sama. Select element harus konsisten dengan input lain.

Pagination menggunakan Outline button style.

---

## Implementasi

### Urutan pengerjaan UI:

```
1. Tambah font ke guest layout (Peace Sans, Bebas Neue, Lora)
2. Update navbar — tambah user menu / dropdown
3. Update movie detail — pastikan tombol aksi konsisten
4. Update Your Space — mobile tab bar di bawah
5. Review semua halaman space untuk konsistensi
```

---

## Catatan untuk Developer/AI

- Semua styling baru masuk ke `welcome.css` (bukan file terpisah) kecuali ada alasan kuat
- Tailwind hanya dipakai di auth pages (guest layout area) — halaman movie pakai custom CSS
- Jangan mix Tailwind dan custom CSS di komponen yang sama
- Setiap warna baru harus menggunakan CSS variable yang sudah ada dulu
- Tidak boleh mengubah design token yang sudah ada tanpa alasan yang kuat
- Mobile-first untuk semua halaman baru

---

*Dokumen ini menjadi referensi utama untuk semua keputusan UI di Jakkaspace.*
