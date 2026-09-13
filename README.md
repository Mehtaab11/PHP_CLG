# 🎬 NetflixPHP

A Netflix-inspired video browsing and streaming web app built with **PHP**, **MySQL**, and **Vanilla JS** — running locally via XAMPP.

---

## ✨ Features

- 🏠 **Homepage** — Hero banner with the latest video + a responsive video grid
- 🔍 **Search** — Live search across video titles and descriptions
- 🎭 **Genre Filter** — Filter videos by genre (Action, Drama, Sci-Fi, etc.)
- 🎥 **Video Player** — Dedicated player page with YouTube embed + "Up Next" sidebar
- 🗄️ **MySQL Backend** — PDO-powered database with prepared statements
- 🔒 **Secure** — Input sanitization, output escaping, and whitelisted genre filters

---

## 🛠️ Tech Stack

| Layer      | Technology                     |
|------------|-------------------------------|
| Server     | Apache via XAMPP (localhost)   |
| Backend    | PHP 8.x                        |
| Database   | MySQL via phpMyAdmin            |
| DB Driver  | PDO (`ERRMODE_EXCEPTION`)      |
| Frontend   | Vanilla HTML5 + CSS3           |
| JavaScript | Vanilla JS (ES6+)              |
| Fonts      | Google Fonts — Inter           |
| Icons      | Inline SVG                     |

---

## 📁 Project Structure

```
PHP Project/
├── index.php          # Homepage — video grid with genre filter & search
├── player.php         # Video player + Up Next sidebar
├── db_connect.php     # Shared PDO database connection
├── schema.sql         # DB schema + seed data (re-run to reset)
│
├── css/
│   ├── style.css      # Global styles (navbar, hero, grid, footer)
│   └── player.css     # Player-page styles
│
├── js/
│   ├── main.js        # Navbar scroll + search expand animation
│   └── player.js      # Custom player controls
│
├── README.md          # This file
├── ARCHITECTURE.md    # Stable project reference
├── CHANGELOG.md       # Running log of changes
├── DECISIONS.md       # Technical design decisions
└── TODO.md            # Open tasks and known bugs
```

---

## ⚙️ Setup & Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)

### Steps

1. **Clone the repository** into your XAMPP `htdocs` folder:
   ```bash
   git clone https://github.com/Mehtaab11/PHP_CLG.git "c:/xampp/htdocs/netflix_page/PHP Project"
   ```

2. **Start XAMPP** — launch Apache and MySQL from the XAMPP Control Panel.

3. **Create the database** — open [phpMyAdmin](http://localhost/phpmyadmin) and run `schema.sql`:
   ```
   Database → Import → Select schema.sql → Go
   ```
   This creates the `netflix_php` database and seeds it with sample video data.

4. **Visit the app** in your browser:
   ```
   http://localhost/netflix_page/PHP%20Project/index.php
   ```

---

## 🗄️ Database

- **Database name:** `netflix_php`
- **Table:** `videos`

| Column          | Type             | Notes                                  |
|-----------------|------------------|----------------------------------------|
| `id`            | INT UNSIGNED (PK)| Auto-increment primary key             |
| `title`         | VARCHAR(255)     | Video/series display name              |
| `description`   | TEXT             | Full description text                  |
| `thumbnail_url` | VARCHAR(500)     | YouTube `hqdefault.jpg` URL            |
| `video_url`     | VARCHAR(500)     | 11-character YouTube video ID          |
| `duration`      | VARCHAR(20)      | Human-readable string e.g. `2m 28s`   |
| `genre`         | VARCHAR(100)     | One of the allowed genres (see below)  |
| `upload_date`   | DATE             | Original release/upload date           |
| `created_at`    | TIMESTAMP        | Auto-set on insert                     |

**Allowed genres:** `Animation`, `Action`, `Documentary`, `Thriller`, `Comedy`, `Sci-Fi`, `Drama`

---

## 🔒 Security

- All DB queries use **PDO prepared statements** with named parameters
- `$_GET` input validated with `filter_input()` and `FILTER_VALIDATE_INT`
- All output escaped with `htmlspecialchars()` before rendering
- Genre values are whitelisted against `$allowed_genres` before any SQL use

---

## 📄 License

This project is for educational/college purposes. No license is applied.
