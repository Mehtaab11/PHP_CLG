# CHANGELOG.md — NetflixPHP

> A running, dated log of every meaningful change. Newest entries at the top.
> **Rule:** After any AI-assisted session, paste a summary here before closing.

---

## 2026-09-12

### Added
- `ARCHITECTURE.md` — stable tech stack + schema reference
- `CHANGELOG.md` — this file
- `TODO.md` — open tasks and known bugs tracker
- `DECISIONS.md` — rationale for key technical choices

### Fixed
- **Broken thumbnails (critical):** `maxresdefault.jpg` only exists for ~50% of YouTube videos, causing broken images on roughly half the cards. Switched all `thumbnail_url` values in `schema.sql` to `hqdefault.jpg`, which is guaranteed to exist for every YouTube video.
  - Affected entries: all 12 rows in the `videos` table
  - Fix location: `schema.sql` (must be re-run in phpMyAdmin to apply)
- **`onerror` fallback in `index.php`:** replaced broken `via.placeholder.com` fallback with a YouTube-native fallback (`hqdefault` → `mqdefault`) so no external placeholder service is required

### Changed
- `schema.sql` updated from v2 to v3 (comment header updated to reflect thumbnail change)

---

## 2026-09-10 (estimated — project creation)

### Created
- `schema.sql` — initial `videos` table with 12 seed rows (real YouTube trailer IDs)
  - Genres: Drama, Thriller, Sci-Fi, Action, Comedy, Documentary
- `db_connect.php` — PDO connection with `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, emulated prepares off
- `index.php` — Netflix-style homepage with:
  - Hero banner (first video in DB)
  - Video card grid with hover effects
  - Genre filter nav with badge counts
  - Search bar (title + description `LIKE` query)
  - Responsive layout
- `player.php` — video player page with:
  - YouTube `<iframe>` embed (constructed from stored video ID)
  - `filter_input(FILTER_VALIDATE_INT)` for `?id=` param
  - 404 page for missing/invalid IDs
  - "Up Next" sidebar (same genre, excludes current video)
- `css/style.css` — global stylesheet (navbar, hero, grid, cards, footer)
- `css/player.css` — player-page-specific styles
- `js/main.js` — navbar scroll + search expand behaviour
- `js/player.js` — custom player UI controls
