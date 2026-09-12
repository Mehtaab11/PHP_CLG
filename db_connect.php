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
    die('
        <div style="
            font-family: Arial, sans-serif;
            background:#141414;
            color:#E50914;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            flex-direction:column;
            gap:12px;
        ">
            <h2>⚠ Database Connection Failed</h2>
            <p style="color:#aaa;">Check your MySQL server and credentials in db_connect.php</p>
            <code style="color:#fff;background:#1a1a1a;padding:8px 16px;border-radius:6px;">'
                . htmlspecialchars($e->getMessage()) .
            '</code>
        </div>
    ');
}
?>
