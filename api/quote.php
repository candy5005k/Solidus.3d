<?php
declare(strict_types=1);
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

// Use config/database.php for PDO connection
require_once __DIR__ . '/../config/database.php';

function quote_respond(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    quote_respond(false, 'Invalid request.');
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

// Support both JSON body and form POST
if (empty($input)) {
    $input = $_POST;
}

$name           = trim($input['name'] ?? '');
$email          = trim($input['email'] ?? '');
$phone          = trim($input['phone'] ?? '');
$company        = trim($input['company'] ?? '');
$service        = trim($input['service'] ?? '');
$timeline       = trim($input['timeline'] ?? 'Flexible');
$projectDetails = trim($input['project_details'] ?? '');

if (!$name || !$email || !$service || !$projectDetails) {
    quote_respond(false, 'Name, email, service, and project details are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    quote_respond(false, 'Please enter a valid email address.');
}

// ── Auto-register user if not exists ──────────────────────────────────────
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
$stmt->execute([':e' => $email]);
$existingUser = $stmt->fetch();

$userId = null;
$isNewUser = false;
$tempPassword = '';

if ($existingUser) {
    $userId = (int) $existingUser['id'];
    // If already logged in, keep the session; else log them in silently
    if (!isLoggedIn()) {
        $stmt2 = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt2->execute([':id' => $userId]);
        $userRow = $stmt2->fetch();
        if ($userRow) {
            setUserSession($userRow);
        }
    }
} else {
    // Create account with auto-generated temp password
    $tempPassword = ucfirst(substr(str_replace(['+','/','='],'',base64_encode(random_bytes(9))), 0, 8)) . random_int(10, 99);
    $hash = password_hash($tempPassword, PASSWORD_BCRYPT);

    // Name from email if not provided well
    $autoName = $name ?: explode('@', $email)[0];

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (:n, :e, :p, "user")');
    $stmt->execute([':n' => $autoName, ':e' => $email, ':p' => $hash]);
    $userId = (int) $pdo->lastInsertId();
    $isNewUser = true;

    // Log the user in
    setUserSession([
        'id' => $userId, 'name' => $autoName,
        'email' => $email, 'role' => 'user',
    ]);

    // Send welcome email with credentials
    send_welcome_email($email, $autoName, $tempPassword);
}

// ── Save quote ─────────────────────────────────────────────────────────────
$stmt = $pdo->prepare('
    INSERT INTO quotes (user_id, name, email, phone, company, service, timeline, project_details, status)
    VALUES (:uid, :name, :email, :phone, :company, :service, :timeline, :details, "new")
');
$stmt->execute([
    ':uid'     => $userId,
    ':name'    => $name,
    ':email'   => $email,
    ':phone'   => $phone,
    ':company' => $company,
    ':service' => $service,
    ':timeline'=> $timeline,
    ':details' => $projectDetails,
]);
$quoteId = (int) $pdo->lastInsertId();

// ── Send emails ────────────────────────────────────────────────────────────
send_new_quote_admin_email([
    'name'           => $name,
    'email'          => $email,
    'phone'          => $phone,
    'service'        => $service,
    'timeline'       => $timeline,
    'project_details'=> $projectDetails,
]);

send_quote_confirmation_email($email, $name, $service);

// ── Respond ────────────────────────────────────────────────────────────────
quote_respond(true, 'Quote submitted successfully!', [
    'redirect'   => site_url('quote-dashboard.php'),
    'is_new_user'=> $isNewUser,
    'quote_id'   => $quoteId,
]);
