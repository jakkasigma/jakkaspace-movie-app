# Notifikasi — Workflow

> Created: 2026-07-03
> Status: Ready to Execute
> Prerequisite: Phase 4 ✅ Phase 4B ✅

---

## Tujuan

User mendapat notifikasi in-app ketika ada aktivitas yang relevan dengan mereka — ada yang follow, like review, like diary, atau komentar di review.

---

## Trigger Notifikasi

| Event | Penerima | Pesan |
|---|---|---|
| User A follow User B | User B | "jakka mulai mengikutimu" |
| User A like review User B | User B | "jakka menyukai reviewmu — Film X" |
| User A like diary User B | User B | "jakka menyukai diary entry-mu" |
| User A comment di review User B | User B | "jakka berkomentar: 'setuju banget...'" |

> Tidak kirim notifikasi ke diri sendiri.

---

## Arsitektur

Gunakan **Laravel Database Notifications** — sudah built-in, tidak perlu package tambahan.

- Tabel `notifications` dibuat via `php artisan notifications:table`
- Notifikasi disimpan sebagai JSON di kolom `data`
- Dibaca via `$user->notifications` dan `$user->unreadNotifications`
- User model sudah punya trait `Notifiable` ✅

**Tidak pakai email/push** — hanya in-app (database notification).

---

## Yang Perlu Dibuat

### 1 — Migration

```bash
php artisan notifications:table
php artisan migrate
```

### 2 — Notification Classes

```
app/Notifications/NewFollower.php
app/Notifications/ReviewLiked.php
app/Notifications/DiaryLiked.php
app/Notifications/ReviewCommented.php
```

Semua via `database` channel saja. Tiap class punya method `toDatabase()` yang return array data:

```php
// NewFollower
[
    'type' => 'follow',
    'actor_id' => $follower->id,
    'actor_name' => $follower->name,
    'actor_username' => $follower->username,
    'actor_avatar' => $follower->avatar_url,
]

// ReviewLiked
[
    'type' => 'review_like',
    'actor_id' => $liker->id,
    'actor_name' => $liker->name,
    'actor_username' => $liker->username,
    'actor_avatar' => $liker->avatar_url,
    'review_id' => $review->id,
    'tmdb_id' => $review->tmdb_id,
]

// DiaryLiked
[
    'type' => 'diary_like',
    'actor_id' => $liker->id,
    'actor_name' => $liker->name,
    'actor_username' => $liker->username,
    'actor_avatar' => $liker->avatar_url,
    'diary_entry_id' => $entry->id,
    'tmdb_id' => $entry->tmdb_id,
]

// ReviewCommented
[
    'type' => 'review_comment',
    'actor_id' => $commenter->id,
    'actor_name' => $commenter->name,
    'actor_username' => $commenter->username,
    'actor_avatar' => $commenter->avatar_url,
    'review_id' => $review->id,
    'comment_preview' => Str::limit($body, 80),
    'tmdb_id' => $review->tmdb_id,
]
```

### 3 — Dispatch di Services

Update 3 service yang sudah ada:

**`FollowService::follow()`** — dispatch `NewFollower` ke `$target`

**`InteractionService::likeReview()`** — dispatch `ReviewLiked` ke `$review->user` (kalau bukan diri sendiri)

**`InteractionService::likeDiary()`** — dispatch `DiaryLiked` ke `$entry->user` (kalau bukan diri sendiri)

**`InteractionService::addComment()`** — dispatch `ReviewCommented` ke `$review->user` (kalau bukan diri sendiri)

### 4 — NotificationController

```
app/Http/Controllers/NotificationController.php
```

Methods:
```
index()   → GET /notifications — halaman semua notifikasi
markRead() → POST /notifications/{notification}/read — tandai satu sebagai baca
markAllRead() → POST /notifications/read-all — tandai semua sebagai baca
```

### 5 — Routes

```php
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
```

### 6 — View

```
resources/views/notifications/index.blade.php
```

Tampilkan semua notifikasi, yang belum dibaca di atas dengan highlight. Klik notifikasi → tandai sebagai baca + redirect ke halaman relevan.

### 7 — Navbar: Notification Bell

Di navbar, tambah icon lonceng 🔔 untuk user yang login. Kalau ada notif belum dibaca, tampil badge merah dengan jumlahnya.

Karena ini server-rendered (bukan SPA), badge count diambil dari `auth()->user()->unreadNotifications->count()` — di-pass lewat view composer atau langsung di component.

```
resources/views/components/movie/notification-bell.blade.php
```

Component sederhana:
```blade
@auth
    <a href="{{ route('notifications') }}" class="nav-notif-btn">
        🔔
        @if ($unreadCount > 0)
            <span class="nav-notif-badge">{{ $unreadCount }}</span>
        @endif
    </a>
@endauth
```

### 8 — View Composer (optional tapi rapi)

Daftarkan di `AppServiceProvider` agar `$unreadCount` tersedia di semua view yang include navbar:

```php
View::composer('components.movie.navbar', function ($view) {
    $view->with('unreadCount', auth()->check()
        ? auth()->user()->unreadNotifications()->count()
        : 0
    );
});
```

### 9 — Tests

```
tests/Feature/NotificationTest.php
```

Cover:
- Follow → notif terkirim ke target
- Like review → notif terkirim ke pemilik review
- Like diary → notif terkirim ke pemilik diary
- Comment → notif terkirim ke pemilik review
- Self-action tidak kirim notif
- Mark as read berfungsi
- Mark all read berfungsi

---

## Urutan Eksekusi

```
1. php artisan notifications:table + migrate
2. 4 Notification classes
3. Update FollowService + InteractionService (dispatch notif)
4. NotificationController + routes
5. View notifications/index.blade.php
6. Component notification-bell + update navbar
7. View Composer di AppServiceProvider
8. CSS (bell, badge, notif list)
9. Tests
10. Pint
```

---

## Catatan

- **Auto-cleanup**: notifikasi lama (> 30 hari) bisa dihapus via scheduled command nanti (Phase 6)
- **Limit**: tampilkan maksimal 50 notifikasi terbaru di halaman notifikasi
- **No real-time**: update badge hanya saat page refresh — tidak pakai WebSocket/Pusher untuk sekarang
