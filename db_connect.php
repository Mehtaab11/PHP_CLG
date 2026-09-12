<?php
/**
 * db_connect.php
 * -----------------------------------------------
 * Database connection file using PDO.
 * Include this file in any page that needs MySQL.
 * -----------------------------------------------
 */

// ── Database configuration ──────────────────────────────────────────────────
define('DB_HOST', 'localhost');   // XAMPP/WAMP default host
define('DB_NAME', 'netflix_php'); // Database name (create this     in phpMyAdmin)
define('DB_USER', 'root');        // Default XAMPP username
define('DB_PASS', '');            // Default XAMPP password (blank)
define('DB_CHARSET', 'utf8mb4');

// ── PDO options ─────────────────────────────────────────────────────────────
$dsn     = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(503);
    $err_msg = htmlspecialchars($e->getMessage());
    die(<<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Unavailable — NetflixPHP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #141414;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Subtle grid background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(229,9,20,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(229,9,20,.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        .card {
            position: relative;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 3rem 2.5rem 2.5rem;
            max-width: 580px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,.6);
        }

        /* Pulsing icon */
        .icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(229,9,20,.12);
            border: 2px solid rgba(229,9,20,.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.75rem;
            animation: pulse 2.5s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(229,9,20,.35); }
            50%       { box-shadow: 0 0 0 14px rgba(229,9,20,0); }
        }

        .icon-wrap svg { display: block; }

        /* Logo */
        .logo {
            position: absolute;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 1.4rem;
            font-weight: 800;
            color: #E50914;
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .status-code {
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #E50914;
            margin-bottom: .6rem;
        }

        h1 {
            font-size: 1.65rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: .75rem;
            line-height: 1.25;
        }

        .subtitle {
            font-size: .95rem;
            color: #888;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Steps */
        .steps {
            text-align: left;
            background: #111;
            border: 1px solid #222;
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .steps h2 {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #555;
            margin-bottom: .9rem;
        }
        .steps ol {
            padding-left: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: .55rem;
        }
        .steps li {
            font-size: .88rem;
            color: #aaa;
            line-height: 1.5;
        }
        .steps li strong { color: #ddd; }
        .steps code {
            font-family: 'Courier New', monospace;
            font-size: .8rem;
            background: #1e1e1e;
            color: #E50914;
            padding: 1px 6px;
            border-radius: 4px;
        }

        /* Error detail */
        .error-detail {
            text-align: left;
            background: #0d0d0d;
            border: 1px solid #2a2a2a;
            border-left: 3px solid #E50914;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            margin-bottom: 2rem;
        }
        .error-detail h2 {
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #E50914;
            margin-bottom: .5rem;
        }
        .error-detail pre {
            font-family: 'Courier New', monospace;
            font-size: .78rem;
            color: #ccc;
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.5;
        }

        .retry-btn {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #E50914;
            color: #fff;
            font-family: inherit;
            font-size: .9rem;
            font-weight: 600;
            padding: .75rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s, transform .15s;
        }
        .retry-btn:hover { background: #f40612; transform: translateY(-1px); }
        .retry-btn:active { transform: translateY(0); }
    </style>
</head>
<body>
    <div class="card">
        <span class="logo">N</span>

        <div class="icon-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#E50914" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round" width="36" height="36">
                <path d="M12 9v4m0 4h.01"/>
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            </svg>
        </div>

        <p class="status-code">503 &mdash; Service Unavailable</p>
        <h1>Database Connection Failed</h1>
        <p class="subtitle">
            NetflixPHP can&rsquo;t reach MySQL.<br>
            This usually means the database server isn&rsquo;t running.
        </p>

        <div class="steps">
            <h2>How to fix this</h2>
            <ol>
                <li>Open <strong>XAMPP Control Panel</strong> and make sure <code>MySQL</code> is <strong>Running</strong></li>
                <li>Confirm the database <code>netflix_php</code> exists in <strong>phpMyAdmin</strong></li>
                <li>If it doesn&rsquo;t exist, run <code>schema.sql</code> in phpMyAdmin to create it</li>
                <li>Check credentials in <code>db_connect.php</code> (default: user <code>root</code>, blank password)</li>
            </ol>
        </div>

        <div class="error-detail">
            <h2>Error Detail</h2>
            <pre>{$err_msg}</pre>
        </div>

        <a href="javascript:location.reload()" class="retry-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                <path d="M3 3v5h5"/>
            </svg>
            Try Again
        </a>
    </div>
</body>
</html>
HTML);
}
?>
