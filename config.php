<?php
// ═══════════════════════════════════════════════════════════════
// config.php — Database + Email + Site Settings
// PLACE THIS FILE AT: public_html/api/config.php
//
// ⚠️  NEVER commit this file to GitHub (add to .gitignore)
// ═══════════════════════════════════════════════════════════════

// ─────────────────────────────────────
// HOSTINGER MySQL CREDENTIALS
// Find these in: hPanel → Databases → MySQL Databases
// ─────────────────────────────────────
define('DB_HOST', 'localhost');          // Always localhost on Hostinger
define('DB_NAME', 'u123456789_leads');   // YOUR database name from hPanel
define('DB_USER', 'u123456789_leads');   // YOUR database username from hPanel
define('DB_PASS', 'YourStrongPassword'); // YOUR database password

// ─────────────────────────────────────
// EMAIL SETTINGS
// Leads notification goes to this email
// ─────────────────────────────────────
define('NOTIFY_EMAIL',   'leads@azaleakalyani.com');  // Where YOU get lead alerts
define('FROM_EMAIL',     'noreply@azaleakalyani.com'); // Sender email (must be your domain)
define('FROM_NAME',      'AZALEA — BKR Lifespaces');
define('SUPPORT_PHONE',  '+91 8484846556');

// ─────────────────────────────────────
// ALLOWED ORIGINS (CORS)
// Add every domain that will call this API
// ─────────────────────────────────────
define('ALLOWED_ORIGINS', [
    'https://azaleakalyani.com',
    'https://www.azaleakalyani.com',
    'https://propnmore.com',
    'https://www.propnmore.com',
    'http://localhost:5500',   // VS Code Live Server (dev only)
    'http://127.0.0.1:5500',
]);

// ─────────────────────────────────────
// SITE IDENTIFIER
// This tells the API which site is calling
// Override per site if needed
// ─────────────────────────────────────
define('DEFAULT_SITE', 'azalea');

// ─────────────────────────────────────
// DATABASE CONNECTION (PDO)
// Used by all API files — just include config.php
// ─────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }
    }
    return $pdo;
}

// ─────────────────────────────────────
// CORS HEADERS
// Call this at top of every API file
// ─────────────────────────────────────
function setCorsHeaders(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, ALLOWED_ORIGINS)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');

    // Preflight request — browsers send this before POST
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ─────────────────────────────────────
// SANITIZE INPUT
// ─────────────────────────────────────
function clean(string $val): string {
    return trim(strip_tags($val));
}
