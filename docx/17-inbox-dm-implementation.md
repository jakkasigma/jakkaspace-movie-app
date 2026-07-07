# Phase 6.5 — Inbox DM Implementation: Progress & Workflow

> Created: 2026-07-05
> Status: In Progress (partial)
> Prerequisite: Phase 6 ✅

---

## Visi

Inbox chat real-time ala Instagram DM — immersive, mobile-first, dengan contact discovery, unread badges, dan live message delivery via Laravel Reverb.

---

## Status Progress

| Komponen | Status | Keterangan |
|---|---|---|
| **Database & model** | ✅ Done | conversations + messages + conversation_members, pivot `last_read_at` |
| **InboxService** | ✅ Done | CRUD, unread count, mark as read, find/create conversation |
| **InboxController** | ✅ Done | index(), show(), startDirect(), store() |
| **Routes** | ✅ Done | `inbox.direct` (GET\|POST), `inbox.show`, `inbox.messages.store` |
| **View inbox/index.blade.php** | ✅ Done | Instagram-style contacts + conversations + unread badge |
| **View inbox/show.blade.php** | ✅ Done | Chat room + date separator + Echo listener |
| **CSS inbox** | ✅ Done | mobile full-screen, bubble, date sep, nav badge, unread |
| **Desktop navbar** | ✅ Done | INBOX link + unread badge |
| **Hiding navbar/footer** | ✅ Done | `body.inbox-chat-room` hides layout |
| **Reverb real-time** | ✅ Done | `laravel/reverb`, `laravel-echo`, `pusher-js`, Echo listener |
| **MessageSent event** | ✅ Done | Broadcast to private channel, `.toOthers()` |
| **Username fix** | ✅ Done | `@` escaping di Blade |
| **Tests** | ✅ Partial | 19 tests (index, show, send, unread, contacts) |

---

## Flow Aplikasi

### 1. Inbox Index (`/inbox`)

```
GET /inbox
    ↓
InboxController@index
    ↓
InboxService@getConversations($user)
    — semua conversation user + latest message + unread count
    ↓
InboxService@getFollowingWithCounts($user)
    — berikutnya untuk contact list (siapa yang bisa di-DM)
    ↓
View: inbox/index.blade.php
    Section: "Pesan" (conversations existing)
    Section: "Kontak" (following — link Mulai)
```

### 2. Mulai Chat Baru

```
Klik "Mulai" di kontak → GET /inbox/direct?user_id=X
    ↓
InboxController@startDirect
    ↓
InboxService@findOrCreateDirect($user, $other)
    — cek apakah conversation sudah ada
    — kalau belum, create baru + add members + set last_read_at untuk sender
    — kalau sudah, langsung buka
    ↓
Redirect ke inbox/show/{conversation}
```

### 3. Chat Room (`/inbox/{conversation}`)

```
GET /inbox/{conversation}
    ↓
InboxController@show
    ↓
InboxService@findConversation($user, $conversationId)
    — ambil messages dengan pagination
    — panggil markAsRead()
    ↓
View: inbox/show.blade.php
    — Semua pesan dengan date separator
    — Input form kirim pesan
    — Echo listener (real-time dari orang lain)
```

### 4. Kirim Pesan

```
POST /inbox/{conversation}/messages
    body: { message: "..." } atau { film_id: 123 }
    ↓
InboxController@store
    ↓
InboxService@sendText atau sendFilmShare
    — Simpan message
    — Broadcast MessageSent via Reverb (.toOthers)
    ↓
Response: JSON sukses
    ↓
Client-side: prepend bubble ke DOM (milik sendiri)
    ↓
Echo listener (penerima): otomatis inject bubble
```

---

## Real-Time Architecture

```
┌──────────────┐     Reverb WebSocket     ┌──────────────┐
│  User A      │◄────────────────────────►│  User B      │
│  (sender)    │                          │  (receiver)  │
└──────┬───────┘                          └──────┬───────┘
       │                                         │
       │ POST /inbox/X/messages                   │
       ▼                                         │
┌──────────────┐                                 │
│  Laravel App │                                 │
│  store message│                                │
│  broadcast    │                                │
│  MessageSent  │──── Reverb ───────────────────►│ Echo.listen(
│  .toOthers()  │                                │   chat.{id},
│              │                                │   .MessageSent
│              │                                │ )
└──────────────┘                                │
                                                ▼
                                         ┌──────────────┐
                                         │  Inject DOM  │
                                         │  (bubble +   │
                                         │   date sep)  │
                                         └──────────────┘
```

### Private Channel Authorization

File `routes/channels.php`:
```php
Broadcast::channel('chat.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::where('id', $conversationId)
        ->whereHas('members', fn ($q) => $q->where('user_id', $user->id))
        ->exists();
});
```

---

## Layout Strategy

### Halaman Inbox Index

- Pakai `layouts/movie.blade.php` (navbar di desktop, bottom nav di mobile)
- Navbar: link INBOX aktif + unread badge merah
- Bottom nav: tetap tampil

### Halaman Chat Room (`inbox/{conversation}`)

- Pakai `layouts/movie.blade.php`
- **Navbar di-sembunyikan** via CSS:
  ```css
  body.inbox-chat-room .navbar { display: none; }
  body.inbox-chat-room .navbar-placeholder { display: none; }
  body.inbox-chat-room footer { display: none; }
  body.inbox-chat-room .bottom-nav { display: none; }
  ```
- Header chat sendiri: back button + avatar + nama (bukan navbar)
- Full-screen chat bubble

### CSS: `inbox-chat-page` vs `inbox-chat-room`

```css
body.inbox-chat-page .bottom-nav { display: none; }
/* ini seharusnya inbox-chat-room — perlu diperbaiki kalau ada bug */
```

---

## Unread Count System

### Database

Pivot `conversation_members` punya kolom `last_read_at`:
- Di-update setiap kali user buka conversation
- Default `now()` saat pertama join

### Hitung Unread

```sql
SELECT COUNT(*) FROM messages m
WHERE m.conversation_id = ?
  AND m.user_id != ?
  AND m.created_at > (
      SELECT cm.last_read_at FROM conversation_members cm
      WHERE cm.conversation_id = m.conversation_id
        AND cm.user_id = ?
  )
```

### Implementasi di InboxService

`getUnreadCount($user, $conversation)`:
```php
$member = $conversation->members()
    ->where('user_id', $user->id)
    ->first();

return $conversation->messages()
    ->where('user_id', '!=', $user->id)
    ->where('created_at', '>', $member->pivot->last_read_at ?? now())
    ->count();
```

`getConversations()` menambahkan `unread_count` via subquery `withCount`:
```php
$sub = Message::whereColumn('conversation_id', 'conversations.id')
    ->where('user_id', '!=', $user->id)
    ->whereNotExists(function ($q) use ($user) {
        $q->select(DB::raw(1))
          ->from('conversation_members')
          ->whereColumn('conversation_id', 'conversations.id')
          ->where('user_id', $user->id)
          ->whereRaw('messages.created_at > conversation_members.last_read_at');
    });

$conversations = $user->conversations()
    ->withCount(['messages as unread_count' => fn ($q) => $q->where('user_id', '!=', $user->id)->whereNotExists(...)])
    ...
```

### Navbar Badge

Total unread di semua conversation — di-share via `AppServiceProvider`:
```php
view()->composer('components.movie.navbar', function ($view) {
    $totalUnread = 0;
    if (Auth::check()) {
        $totalUnread = app(InboxService::class)->getTotalUnreadCount(Auth::user());
    }
    $view->with('inboxUnreadCount', $totalUnread);
});
```

---

## Message Types

### Type: `text`

```json
{
    "id": 1,
    "type": "text",
    "body": "Halo, nonton film apa hari ini?",
    "user_id": 1,
    "created_at": "2026-07-05T10:00:00Z"
}
```

### Type: `film_share`

```json
{
    "id": 2,
    "type": "film_share",
    "body": "Inception",
    "tmdb_id": 27205,
    "user_id": 1,
    "created_at": "2026-07-05T10:01:00Z"
}
```

---

## Date Separator Logic

Di view `inbox/show.blade.php`:

```php
$currentDate = null;
foreach ($messages as $message) {
    $messageDate = $message->created_at->format('Y-m-d');
    if ($messageDate !== $currentDate) {
        $currentDate = $messageDate;
        // Tampilkan separator
        if ($message->created_at->isToday()) {
            echo "Hari Ini";
        } elseif ($message->created_at->isYesterday()) {
            echo "Kemarin";
        } else {
            echo $message->created_at->format('d/m/Y');
        }
    }
    // Tampilkan bubble
}
```

---

## Responsive & Mobile Strategy

### Desktop (> 768px)

```
┌──────────────────────────────────────────────┐
│  navbar                                       │
├───────────────────┬──────────────────────────┤
│  Contacts/Conv    │  Chat Room               │
│  (sidebar 350px)  │  (flex-1)               │
│                   │                          │
│  • Avatar + Name  │  ← Kembali              │
│  • Last message   │  Avatar + Nama          │
│  • Unread badge   │  ┌──────────────────┐   │
│                   │  │ Bubble chat      │   │
│  ————————         │  │                  │   │
│  Kontak           │  └──────────────────┘   │
│  • User 1  [Mulai]│                         │
│  • User 2  [Mulai]│  [Input] [Kirim]        │
└───────────────────┴──────────────────────────┘
```

### Mobile (< 768px)

**Index:**
```
┌──────────────────┐
│ Navbar           │
├──────────────────┤
│ 🔍 Cari pesan   │
├──────────────────┤
│ 💬 Pesan         │
│ ┌──────────────┐│
│ │ Avatar + Nama││
│ │ Last msg     ││
│ └──────────────┘│
│ ┌──────────────┐│
│ │ Avatar + Nama││
│ │ Last msg     ││
│ └──────────────┘│
├──────────────────┤
│ 👥 Kontak        │
│ ┌──────┐ ┌──────┐│
│ │Avatar│ │Avatar││
│ │Nama  │ │Nama  ││
│ │Mulai │ │Mulai ││
│ └──────┘ └──────┘│
├──────────────────┤
│ Bottom Nav       │
└──────────────────┘
```

**Chat Room:**
```
┌──────────────────┐
│ ← Kembali  Avatar│
│            Nama  │
├──────────────────┤
│                  │
│  ┌──────────┐    │
│  │ Bubble    │    │
│  └──────────┘    │
│                  │
│     ┌──────────┐ │
│     │   Bubble  │ │
│     └──────────┘ │
│                  │
│  ── Hari Ini ──  │
│                  │
│  ┌──────────┐    │
│  │ Bubble    │    │
│  └──────────┘    │
│                  │
├──────────────────┤
│ [Input] [Kirim]  │
└──────────────────┘
```

---

## Yang Perlu Dikerjakan / Diperbaiki

1. ✅ Real-time Echo listener sudah di `inbox/show.blade.php`
2. ✅ Film share bubble — kirim `tmdb_id`, tampilkan poster + judul
3. ⬜ Film share di Echo listener — DOM injection untuk film share masih perlu handle khusus
4. ⬜ Scroll ke bawah otomatis saat pesan baru masuk (Echo listener)
5. ⬜ Typing indicator (opsional — stretch goal)
6. ⬜ Read receipts / "Dilihat" timestamp (opsional — stretch goal)
7. ⬜ Group list conversation (phase lanjutan)
8. ⬜ Delete conversation (opsional)
9. ⬜ Image share (opsional — stretch goal)

---

