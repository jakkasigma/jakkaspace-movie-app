# Phase 2 — Your Space: Visual Refresh

Goal: Polish UI agar tampil lebih serius, konsisten, dan engaging.

## Task List

### Task 1 — Sticky Sidebar Nav
- `.space-nav` → `position: sticky; top: 92px; height: fit-content;`
- `.space-page` → `display: flex; gap: 0;` dengan `.space-body` sebagai `flex: 1`

### Task 2 — Animations & Transitions
- Fade-in on page load (`.space-page` → subtle `opacity` animation)
- Card hover: scale/translateY subtle
- Section header: subtle border-bottom accent color
- Smooth scroll behavior

### Task 3 — Consistent Card Design
- Unified border-radius, padding, background untuk semua card
- Diary card, history item, list card, mini card dashboard semua pakai sistem yang sama
- Hover state: brighten border, slight background lighten

### Task 4 — Better Empty States
- Custom SVG illustrations per page (diary, history, watchlist, favorites, lists)
- Better copy + CTA button styling

### Task 5 — Typography Hierarchy
- Consistent `font-family`, `font-size`, `color` scale
- Section titles vs body text vs metadata
- Better spacing between sections

### Task 6 — Color Accents
- Active nav link: accent color (lime-300?)
- Section divider: gradient instead of solid line
- Stat values: accent color on hover
