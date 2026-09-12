# ARCHITECTURE.md — NetflixPHP

> Stable source of truth for the project. Update this when structure or design decisions change — not every session.

---

## Tech Stack

| Layer       | Technology                       | Version / Notes                          |
|-------------|----------------------------------|------------------------------------------|
| Server      | Apache (via XAMPP)               | Localhost only, no deployment target yet |
| Backend     | PHP                              | 8.x (XAMPP bundled)                      |
| Database    | MySQL (via XAMPP / phpMyAdmin)   | Database name: `netflix_php`             |
| DB Driver   | PDO (PHP Data Objects)           | `ERRMODE_EXCEPTION`, `FETCH_ASSOC`       |
| Frontend    | Vanilla HTML5 + CSS3             | No frameworks (Bootstrap, Tailwind, etc.)|
| JavaScript  | Vanilla JS (ES6+)                | No jQuery, no bundler                    |
| Fonts       | Google Fonts — Inter             | Weights: 300, 400, 500, 600, 700, 800    |
| Icons       | Inline SVG only                  | No icon library dependencies             |

---

## Folder / File Structure

```
PHP Project/
├── index.php          # Homepage — video browse grid with genre filter + search
├── player.php         # Video player page — YouTube embed + Up Next sidebar
├── db_connect.php     # PDO connection (shared via require_once)
├── schema.sql         # Full DB schema + INSERT seed data (re-run to reset DB)
│
├── css/
│   ├── style.css      # Global styles (navbar, hero, video grid, footer)
│   └── player.css     # Player-page-only styles (player layout, sidebar, etc.)
│
├── js/
│   ├── main.js        # Navbar scroll behaviour + search expand animation
│   └── player.js      # Custom video player controls (play/pause, seek, volume)
│
├── ARCHITECTURE.md    # This file — stable project reference
├── CHANGELOG.md       # Running log of all changes made
├── TODO.md            # Open tasks and known bugs
└── DECISIONS.md       # Why specific technical choices were made
```

---

## Database Schema

### Database: `netflix_php`

#### Table: `videos`

| Column          | Type                  | Notes                                             |
|-----------------|-----------------------|---------------------------------------------------|
| `id`            | INT UNSIGNED (PK, AI) | Auto-increment primary key                        |
| `title`         | VARCHAR(255)          | Display name of the video/series                  |
| `description`   | TEXT                  | Full description text                             |
| `thumbnail_url` | VARCHAR(500)          | YouTube `hqdefault.jpg` URL (always available)    |
| `video_url`     | VARCHAR(500)          | YouTube video ID (11 chars), e.g. `oOC-4JxqyzM`  |
| `duration`      | VARCHAR(20)           | Human-readable string, e.g. `2m 28s`             |
| `genre`         | VARCHAR(100)          | One of the allowed genres (see below)             |
| `upload_date`   | DATE                  | Original release/upload date                      |
| `created_at`    | TIMESTAMP             | Auto-set on insert                                |

**Allowed genres:** `Animation`, `Action`, `Documentary`, `Thriller`, `Comedy`, `Sci-Fi`, `Drama`

> `video_url` stores only the **11-character YouTube video ID**.  
> The embed URL is constructed at runtime: `https://www.youtube.com/embed/{video_url}`  
> Thumbnails are fetched from: `https://img.youtube.com/vi/{video_url}/hqdefault.jpg`

---

## Page Flow

```
User visits index.php
  ├── No params      → Show hero banner (first video) + all videos grid
  ├── ?genre=X       → Filter grid by genre, no hero
  └── ?search=X      → Search title/description, no hero

User clicks a card → player.php?id=X
  ├── Valid ID       → Show YouTube embed player + Up Next sidebar (same genre)
  └── Invalid ID     → Redirect to index.php
  └── Not found      → 404 page
```

---

## Naming Conventions

- **PHP files:** `snake_case.php` (e.g. `db_connect.php`, `player.php`)
- **CSS classes:** `kebab-case` (e.g. `card-thumb`, `video-grid`, `genre-pill`)
- **JS variables:** `camelCase` (e.g. `videoId`, `searchInput`)
- **SQL columns:** `snake_case` (e.g. `thumbnail_url`, `upload_date`)
- **HTML IDs:** `kebab-case` (e.g. `hero-banner`, `video-grid`, `hero-play-btn`)
- **Genre values:** Title Case (e.g. `Sci-Fi`, `Thriller`) — must match `$allowed_genres` in `index.php`

---

## Security Practices

- All DB queries use **PDO prepared statements** with named parameters (`:id`, `:genre`, `:search`)
- `$_GET` input sanitized with `filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)`
- All output escaped with `htmlspecialchars()` before rendering
- Genre filter whitelisted against `$allowed_genres` array before use in SQL
