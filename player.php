<?php
/**
 * player.php
 * -----------------------------------------------
 * Video Player Page
 * - Reads ?id=X from URL (safely via prepared stmt)
 * - Displays HTML5 <video> player with custom JS controls
 * - Shows title, description, genre, duration
 * - Shows "Up Next" related videos from same genre
 * -----------------------------------------------
 */

require_once 'db_connect.php';

// ── 1. Validate & sanitize the video ID ──────────────────────────────────────
$video_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Redirect to home if ID is invalid or missing
if ($video_id === false || $video_id === null || $video_id <= 0) {
    header('Location: index.php');
    exit;
}

// ── 2. Fetch the requested video (prepared statement) ────────────────────────
try {
    $stmt = $pdo->prepare(
        "SELECT id, title, description, thumbnail_url, video_url, duration, genre, upload_date
           FROM videos
          WHERE id = :id
          LIMIT 1"
    );
    $stmt->execute([':id' => $video_id]);
    $video = $stmt->fetch();

} catch (PDOException $e) {
    $video = null;
}

// ── 3. Handle video not found ────────────────────────────────────────────────
if (!$video) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Video Not Found — NetflixPHP</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="not-found-page">
            <div class="not-found-content">
                <span class="not-found-code">404</span>
                <h1 class="not-found-title">Video Not Found</h1>
                <p class="not-found-msg">
                    The video you're looking for (ID: <?= htmlspecialchars((string)$video_id) ?>)
                    doesn't exist or has been removed.
                </p>
                <a href="index.php" class="btn-play">← Back to Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ── 4. Fetch "Up Next" related videos (same genre, exclude current) ──────────
try {
    $rel_stmt = $pdo->prepare(
        "SELECT id, title, thumbnail_url, duration, genre
           FROM videos
          WHERE genre = :genre
            AND id  != :current_id
          ORDER BY upload_date DESC
          LIMIT 6"
    );
    $rel_stmt->execute([
        ':genre'      => $video['genre'],
        ':current_id' => $video_id,
    ]);
    $related = $rel_stmt->fetchAll();

    // If not enough related from same genre, top up with other videos
    if (count($related) < 4) {
        $extra_ids   = array_column($related, 'id');
        $extra_ids[] = $video_id;
        $placeholders = implode(',', array_fill(0, count($extra_ids), '?'));
        $extra_stmt  = $pdo->prepare(
            "SELECT id, title, thumbnail_url, duration, genre
               FROM videos
              WHERE id NOT IN ($placeholders)
              ORDER BY RAND()
              LIMIT 4"
        );
        $extra_stmt->execute($extra_ids);
        $related = array_merge($related, $extra_stmt->fetchAll());
    }

} catch (PDOException $e) {
    $related = [];
}

// ── 5. Format upload date nicely ─────────────────────────────────────────────
$upload_formatted = date('F j, Y', strtotime($video['upload_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Watch <?= htmlspecialchars($video['title']) ?> — <?= htmlspecialchars(substr($video['description'], 0, 120)) ?>">
    <title><?= htmlspecialchars($video['title']) ?> — NetflixPHP</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/player.css">
</head>
<body class="player-body">

<!-- ════════════════════════════════════════════
     NAVIGATION BAR (minimal on player page)
════════════════════════════════════════════ -->
<nav class="navbar navbar--player" id="navbar">
    <div class="nav-left">
        <a href="index.php" class="logo" aria-label="Back to Home">
            <svg viewBox="0 0 111 30" class="logo-svg" aria-hidden="true">
                <path d="M105.062 14.28L111 30c-1.75-.25-3.499-.563-5.28-.845l-3.345-8.686-3.437 7.969c-1.687-.282-3.344-.376-5.031-.595l6.031-13.75L94.468 0h5.063l3.062 7.874L105.875 0h5.124l-5.937 14.28zM90.47 0h-4.965v27.498c1.62.094 3.312.156 5.062.343V0H90.47zM81.13 0H76.19v24.376c1.656.186 3.312.406 4.937.624V0h.003zM71.757 6.44L71.915 0H67.07v29.032c1.657.25 3.283.531 4.94.78l-.003-17.06c1.468 2.062 4.565 6.75 7.064 10.093.843-.156 1.687-.28 2.53-.406L77.22 15.65 71.757 6.44zM.033 0h4.967l6.59 18.5V0H16.6v27.498c-1.686.187-3.344.375-5.0.593L4.97 9.562V28c-1.657.22-3.344.44-4.937.689V0z" fill="#E50914"/>
            </svg>
        </a>
        <a href="index.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Browse
        </a>
    </div>
    <!-- Breadcrumb -->
    <div class="nav-breadcrumb" aria-label="breadcrumb">
        <span><?= htmlspecialchars($video['genre']) ?></span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="m9 18 6-6-6-6"/></svg>
        <span class="breadcrumb-current"><?= htmlspecialchars($video['title']) ?></span>
    </div>
</nav>

<!-- ════════════════════════════════════════════
     PLAYER LAYOUT (two-column on desktop)
════════════════════════════════════════════ -->
<div class="player-layout">

    <!-- ── LEFT: Main Player Column ──────────────────────────────────────── -->
    <div class="player-main" id="player-column">

        <!-- ── Custom HTML5 Video Player ─────────────────────────────────── -->
        <div class="player-wrapper" id="player-wrapper">

            <!-- Native HTML5 video element -->
            <video
                id="main-video"
                class="main-video"
                preload="metadata"
                poster="<?= htmlspecialchars($video['thumbnail_url']) ?>"
                playsinline
                aria-label="<?= htmlspecialchars($video['title']) ?>"
            >
                <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                <!-- Fallback message for browsers that don't support HTML5 video -->
                <p>Your browser does not support HTML5 video.
                   <a href="<?= htmlspecialchars($video['video_url']) ?>">Download the video</a>.
                </p>
            </video>

            <!-- ── Custom Control Bar ──────────────────────────────────── -->
            <div class="custom-controls" id="custom-controls" aria-label="Video controls">

                <!-- Progress / Seek Bar -->
                <div class="progress-container" id="progress-container" role="slider"
                     aria-label="Seek bar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"
                     tabindex="0">
                    <div class="progress-buffered" id="progress-buffered"></div>
                    <div class="progress-played"   id="progress-played"></div>
                    <div class="progress-thumb"    id="progress-thumb"></div>
                    <!-- Time tooltip on hover -->
                    <div class="seek-tooltip" id="seek-tooltip">0:00</div>
                </div>

                <!-- Controls row -->
                <div class="controls-row">
                    <!-- Left controls -->
                    <div class="controls-left">
                        <!-- Play / Pause -->
                        <button class="ctrl-btn" id="play-pause-btn" aria-label="Play">
                            <svg id="play-icon" viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                                <polygon points="5,3 19,12 5,21"/>
                            </svg>
                            <svg id="pause-icon" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" style="display:none">
                                <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                            </svg>
                        </button>

                        <!-- Skip back 10s -->
                        <button class="ctrl-btn" id="skip-back-btn" aria-label="Rewind 10 seconds">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.56"/>
                            </svg>
                            <span class="skip-label">10</span>
                        </button>

                        <!-- Skip forward 10s -->
                        <button class="ctrl-btn" id="skip-fwd-btn" aria-label="Forward 10 seconds">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.49-3.56"/>
                            </svg>
                            <span class="skip-label">10</span>
                        </button>

                        <!-- Volume control -->
                        <div class="volume-group">
                            <button class="ctrl-btn" id="mute-btn" aria-label="Mute">
                                <svg id="vol-icon" viewBox="0 0 24 24" fill="currentColor" width="22" height="22">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>
                                </svg>
                                <svg id="mute-icon" viewBox="0 0 24 24" fill="currentColor" width="22" height="22" style="display:none">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                    <line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/>
                                </svg>
                            </button>
                            <input type="range" id="volume-slider" class="volume-slider"
                                   min="0" max="1" step="0.05" value="1"
                                   aria-label="Volume">
                        </div>

                        <!-- Time display -->
                        <span class="time-display" id="time-display" aria-live="off">
                            <span id="current-time">0:00</span>
                            <span class="time-sep"> / </span>
                            <span id="total-time">0:00</span>
                        </span>
                    </div>

                    <!-- Right controls -->
                    <div class="controls-right">
                        <!-- Playback speed -->
                        <div class="speed-group">
                            <button class="ctrl-btn speed-btn" id="speed-btn" aria-label="Playback speed" aria-haspopup="listbox">
                                <span id="speed-label">1×</span>
                            </button>
                            <ul class="speed-menu" id="speed-menu" role="listbox" aria-label="Playback speed options" hidden>
                                <?php foreach ([0.5, 0.75, 1, 1.25, 1.5, 2] as $s): ?>
                                <li class="speed-option <?= $s == 1 ? 'active' : '' ?>"
                                    role="option"
                                    data-speed="<?= $s ?>"
                                    tabindex="0"
                                    aria-selected="<?= $s == 1 ? 'true' : 'false' ?>">
                                    <?= $s ?>×
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- Picture-in-Picture -->
                        <button class="ctrl-btn" id="pip-btn" aria-label="Picture in Picture">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <rect x="12" y="11" width="9" height="7" rx="1" fill="currentColor" stroke="none"/>
                            </svg>
                        </button>

                        <!-- Fullscreen -->
                        <button class="ctrl-btn" id="fullscreen-btn" aria-label="Fullscreen">
                            <svg id="expand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                                <polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/>
                                <line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/>
                            </svg>
                            <svg id="collapse-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22" style="display:none">
                                <polyline points="4 14 10 14 10 20"/><polyline points="20 10 14 10 14 4"/>
                                <line x1="10" y1="14" x2="3" y2="21"/><line x1="21" y1="3" x2="14" y2="10"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div><!-- /custom-controls -->

            <!-- Big center play/pause click overlay -->
            <div class="center-click-overlay" id="center-overlay" aria-hidden="true">
                <div class="center-ripple" id="center-ripple"></div>
            </div>
        </div><!-- /player-wrapper -->

        <!-- ── Video Metadata Below Player ────────────────────────────────── -->
        <div class="video-meta-section" id="video-meta">
            <div class="meta-top">
                <div class="meta-badges">
                    <span class="badge badge--genre"><?= htmlspecialchars($video['genre']) ?></span>
                    <span class="badge badge--duration">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <?= htmlspecialchars($video['duration']) ?>
                    </span>
                    <span class="badge badge--date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        </svg>
                        <?= htmlspecialchars($upload_formatted) ?>
                    </span>
                </div>
                <!-- Share-like actions (UI only for the project) -->
                <div class="meta-actions">
                    <button class="action-btn" id="like-btn" aria-label="Like this video" title="Like">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3H14z"/>
                            <path d="M7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/>
                        </svg>
                        <span id="like-count">0</span>
                    </button>
                    <button class="action-btn" id="watchlist-btn" aria-label="Add to watchlist" title="Watchlist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        My List
                    </button>
                </div>
            </div>

            <h1 class="video-title" id="video-title-heading"><?= htmlspecialchars($video['title']) ?></h1>
            <p class="video-description"><?= nl2br(htmlspecialchars($video['description'])) ?></p>
        </div>

    </div><!-- /player-main -->

    <!-- ── RIGHT: Up Next / Related Videos Sidebar ──────────────────────── -->
    <aside class="player-sidebar" id="player-sidebar" aria-label="Related videos">
        <h2 class="sidebar-title">
            Up Next
            <span class="sidebar-genre-tag"><?= htmlspecialchars($video['genre']) ?></span>
        </h2>

        <?php if (!empty($related)): ?>
        <div class="related-list" id="related-list">
            <?php foreach ($related as $rel): ?>
            <a href="player.php?id=<?= (int)$rel['id'] ?>"
               class="related-card"
               id="related-<?= (int)$rel['id'] ?>"
               aria-label="Watch <?= htmlspecialchars($rel['title']) ?>">

                <div class="related-thumb-wrap">
                    <img
                        src="<?= htmlspecialchars($rel['thumbnail_url']) ?>"
                        alt="<?= htmlspecialchars($rel['title']) ?> thumbnail"
                        class="related-thumb"
                        loading="lazy"
                        onerror="this.src='https://via.placeholder.com/320x180/1a1a1a/E50914?text=No+Image'"
                    >
                    <div class="related-play-overlay">
                        <svg viewBox="0 0 24 24" fill="white" width="22" height="22">
                            <polygon points="5,3 19,12 5,21"/>
                        </svg>
                    </div>
                    <span class="related-duration"><?= htmlspecialchars($rel['duration']) ?></span>
                </div>

                <div class="related-info">
                    <p class="related-title"><?= htmlspecialchars($rel['title']) ?></p>
                    <span class="related-genre"><?= htmlspecialchars($rel['genre']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <p class="no-related">No related videos found.</p>
        <?php endif; ?>

    </aside><!-- /player-sidebar -->

</div><!-- /player-layout -->

<!-- ════════════════════════════════════════════
     FOOTER
════════════════════════════════════════════ -->
<footer class="footer footer--player">
    <p class="footer-text">NetflixPHP — PHP &amp; MySQL College Project</p>
    <p class="footer-sub">&copy; <?= date('Y') ?> For educational purposes only</p>
</footer>

<!-- ════════════════════════════════════════════
     JAVASCRIPT — Custom Video Controls
════════════════════════════════════════════ -->
<script src="js/main.js"></script>
<script src="js/player.js"></script>
</body>
</html>
