# Jakkaspace Project Context

> Version: 1.0
> Status: Active
> Last Updated: YYYY-MM-DD

---

# Project Overview

Jakkaspace adalah aplikasi web yang sedang dikembangkan sebagai platform personal movie diary dan social platform untuk pecinta film.

Pada tahap awal, aplikasi berfungsi sebagai movie discovery berbasis TMDB. Seiring perkembangan, aplikasi akan berevolusi menjadi platform yang memungkinkan pengguna mencatat perjalanan menonton mereka, menulis diary, membuat review, mengelola daftar film, dan berinteraksi dengan komunitas.

Proyek ini dikembangkan sebagai produk jangka panjang, sehingga seluruh keputusan teknis harus mempertimbangkan skalabilitas, maintainability, dan pengalaman pengguna.

---

# Current Stage

Status pengembangan saat ini:

- MVP Movie Discovery
- Menggunakan data dari TMDB API
- Belum memiliki sistem autentikasi penuh
- Belum memiliki fitur sosial
- Sedang melakukan perbaikan fondasi arsitektur sebelum pengembangan fitur besar

Prioritas utama saat ini adalah memperkuat pondasi aplikasi.

---

# Long-Term Vision

Jakkaspace akan berkembang menjadi:

- Personal Movie Diary
- Movie Review Platform
- Movie Tracking Platform
- Social Platform untuk pecinta film
- Personalized Movie Recommendation Platform

Movie hanyalah sumber data.

Fokus utama aplikasi adalah aktivitas dan perjalanan pengguna terhadap film.

---

# Target Users

Target pengguna utama:

- Pecinta film
- Penonton kasual
- Reviewer
- Kolektor film
- Pengguna yang ingin mendokumentasikan perjalanan menonton mereka

---

# Tech Stack

Backend:
- Laravel 12
- PHP 8.2

Frontend:
- Blade
- Tailwind CSS
- Vite

Data Source:
- TMDB API

Testing:
- Pest

Code Style:
- Laravel Pint

---

# Architecture Direction

Project akan dikembangkan menggunakan arsitektur modular.

Business Logic dipisahkan dari Controller.

Seluruh fitur baru harus mengikuti domain aplikasi.

Arsitektur akan berkembang secara bertahap tanpa melakukan rewrite besar.

---

# Development Philosophy

Project ini lebih mengutamakan:

- kualitas dibanding kecepatan
- maintainability dibanding shortcut
- konsistensi dibanding preferensi pribadi
- scalability dibanding implementasi sementara

---

# Current Priorities

Prioritas saat ini:

1. Memperbaiki fondasi aplikasi
2. Merapikan arsitektur
3. Menyiapkan domain aplikasi
4. Mendesain database
5. Baru kemudian mengembangkan fitur baru

---

# AI Instructions

Saat membantu pengembangan proyek ini:

- Pahami konteks proyek sebelum memberikan solusi.
- Jangan mengubah arsitektur tanpa alasan yang jelas.
- Jangan membuat implementasi yang bertentangan dengan dokumentasi.
- Jelaskan alasan setiap keputusan teknis.
- Jika terdapat beberapa solusi, jelaskan kelebihan dan kekurangannya.
- Utamakan solusi yang mudah dipelihara dalam jangka panjang.

---

# Source of Truth

Dokumen yang menjadi acuan utama proyek:

1. 00-project-context.md
2. 01-product-foundation.md
3. 02-ai-development-rules.md

Jika terjadi konflik antar dokumen, gunakan urutan di atas sebagai prioritas.

---

# Notes

Jakkaspace bukan sekadar proyek latihan.

Target akhirnya adalah menjadi produk nyata yang dapat terus berkembang.

Seluruh keputusan pengembangan harus selalu mendukung tujuan tersebut.