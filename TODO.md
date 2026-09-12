# TODO.md — NetflixPHP

> Open tasks, known bugs, and deliberately deferred work.
> Mark items `[x]` when done and move them to CHANGELOG.md.

---

## 🐛 Known Bugs

- [ ] **Thumbnails require DB refresh** — after the `hqdefault.jpg` fix in `schema.sql`, the database must be manually re-run in phpMyAdmin to take effect. Existing rows still have `maxresdefault.jpg` until then.
- [ ] **Hero banner uses `thumbnail_url` as CSS background** — if that image is slow to load or blocked, the hero shows a blank/dark area with no fallback. The `onerror` JS fix on `<img>` tags does not apply to CSS `background-image`.

---

## 🔧 Improvements / Nice-to-Haves

- [ ] **Admin panel** — a simple password-protected page to add/edit/delete videos from the DB via a form instead of phpMyAdmin
- [ ] **Pagination** — currently loads all videos at once; add `LIMIT`/`OFFSET` or infinite scroll for when the DB has many rows
- [ ] **Responsive nav** — genre links in the navbar overflow on small screens; needs a hamburger/dropdown for mobile
- [ ] **Hero fallback image** — add a CSS `background-color` or a `<img>` fallback for the hero banner in case the thumbnail fails to load
- [ ] **Loading skeleton** — show skeleton cards while images load for a more polished feel
- [ ] **Active genre in `<title>`** — player page title could include the video title (it may already, verify)

---

## 🚫 Deliberately Deferred

- **User authentication / login** — out of scope for this college project
- **Video upload** — files stored as YouTube IDs only; actual file hosting is out of scope
- **HTTPS / production deployment** — localhost only for now
- **Multiple genres per video** — schema stores one genre per video; many-to-many is over-engineered for this scope
