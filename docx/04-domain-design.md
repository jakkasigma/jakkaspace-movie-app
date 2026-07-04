# Jakkaspace Domain Design

> Version: 1.0
> Status: Draft
> Last Updated: YYYY-MM-DD

---

# Purpose

Dokumen ini mendefinisikan domain utama dalam Jakkaspace.

Setiap fitur baru harus berada di dalam salah satu domain yang telah ditentukan.

Apabila suatu fitur tidak memiliki domain yang jelas, maka desain fitur tersebut harus dievaluasi kembali.

---

# What is a Domain?

Domain adalah area bisnis yang memiliki tanggung jawab tertentu.

Domain bukan halaman.

Domain bukan folder.

Domain adalah inti dari sebuah fitur.

---

# Core Domains

## User

### Responsibility

Mengelola seluruh data dan identitas pengguna.

### Scope

- Profile
- Authentication
- Preferences
- Account Settings
- Privacy
- Statistics

---

## Movie

### Responsibility

Mengelola seluruh informasi mengenai film.

Movie merupakan sumber informasi, bukan pusat aplikasi.

### Scope

- Detail Movie
- Genres
- Cast
- Crew
- Images
- Videos
- Similar Movies
- Search
- Discover

Movie tidak menyimpan aktivitas pengguna.

---

## Diary

### Responsibility

Mencatat perjalanan menonton pengguna.

Diary merupakan inti utama Jakkaspace.

### Scope

- Watch Log
- Watch Date
- Personal Notes
- Mood
- Rewatch
- Watching Experience

Diary bersifat personal.

---

## Review

### Responsibility

Mengelola opini pengguna terhadap film.

### Scope

- Rating
- Review
- Spoiler
- Edit Review
- Delete Review

Review dapat dibagikan kepada pengguna lain.

---

## Watch History

### Responsibility

Menyimpan riwayat seluruh aktivitas menonton pengguna.

### Scope

- Watched
- Currently Watching
- Rewatch
- Watch Time

---

## Lists

### Responsibility

Mengelola koleksi film milik pengguna.

### Scope

- Favorite
- Watchlist
- Custom Lists

---

## Social

### Responsibility

Mengelola seluruh interaksi antar pengguna.

### Scope

- Follow
- Followers
- Likes
- Comments
- Activity Feed

---

## Recommendation

### Responsibility

Memberikan rekomendasi film kepada pengguna.

### Scope

- Personalized Recommendation
- Similar Movies
- Trending Recommendation

---

## Notification

### Responsibility

Mengelola seluruh pemberitahuan kepada pengguna.

### Scope

- Likes
- Comments
- Followers
- System Notification

---

## Search

### Responsibility

Mengelola pencarian.

### Scope

- Movie Search
- User Search
- List Search

---

# Domain Relationships

User adalah pusat aplikasi.

User dapat:

- Menulis Diary
- Memberi Review
- Membuat List
- Mengikuti User lain
- Menonton Movie
- Menerima Recommendation
- Mendapat Notification

Movie hanya menjadi objek yang dihubungkan dengan aktivitas pengguna.

---

# Domain Dependency Rules

Domain tidak boleh saling bergantung secara berlebihan.

Contoh:

Diary boleh mengetahui Movie.

Movie tidak boleh mengetahui Diary.

Review boleh mengetahui Movie.

Movie tidak boleh mengetahui Review.

Social boleh mengetahui User.

User tidak boleh mengetahui implementasi Social.

---

# Domain Independence

Setiap domain harus dapat dikembangkan secara mandiri.

Perubahan pada satu domain tidak boleh menyebabkan perubahan besar pada domain lain.

---

# Future Domains

Apabila diperlukan, domain baru dapat ditambahkan.

Namun domain baru harus memiliki:

- Tujuan yang jelas.
- Tanggung jawab yang jelas.
- Tidak tumpang tindih dengan domain lain.

---

# Golden Rule

Sebelum membuat fitur baru, tentukan terlebih dahulu:

1. Domain mana yang bertanggung jawab?
2. Apakah domain tersebut memang tempat yang tepat?
3. Apakah fitur tersebut melanggar batas domain lain?

Jika belum dapat dijawab, implementasi sebaiknya ditunda.

---

# End of Document