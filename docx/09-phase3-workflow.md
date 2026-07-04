# Phase 3 — Movie Collection: Workflow

> Created: 2026-07-03
> Status: Ready to Execute
> Prerequisite: Phase 0 ✅ Phase 1 ✅ Phase 2 ✅

---

## Tujuan Phase 3

Pengguna bisa membuat koleksi film pribadi dengan nama dan deskripsi sendiri, memilih apakah list bisa dilihat publik atau privat, dan menambah/hapus film dari list langsung di halaman movie detail.

---

## Yang Sudah Tersedia

| Tersedia | Keterangan |
|---|---|
| Tabel `movie_lists` | user_id, name, description, is_public |
| Tabel `list_movies` | movie_list_id, tmdb_id, sort_order |
| Model `MovieList` + `ListMovie` | relasi, factory sudah ada |
| User relasi `movieLists()` | sudah ada di User model |

---

## Urutan Pengerjaan

### Langkah 1 — MovieListService

**File:** `app/Services/User/MovieListService.php`

```
createList(User $user, array $data): MovieList
updateList(MovieList $list, array $data): MovieList
deleteList(MovieList $list): void
getUserLists(User $user): Collection
addMovie(MovieList $list, int $tmdbId): ListMovie
removeMovie(MovieList $list, int $tmdbId): void
isMovieInList(MovieList $list, int $tmdbId): bool
getMoviesInList(MovieList $list): array   ← fetch dari TMDB + cache
```

---

### Langkah 2 — Form Request

```
app/Http/Requests/MovieListRequest.php
```

Rules:
- `name`: required, string, max 100
- `description`: nullable, string, max 500
- `is_public`: boolean

---

### Langkah 3 — MovieListController

```
app/Http/Controllers/MovieListController.php
```

Methods:
```
index()    → halaman semua list user (/your-space/lists)
create()   → form buat list baru
store()    → simpan list baru
show()     → halaman detail list (/lists/{list})
edit()     → form edit list
update()   → update list
destroy()  → hapus list
```

---

### Langkah 4 — ListMovieController

```
app/Http/Controllers/ListMovieController.php
```

Methods:
```
store(MovieList $list, int $movie)   → tambah film ke list
destroy(MovieList $list, int $movie) → hapus film dari list
```

---

### Langkah 5 — Routes

```
// Auth required
GET    /your-space/lists              → MovieListController@index
GET    /your-space/lists/create       → MovieListController@create
POST   /your-space/lists              → MovieListController@store
GET    /your-space/lists/{list}/edit  → MovieListController@edit
PUT    /your-space/lists/{list}       → MovieListController@update
DELETE /your-space/lists/{list}       → MovieListController@destroy

POST   /lists/{list}/movies/{movie}   → ListMovieController@store
DELETE /lists/{list}/movies/{movie}   → ListMovieController@destroy

// Public (no auth needed)
GET    /lists/{list}                  → MovieListController@show
```

---

### Langkah 6 — Update Movie Detail

Di halaman `/movies/{id}`, kalau user sudah login, tambah dropdown "Tambah ke List" yang menampilkan semua list milik user. Bisa tambah/hapus dari list langsung.

---

### Langkah 7 — Views

```
resources/views/space/lists/index.blade.php   — semua list user
resources/views/space/lists/create.blade.php  — form buat list
resources/views/space/lists/edit.blade.php    — form edit list
resources/views/lists/show.blade.php          — halaman publik list
```

---

### Langkah 8 — Update Space Nav

Tambah tab "Lists" ke space nav dan tab bar mobile.

---

### Langkah 9 — Tests

```
tests/Feature/MovieListTest.php
tests/Feature/ListMovieTest.php
```

---

## Urutan Eksekusi Final

```
1. MovieListService
2. MovieListRequest
3. MovieListController + routes
4. ListMovieController + routes
5. Views (index, create, edit, show)
6. Update movie detail — "Tambah ke List"
7. Update space nav + tab bar
8. Tests
```
