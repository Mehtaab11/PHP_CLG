<?php
/**
 * index.php
 * -----------------------------------------------
 * Netflix-style Homepage — Browse Videos Grid
 * Fetches video thumbnails from MySQL and renders
 * a responsive card grid with hover effects.
 * -----------------------------------------------
 */

require_once 'db_connect.php'; // Load PDO connection ($pdo)

// ── Genre Filter (optional bonus feature) ────────────────────────────────────
$allowed_genres = ['Animation', 'Action', 'Documentary', 'Thriller', 'Comedy', 'Sci-Fi'];
$selected_genre = '';

if (!empty($_GET['genre']) && in_array($_GET['genre'], $allowed_genres)) {
    $selected_genre = $_GET['genre'];
}

// ── Search Query (optional bonus feature) ────────────────────────────────────
$search = '';
if (!empty($_GET['search'])) {
    $search = trim($_GET['search']); // Sanitize whitespace
}

// ── Fetch Videos from Database ───────────────────────────────────────────────
try {
    if ($search !== '') {
        // Search by title or description using prepared statement
        $stmt = $pdo->prepare(
            "SELECT id, title, description, thumbnail_url, duration, genre, upload_date
               FROM videos
              WHERE title LIKE :search OR description LIKE :search
              ORDER BY upload_date DESC"
        );
        $stmt->execute([':search' => '%' . $search . '%']);

    } elseif ($selected_genre !== '') {
        // Filter by genre using prepared statement
        $stmt = $pdo->prepare(
            "SELECT id, title, description, thumbnail_url, duration, genre, upload_date
               FROM videos
              WHERE genre = :genre
              ORDER BY upload_date DESC"
        );
        $stmt->execute([':genre' => $selected_genre]);

    } else {
        // Fetch all videos, newest first
        $stmt = $pdo->query(
            "SELECT id, title, description, thumbnail_url, duration, genre, upload_date
               FROM videos
              ORDER BY upload_date DESC"
        );
    }

    $videos = $stmt->fetchAll();

} catch (PDOException $e) {
    $videos   = [];
    $db_error = 'Database error: ' . htmlspecialchars($e->getMessage());
}

// ── Genre counts for nav badges ───────────────────────────────────────────────
try {
    $genre_stmt  = $pdo->query("SELECT genre, COUNT(*) as cnt FROM videos GROUP BY genre ORDER BY genre");
    $genre_counts = $genre_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['Action' => 3, ...]
} catch (PDOException $e) {
    $genre_counts = [];
}

$page_title = $search
    ? 'Search: ' . htmlspecialchars($search) . ' — NetflixPHP'
    : ($selected_genre ? $selected_genre . ' — NetflixPHP' : 'NetflixPHP — Home');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A Netflix-style video streaming page built with PHP and MySQL for a college project.">
    <title><?= htmlspecialchars($page_title) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ════════════════════════════════════════════
     NAVIGATION BAR
════════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
    <div class="nav-left">
        <a href="index.php" class="logo" aria-label="NetflixPHP Home">
            <!-- Text-based logo: bold N + brand name -->
            <span class="logo-text" aria-hidden="true">
                <span class="logo-n">N</span>
            </span>
        </a>

        <!-- Genre filter links -->
        <ul class="nav-genres" role="list">
            <li>
                <a href="index.php"
                   class="genre-link <?= $selected_genre === '' && $search === '' ? 'active' : '' ?>">
                    All
                </a>
            </li>
            <?php foreach ($allowed_genres as $g): ?>
            <li>
                <a href="index.php?genre=<?= urlencode($g) ?>"
                   class="genre-link <?= $selected_genre === $g ? 'active' : '' ?>">
                    <?= htmlspecialchars($g) ?>
                    <?php if (!empty($genre_counts[$g])): ?>
                        <span class="genre-badge"><?= $genre_counts[$g] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Search Bar -->
    <div class="nav-right">
        <form action="index.php" method="GET" class="search-form" role="search">
            <div class="search-wrapper">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    id="search-input"
                    name="search"
                    class="search-input"
                    placeholder="Search titles..."
                    value="<?= htmlspecialchars($search) ?>"
                    autocomplete="off"
                    aria-label="Search videos"
                >
            </div>
        </form>
    </div>
</nav>

<!-- ════════════════════════════════════════════
     HERO BANNER (shown on homepage only)
════════════════════════════════════════════ -->
<?php if ($search === '' && $selected_genre === '' && !empty($videos)): ?>
<?php $hero = $videos[0]; // First video becomes the hero ?>
<section class="hero" id="hero-banner"
    style="--hero-thumb: url('<?= htmlspecialchars($hero['thumbnail_url']) ?>')">

    <div class="hero-overlay"></div>
    <div class="hero-content">
        <span class="hero-badge">
            <svg viewBox="0 0 24 24" fill="#E50914" width="14" height="14"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            New Release
        </span>
        <h1 class="hero-title"><?= htmlspecialchars($hero['title']) ?></h1>
        <p class="hero-desc"><?= htmlspecialchars(substr($hero['description'], 0, 150)) ?>...</p>
        <div class="hero-meta">
            <span class="hero-tag"><?= htmlspecialchars($hero['genre']) ?></span>
            <span class="hero-tag"><?= htmlspecialchars($hero['duration']) ?></span>
        </div>
        <div class="hero-actions">
            <a href="player.php?id=<?= (int)$hero['id'] ?>" class="btn-play" id="hero-play-btn">
                <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><polygon points="5,3 19,12 5,21"/></svg>
                Play Now
            </a>
            <button class="btn-info" onclick="window.location='player.php?id=<?= (int)$hero['id'] ?>'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                More Info
            </button>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ════════════════════════════════════════════
     MAIN CONTENT — VIDEO GRID
════════════════════════════════════════════ -->
<?php
/* Add extra top padding when hero is NOT shown (genre filter or search active) */
$no_hero = ($search !== '' || $selected_genre !== '' || empty($videos));
?>
<main class="main-content" id="main-content"
      <?= $no_hero ? 'style="padding-top: calc(var(--navbar-h) + 1.5rem);"' : '' ?>>

    <!-- Section heading -->
    <div class="section-header">
        <h2 class="section-title">
            <?php if ($search !== ''): ?>
                🔍 Results for "<span class="highlight"><?= htmlspecialchars($search) ?></span>"
            <?php elseif ($selected_genre !== ''): ?>
                <?= htmlspecialchars($selected_genre) ?> Films
            <?php else: ?>
                All Videos
            <?php endif; ?>
        </h2>
        <span class="video-count"><?= count($videos) ?> title<?= count($videos) !== 1 ? 's' : '' ?></span>
    </div>

    <!-- Database error message (if any) -->
    <?php if (!empty($db_error)): ?>
        <div class="alert alert-error" role="alert">
            ⚠ <?= $db_error ?>
        </div>
    <?php endif; ?>

    <!-- No results message -->
    <?php if (empty($videos) && empty($db_error)): ?>
        <div class="empty-state" role="status">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="64" height="64">
                <path d="m15 15 6 6m-11-4a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
            </svg>
            <p>No videos found<?= $search ? ' for "' . htmlspecialchars($search) . '"' : '' ?>.</p>
            <a href="index.php" class="btn-play" style="font-size:.85rem;padding:.6rem 1.4rem;">Browse All</a>
        </div>

    <?php else: ?>
    <!-- ── Video Card Grid ───────────────────────────────── -->
    <div class="video-grid" id="video-grid">
        <?php foreach ($videos as $video): ?>
        <article class="video-card" id="card-<?= (int)$video['id'] ?>">
            <a href="player.php?id=<?= (int)$video['id'] ?>"
               class="card-link"
               aria-label="Watch <?= htmlspecialchars($video['title']) ?>">

                <!-- Thumbnail -->
                <div class="card-thumb-wrapper">
                    <img
                        src="<?= htmlspecialchars($video['thumbnail_url']) ?>"
                        alt="<?= htmlspecialchars($video['title']) ?> thumbnail"
                        class="card-thumb"
                        loading="lazy"
                        onerror="this.onerror=null;this.src=this.src.replace('hqdefault','mqdefault')"
                    >
                    <!-- Play overlay -->
                    <div class="card-overlay">
                        <div class="play-circle">
                            <svg viewBox="0 0 24 24" fill="white" width="32" height="32">
                                <polygon points="5,3 19,12 5,21"/>      
                            </svg>
                        </div>
                    </div>
                    <!-- Duration badge -->
                    <span class="duration-badge"><?= htmlspecialchars($video['duration']) ?></span>
                    <!-- Genre badge -->
                    <span class="genre-pill"><?= htmlspecialchars($video['genre']) ?></span>
                </div>

                <!-- Card info -->
                <div class="card-info">
                    <h3 class="card-title"><?= htmlspecialchars($video['title']) ?></h3>
                    <p class="card-desc">
                        <?= htmlspecialchars(substr($video['description'], 0, 90)) ?>...
                    </p>
                    <div class="card-meta">
                        <time datetime="<?= htmlspecialchars($video['upload_date']) ?>">
                            <?= date('M Y', strtotime($video['upload_date'])) ?>
                        </time>
                    </div>
                </div>
            </a>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</main>

<!-- ════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════ -->
<footer class="footer">
    <div class="footer-logo">
        <!-- Text logo in footer -->
        <span class="logo-text logo-text--footer" aria-label="NetflixPHP">
            <span class="logo-n">N</span>
        </span>
    </div>
    <p class="footer-text">
        College PHP &amp; MySQL Project 
    </p>
    <p class="footer-credit">
        Made by <strong>Mehaab</strong>
    </p>
    <p class="footer-sub">
        &copy; <?= date('Y') ?> NetflixPHP &nbsp;|&nbsp; For educational purposes only
    </p>
</footer>

<!-- ════════════════════════════════════════════
     JAVASCRIPT — Navbar scroll + search expand
════════════════════════════════════════════ -->
<script src="js/main.js"></script>
</body>
</html>
