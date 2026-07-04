x   # Jakkaspace System Architecture

> Version: 1.0
> Status: Draft
> Last Updated: YYYY-MM-DD

---

# Purpose

Dokumen ini menjelaskan bagaimana seluruh sistem Jakkaspace dibangun.

Tujuannya adalah menjaga konsistensi arsitektur selama pengembangan sehingga setiap fitur baru mengikuti pola yang sama.

Dokumen ini menjadi acuan utama sebelum melakukan refactor maupun implementasi fitur baru.

---

# Architecture Philosophy

Jakkaspace menggunakan pendekatan arsitektur yang sederhana, modular, dan mudah berkembang.

Arsitektur dipilih berdasarkan prinsip:

- Mudah dipahami.
- Mudah dipelihara.
- Mudah dikembangkan.
- Tidak melakukan overengineering.
- Mengikuti best practice Laravel.

---

# High Level Architecture

Semua request mengikuti alur berikut.

```

Browser
│
▼
Routes
│
▼
Controller
│
▼
Service
│
├───────────────┐
│               │
▼               ▼
TMDB Client   Database
│               │
└───────┬───────┘
▼
Response Formatter
│
▼
Blade View / JSON Response

```

Controller tidak boleh langsung mengambil data dari TMDB atau Database.

Semua logika bisnis berada pada Service.

---

# Application Layers

## Presentation Layer

Bertanggung jawab terhadap:

- Route
- Controller
- Blade
- View Component

Layer ini hanya menangani request dan response.

Tidak boleh berisi business logic.

---

## Business Layer

Bertanggung jawab terhadap:

- Business Logic
- Workflow aplikasi
- Validasi proses bisnis
- Penggabungan beberapa sumber data

Layer ini berada pada Service.

---

## Data Layer

Bertanggung jawab terhadap:

- Database
- External API
- Cache

Layer ini tidak mengetahui bagaimana data akan ditampilkan.

---

# Folder Responsibilities

## Controller

Controller hanya bertugas:

- menerima request
- memanggil Service
- mengembalikan response

Controller tidak boleh:

- melakukan query kompleks
- memanggil TMDB secara langsung
- melakukan formatting data
- berisi business logic

---

## Service

Service adalah pusat logika aplikasi.

Semua aturan bisnis berada di sini.

Service boleh:

- mengakses TMDB
- mengakses Database
- mengakses Cache
- menggabungkan beberapa sumber data
- mengatur workflow

---

## TMDB Client

Semua komunikasi dengan TMDB dilakukan melalui satu client.

Tidak boleh ada request HTTP langsung di Controller maupun Blade.

Apabila suatu saat provider data berubah, perubahan hanya dilakukan pada layer ini.

---

## Models

Model hanya merepresentasikan data.

Model tidak boleh menjadi tempat seluruh business logic aplikasi.

---

## Blade

Blade hanya bertanggung jawab terhadap tampilan.

Blade tidak boleh melakukan:

- Query
- HTTP Request
- Perhitungan kompleks
- Business Logic

---

# Request Lifecycle

Semua fitur baru harus mengikuti alur berikut.

```

User Request

↓

Route

↓

Controller

↓

Service

↓

TMDB / Database / Cache

↓

Data Processing

↓

Response

↓

Blade

```

Tidak boleh melewati layer.

---

# Data Flow Principles

Semua data harus mengalir satu arah.

```

Data Source

↓

Service

↓

Controller

↓

View

```

View tidak boleh mengambil data sendiri.

---

# Dependency Rules

Controller boleh mengetahui Service.

Service boleh mengetahui:

- Database
- Cache
- External API

TMDB Client tidak boleh mengetahui Controller.

Blade tidak boleh mengetahui Service.

---

# Error Handling Flow

Semua error berasal dari:

- Validation
- Database
- External API
- Internal Error

Seluruh error diproses sebelum dikirim ke pengguna.

User tidak boleh melihat error internal Laravel.

---

# Caching Strategy

Data yang berasal dari TMDB harus dipertimbangkan untuk menggunakan cache.

Contohnya:

- Trending
- Popular
- Genres
- Detail Movie
- Credits

Cache digunakan untuk meningkatkan performa dan mengurangi request ke TMDB.

---

# Future Expansion

Arsitektur harus mendukung penambahan domain baru tanpa mengubah struktur utama aplikasi.

Contoh domain:

- User
- Movie
- Diary
- Review
- Watch History
- List
- Recommendation
- Social
- Notification

---

# Architecture Principles

Seluruh pengembangan harus mengikuti prinsip berikut:

- Single Responsibility
- Separation of Concerns
- Low Coupling
- High Cohesion
- Modular Development
- Consistent Structure
- Readability First

---

# Anti Patterns

Hindari:

- Fat Controller
- Business Logic di Blade
- HTTP Request di View
- Query Database di Blade
- Utility Class yang terlalu umum
- Helper yang digunakan untuk seluruh aplikasi tanpa tujuan yang jelas

---

# Architecture Evolution

Arsitektur akan berkembang secara bertahap.

Refactor dilakukan ketika benar-benar memberikan peningkatan maintainability.

Hindari rewrite besar tanpa alasan yang kuat.

---

# End of Document