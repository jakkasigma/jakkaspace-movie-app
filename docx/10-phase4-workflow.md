# Phase 4 — Community: Workflow

> Created: 2026-07-03
> Status: Ready to Execute
> Prerequisite: Phase 0 ✅ Phase 1 ✅ Phase 2 ✅ Phase 3 ✅

---

## Tujuan Phase 4

Pengguna bisa saling berinteraksi — follow satu sama lain, lihat profil publik, melihat aktivitas orang yang di-follow, dan berinteraksi dengan review (likes & comments).

---

## Fitur yang Akan Dibangun

| Fitur | Deskripsi |
|---|---|
| Public Profile (`/@username`) | Halaman profil publik — diary, review, lists milik user lain |
| Follow / Unfollow | Ikuti atau berhenti ikuti user lain |
| Activity Feed | Lihat aktivitas terbaru dari orang yang di-follow |
| Review Page Publik | Halaman detail satu review dengan komentar |
| Likes pada Review | Tombol like di review |
| Likes pada Diary | Tombol like di diary entry publik |
| Comments pada Review | Komentar di halaman review |

---

## Yang Perlu Dibuat dari Nol

### Tabel baru:

```
follows          — follower_id, following_id
review_likes     — user_id, review_id
diary_likes      — user_id, diary_entry_id
review_comments  — user_id, review_id, body
activities       — user_id, type, subject_type, subject_id, data (JSON)
```

---

## Urutan Pengerjaan

### Langkah 1 — Migrasi & Model

**Migrasi:**
```
php artisan make:migration create_follows_table
php artisan make:migration create_review_likes_table
php artisan make:migration create_diary_likes_table
php artisan make:migration create_review_comments_table
```

**Skema tabel:**

`follows`:
- `follower_id` — FK ke users
- `following_id` — FK ke users
- Unique constraint: `(follower_id, following_id)`

`review_likes`:
- `user_id` — FK ke users
- `review_id` — FK ke reviews
- Unique constraint: `(user_id, review_id)`

`diary_likes`:
- `user_id` — FK ke users
- `diary_entry_id` — FK ke diary_entries
- Unique constraint: `(user_id, diary_entry_id)`

`review_comments`:
- `user_id` — FK ke users
- `review_id` — FK ke reviews
- `body` — text, max 1000 karakter

**Model yang dibuat:**
```
app/Models/Follow.php
app/Models/ReviewLike.php
app/Models/DiaryLike.php
app/Models/ReviewComment.php
```

**Update User model — tambah relasi:**
```php
following()    — BelongsToMany users via follows (follower_id → following_id)
followers()    — BelongsToMany users via follows (following_id → follower_id)
reviewLikes()  — HasMany ReviewLike
diaryLikes()   — HasMany DiaryLike
```

**Update Review model — tambah relasi:**
```php
likes()     — HasMany ReviewLike
comments()  — HasMany ReviewComment
```

**Update DiaryEntry model — tambah relasi:**
```php
likes()  — HasMany DiaryLike
```

---

### Langkah 2 — ProfileService

**File:** `app/Services/User/ProfileService.php`

```php
getPublicProfile(string $username): ?User
getProfileStats(User $user): array       — total watched, reviews, lists publik, followers, following
getProfileDiary(User $user): Collection  — diary publik, paginated
getProfileReviews(User $user): Collection
getProfileLists(User $user): Collection  — hanya list publik
isFollowing(User $from, User $to): bool
getFollowing(User $user): Collection
getFollowers(User $user): Collection
```

---

### Langkah 3 — FollowService

**File:** `app/Services/User/FollowService.php`

```php
follow(User $follower, User $target): void
unfollow(User $follower, User $target): void
isFollowing(User $follower, User $target): bool
```

---

### Langkah 4 — InteractionService

**File:** `app/Services/User/InteractionService.php`

```php
likeReview(User $user, Review $review): void
unlikeReview(User $user, Review $review): void
isReviewLiked(User $user, Review $review): bool
likeDiary(User $user, DiaryEntry $entry): void
unlikeDiary(User $user, DiaryEntry $entry): void
isDiaryLiked(User $user, DiaryEntry $entry): bool
addComment(User $user, Review $review, string $body): ReviewComment
deleteComment(User $user, ReviewComment $comment): void
```

---

### Langkah 5 — ActivityService (update)

**File:** `app/Services/User/ActivityFeedService.php`

```php
getFeed(User $user, int $limit = 20): Collection
```

Feed berisi aktivitas dari semua user yang di-follow:
- Diary baru
- Review baru
- Film ditambah ke watchlist/favorites
- List baru dibuat

> Catatan: Activity tidak disimpan ke DB. Feed di-build dari query ke tabel-tabel yang ada, sorted by `created_at`. Simpel dan tidak butuh tabel `activities` dulu.

---

### Langkah 6 — Controllers

```
app/Http/Controllers/ProfileController.php    — sudah ada, perlu update
app/Http/Controllers/FollowController.php     — baru
app/Http/Controllers/ReviewPageController.php — baru (public review page)
app/Http/Controllers/ReviewLikeController.php — baru
app/Http/Controllers/DiaryLikeController.php  — baru
app/Http/Controllers/ReviewCommentController.php — baru
app/Http/Controllers/ActivityFeedController.php  — baru
```

**ProfileController** (update method `show` atau buat `public()`):
```
GET /@{username}  → public profile
```

**FollowController:**
```
POST   /users/{user}/follow    → follow
DELETE /users/{user}/follow    → unfollow
GET    /@{username}/followers  → halaman followers
GET    /@{username}/following  → halaman following
```

**ReviewPageController:**
```
GET /reviews/{review}  → halaman publik satu review + comments + likes
```

**ReviewLikeController:**
```
POST   /reviews/{review}/like  → like
DELETE /reviews/{review}/like  → unlike
```

**DiaryLikeController:**
```
POST   /diary/{entry}/like   → like
DELETE /diary/{entry}/like   → unlike
```

**ReviewCommentController:**
```
POST   /reviews/{review}/comments          → tambah komentar
DELETE /reviews/{review}/comments/{comment} → hapus komentar
```

**ActivityFeedController:**
```
GET /feed  → activity feed (auth required)
```

---

### Langkah 7 — Routes

```php
// Public — no auth
Route::get('/@{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/@{username}/followers', [FollowController::class, 'followers'])->name('profile.followers');
Route::get('/@{username}/following', [FollowController::class, 'following'])->name('profile.following');
Route::get('/reviews/{review}', [ReviewPageController::class, 'show'])->name('reviews.show');

// Auth required
Route::middleware('auth')->group(function () {
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');

    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'store'])->name('reviews.like.store');
    Route::delete('/reviews/{review}/like', [ReviewLikeController::class, 'destroy'])->name('reviews.like.destroy');

    Route::post('/diary/{entry}/like', [DiaryLikeController::class, 'store'])->name('diary.like.store');
    Route::delete('/diary/{entry}/like', [DiaryLikeController::class, 'destroy'])->name('diary.like.destroy');

    Route::post('/reviews/{review}/comments', [ReviewCommentController::class, 'store'])->name('reviews.comments.store');
    Route::delete('/reviews/{review}/comments/{comment}', [ReviewCommentController::class, 'destroy'])->name('reviews.comments.destroy');

    Route::get('/feed', [ActivityFeedController::class, 'index'])->name('feed');
});
```

---

### Langkah 8 — Form Requests

```
app/Http/Requests/ReviewCommentRequest.php
```

Rules:
- `body`: required, string, min 1, max 1000

---

### Langkah 9 — Views

```
resources/views/profile/show.blade.php         — profil publik
resources/views/profile/followers.blade.php    — list followers
resources/views/profile/following.blade.php    — list following
resources/views/reviews/show.blade.php         — halaman review publik + comments
resources/views/feed/index.blade.php           — activity feed
```

**Profile show** menampilkan:
- Avatar, nama, username, bio
- Stats: ditonton, review, lists, followers, following
- Tab: Diary / Reviews / Lists (publik)
- Tombol Follow/Unfollow (kalau bukan profil sendiri)

**Review show** menampilkan:
- Info film (poster, judul, tahun)
- Review lengkap (rating, body, spoiler warning)
- Tombol like
- Kolom komentar

**Feed** menampilkan:
- Stream aktivitas dari following
- Tiap item: avatar user + aksi + link film
- Contoh: "jakka menambahkan Interstellar ke watchlist · 2 jam lalu"

---

### Langkah 10 — Update Navbar

Tambah link ke **Feed** di navbar utama (hanya kalau user login).

---

### Langkah 11 — Update Movie Detail

Di halaman `/movies/{id}`, di bawah daftar review, tampilkan link ke review page publik tiap review yang ada.

---

### Langkah 12 — Tests

```
tests/Feature/ProfileTest.php
tests/Feature/FollowTest.php
tests/Feature/ReviewPageTest.php
tests/Feature/ReviewLikeTest.php
tests/Feature/DiaryLikeTest.php
tests/Feature/ReviewCommentTest.php
tests/Feature/ActivityFeedTest.php
```

---

## Urutan Eksekusi Final

```
1. Migrasi + Model (Follow, ReviewLike, DiaryLike, ReviewComment)
2. Update relasi User, Review, DiaryEntry
3. ProfileService
4. FollowService
5. InteractionService
6. ActivityFeedService
7. Form Request (ReviewCommentRequest)
8. Controllers + Routes
9. Views (profile, review page, feed)
10. Update navbar
11. Update movie detail (link ke review page)
12. Tests
13. CSS (profile, feed, review page, follow button)
```

---

## Catatan Arsitektur

- **Tidak ada tabel `activities`** — feed di-build secara live dari query. Lebih simpel untuk skala sekarang.
- **Privacy**: diary entry dan review dari akun `is_private = true` tidak muncul di profil publik dan feed, kecuali untuk followers.
- **Komentar**: tidak nested — flat list saja.
- **Like count**: pakai `withCount('likes')` di query, tidak disimpan sebagai kolom terpisah.
- **Self-follow prevention**: `FollowService::follow()` harus cek bahwa `follower_id !== following_id`.
