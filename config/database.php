<?php
declare(strict_types=1);
/**
 * config/database.php
 * Just establishes $pdo using constants already defined in includes/config.php.
 * Does NOT re-define DB_HOST / DB_NAME etc. — those live in includes/config.php.
 */

// If included directly (e.g. from api/quote.php before config.php loads), pull config in.
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../includes/config.php';
}

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('Solidus 3D DB connection failed: ' . $e->getMessage());
    $pdo = null;
}
