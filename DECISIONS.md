# DECISIONS.md — NetflixPHP

> Why we chose X over Y. Prevents re-litigating settled choices in future sessions.

---

## Database Driver: PDO over mysqli

**Chose:** PDO (`php_pdo_mysql`)  
**Rejected:** `mysqli_*` procedural functions

**Reasons:**
- PDO supports **named parameters** (`:id`, `:genre`) which are cleaner and less error-prone than positional `?` markers
- PDO is **database-agnostic** — if the project ever migrated to PostgreSQL or SQLite, only the DSN string changes
- `ERRMODE_EXCEPTION` makes errors throw catchable `PDOException`, giving proper try/catch control flow
- `ATTR_EMULATE_PREPARES => false` ensures real prepared statements are used at the driver level (stronger injection protection)

---

## Thumbnails: YouTube `hqdefault.jpg` over `maxresdefault.jpg`

**Chose:** `https://img.youtube.com/vi/{id}/hqdefault.jpg`  
**Rejected:** `https://img.youtube.com/vi/{id}/maxresdefault.jpg`

**Reasons:**
- `maxresdefault.jpg` (1280×720) only exists for videos that were uploaded at 720p or higher — roughly 50% of older videos return a 404
- `hqdefault.jpg` (480×360) is **guaranteed to exist** for every public YouTube video
- At card thumbnail sizes (the grid view), the quality difference is imperceptible
- Eliminates the need for complex fallback chains or third-party placeholder services

**Fallback chain if `hqdefault` also fails:**  
`hqdefault` → `mqdefault` (via `onerror` JS in `index.php`)

---

## Video Storage: YouTube IDs over local files

**Chose:** Store only the 11-character YouTube video ID in `video_url`  
**Rejected:** Storing a full URL or actual video files

**Reasons:**
- Local file storage at scale is infeasible for a college project (disk space, streaming overhead)
- Storing just the ID keeps the column clean and lets us construct any YouTube URL pattern at runtime (embed, thumbnail, watch link)
- YouTube handles CDN, bandwidth, and encoding for free

---

## No Frontend Framework (Vanilla HTML/CSS/JS)

**Chose:** Vanilla CSS + ES6 JS  
**Rejected:** Bootstrap, Tailwind, React, Vue

**Reasons:**
- College project objective is to demonstrate understanding of core web technologies
- Vanilla CSS with CSS custom properties (`--var`) gives full design control without fighting a framework's opinions
- Zero build step — files are served directly by Apache with no npm, no bundler, no compilation
- Easier for a grader/reviewer to read and assess

---

## Inline SVG over Icon Libraries

**Chose:** Inline `<svg>` elements  
**Rejected:** Font Awesome, Heroicons CDN

**Reasons:**
- No external CDN dependency — works fully offline (important for localhost demo)
- SVGs can be styled directly with CSS (`fill`, `stroke`, `currentColor`)
- Only the exact icons needed are included — no unused icon font bloat

---

## Genre Filter: SQL `WHERE` over JS Array Filtering

**Chose:** SQL `WHERE genre = :genre` with a DB round-trip  
**Rejected:** Fetching all rows and filtering in PHP/JS

**Reasons:**
- More efficient — only the needed rows are transferred from DB to PHP
- Scales better as the video library grows
- Keeps filtering logic in one authoritative place (the DB) rather than duplicated in frontend and backend
- Consistent with how a real production system would implement this
