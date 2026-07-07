# Your Space — Phase 1 & 2 + Rating Scale Change

## Phase 1: Data Completeness

### Migration
- `add_movie_title_to_diary_entries_table` — kolom `movie_title` di `diary_entries`

### SpaceService — Method Baru
- `getRecentDiaryEntries()` — 5 diary terbaru buat dashboard
- `getRecentReviews()` — 5 review terbaru buat dashboard
- `getWatchlistInfo()` — count + avg rating
- `getFavoritesInfo()` — count
- `getDiarySummaryStats()` — total entries + monthly avg
- `getHistorySummaryStats()` — total hours watched
- Filter & sort params di `getDiaryEntries()` (year, sort)
- Attach poster URL + rating ke Diary & WatchHistory entries
- Auto-save `movie_title` ke DB saat fetch dari TMDB

### Controller
- `SpaceController` — semua method update + `editDiary()` / `updateDiary()`

### Routes
- `GET /your-space/diary/{entry}/edit` → `SpaceController@editDiary`
- `PUT /your-space/diary/{entry}` → `SpaceController@updateDiary`

### Dashboard
- Stats: `total_favorites`, `estimated_hours`
- Section: Diary Terbaru (poster + rating + mood)
- Section: Review Terbaru (stars + body preview)

### Diary Page
- Poster film, rating bintang, filter tahun, sort, edit button

### History Page
- Group by month (Letterboxd-style), poster, rating, total jam

### Cleanup
- Hapus `resources/views/your-space.blade.php` (legacy)

---

## Phase 2: Visual Refresh

### Sticky Nav
- `.space-nav` → `position: sticky; top: 92px; backdrop-filter: blur(12px)`
- Active link → lime accent (`#a3e635`)

### Animations
- `@keyframes spaceFadeIn` — page load
- Staggered `spaceSectionFadeIn` per section
- Card hover → `translateY(-2px)` + lime border glow + shadow

### Empty States
- Component `<x-space.empty icon="..." />`
- SVG icons: film, book, heart, list, clock
- Dashed border, centered, hover effect

### Typography & Accents
- Section title → accent bar (`::before`)
- Page title underline (`::after`)
- Stat value → lime on hover
- `space-section-link` → lime hover + translateX

---

## Rating Scale: 10 → 5

### 17 files changed:

| File | Change |
|------|--------|
| `ReviewRequest.php` | `max:10` → `max:5` |
| `ReviewFactory.php` | `numberBetween(1,10)` → `(1,5)` |
| `AnalyticsService.php` | `array_fill(1,10,0)` → `(1,5,0)` |
| `ExportController.php` (4x) | `Rating/10` → `Rating/5` |
| `space/index.blade.php` (2x) | `10 - $rating` → `5 - $rating` |
| `space/diary.blade.php` | `10 - $rating` → `5 - $rating` |
| `space/history.blade.php` | `10 - $rating` → `5 - $rating` |
| `space/analytics.blade.php` (2x) | `/10` → `/5`, bar calc `/10` → `/5` |
| `tab-info.blade.php` | Rating picker 1-10 → 1-5 |
| `welcome.css` | Button 42px→48px, active green→lime |
| `profile/show.blade.php` | `/10` → `/5` |
| `reviews/show.blade.php` | `/10` → `/5` |
| `feed/index.blade.php` | `/10` → `/5` |
| `AnalyticsTest.php` | avg calc: 8+6=7.0 → 4+3=3.5 |
| `ReviewPageTest.php` | `9/10` → `4/5` |
| `MovieDetailTabsTest.php` (5x) | 6/7/8/9 → 3/4/5 |

---

## Bug Fixes

### Error: `Unknown column 'movie_poster_url'`
- **Cause**: Dynamic attributes set before `updateQuietly()` — Eloquent saved all dirty attrs to DB
- **Fix**: Moved dynamic attr assignment after the save

### Issue: `movie_title` not saved on diary create
- **Cause**: `DiaryService::createEntry()` didn't fetch title from TMDB
- **Fix**: Injected `MovieService`, fetch & save title on create

### Issue: Mood validation inconsistency
- **Cause**: `DiaryEntryRequest` used enum validation, `SpaceController@updateDiary` used free text
- **Fix**: Relaxed `DiaryEntryRequest.mood` to `nullable|string|max:50`
