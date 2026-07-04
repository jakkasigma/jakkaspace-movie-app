# Jakkaspace AI Development Rules

> Version: 1.0
> Status: Active

---

# Purpose

Dokumen ini menjadi aturan utama dalam pengembangan Jakkaspace.

Semua implementasi, refactor, maupun penambahan fitur harus mengikuti aturan ini.

Prioritas utama bukan kecepatan development, tetapi kualitas produk yang dapat berkembang dalam jangka panjang.

---

# Development Philosophy

- Bangun pondasi terlebih dahulu sebelum menambah fitur.
- Hindari solusi sementara jika solusi yang benar masih memungkinkan.
- Selalu pikirkan dampak perubahan terhadap pengembangan di masa depan.
- Refactor lebih baik daripada menumpuk technical debt.
- Setiap perubahan harus memiliki alasan yang jelas.

---

# Project Goals

Seluruh keputusan teknis harus mendukung visi Jakkaspace sebagai:

- Personal Movie Diary
- Social Platform for Movie Lovers
- Long-Term Scalable Product

Jika suatu implementasi tidak mendukung tujuan tersebut, maka implementasi harus dievaluasi kembali.

---

# Architecture Rules

- Gunakan arsitektur yang modular.
- Hindari business logic di Controller.
- Pisahkan setiap tanggung jawab sesuai domain.
- Jangan membuat class dengan banyak tanggung jawab.
- Hindari ketergantungan yang tidak diperlukan.

---

# Code Principles

Kode harus:

- Mudah dibaca.
- Mudah dipelihara.
- Mudah diperluas.
- Konsisten.
- Tidak berlebihan.

Lebih baik kode sederhana yang jelas daripada kode pintar yang sulit dipahami.

---

# Feature Development Rules

Sebelum membuat fitur baru, pastikan:

- Tujuan fitur jelas.
- Domain yang bertanggung jawab sudah benar.
- Tidak menduplikasi fungsi yang sudah ada.
- Tidak merusak arsitektur yang sudah dibangun.

---

# Refactoring Rules

Refactor dilakukan apabila:

- Struktur mulai sulit dipahami.
- Ada duplikasi logika.
- Tanggung jawab class mulai bercampur.
- Penambahan fitur menjadi sulit.

Refactor bukan berarti menulis ulang seluruh sistem.

---

# Performance Rules

Optimasi dilakukan apabila memang dibutuhkan.

Hindari optimasi yang terlalu dini.

Namun tetap pertimbangkan:

- Jumlah request.
- Response time.
- Penggunaan resource.
- Kemudahan scaling.

---

# Security Rules

Semua fitur harus mempertimbangkan keamanan sejak awal.

Tidak boleh menganggap keamanan sebagai pekerjaan belakangan.

---

# UI Principles

UI harus:

- Bersih.
- Konsisten.
- Responsif.
- Mudah dipahami.
- Fokus pada pengalaman pengguna.

Hindari dekorasi yang tidak memiliki tujuan.

---

# Decision Rules

Apabila terdapat lebih dari satu solusi, pilih solusi yang:

- Lebih sederhana.
- Lebih mudah dipelihara.
- Lebih mudah dikembangkan.
- Lebih konsisten dengan arsitektur.

Jangan memilih solusi hanya karena terlihat lebih canggih.

---

# AI Collaboration Rules

Saat bekerja menggunakan AI:

- Jangan langsung menghasilkan kode.
- Pahami konteks proyek terlebih dahulu.
- Hormati struktur yang sudah ada.
- Jangan mengubah arsitektur tanpa alasan yang kuat.
- Jangan melakukan refactor besar tanpa persetujuan.
- Berikan alasan untuk setiap keputusan teknis.
- Jelaskan trade-off apabila terdapat beberapa pilihan implementasi.

---

# Long-Term Vision

Jakkaspace dibangun sebagai produk jangka panjang.

Setiap keputusan harus mempertimbangkan kemungkinan pengembangan selama bertahun-tahun, bukan hanya penyelesaian sprint saat ini.

---

# Golden Rule

Setiap perubahan harus membuat Jakkaspace menjadi:

- Lebih rapi.
- Lebih stabil.
- Lebih mudah dikembangkan.
- Lebih dekat dengan visi produk.

Jika tidak memenuhi salah satu tujuan tersebut, perubahan harus dipertimbangkan kembali.