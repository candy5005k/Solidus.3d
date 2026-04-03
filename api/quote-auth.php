<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

function jsonResponse($ok, $msg, $extra=[]) {
    echo json_encode(array_merge(['success'=>$ok, 'message'=>$msg], $extra));
    exit;
}

if ($action === 'send_otp') {
    $email = trim($input['email'] ?? '');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Invalid work email address.');
    }
    
    // Check if user exists. If not, we will create an un-passworded user upon verification.
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();
    
    $userId = 0; // Means pending user creation
    if ($user) {
        $userId = $user['id'];
    }
    
    // Generate 6 digit OTP
    $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Set previous pending user OTPs to used if any exist for this email loosely
    // In our otp_codes table, we might need a way to support email-based OTP. 
    // Since otp_codes maps to user_id, if new user, we might insert a fake user_id or add an email column.
    
    // Alternatively, just inject a temporary user struct if they don't exist:
    if (!$userId) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:n, :e, :p)');
        $stmt->execute([':n' => 'Instant Lead', ':e' => $email, ':p' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT)]);
        $userId = $pdo->lastInsertId();
    }
    
    // Mark old as used
    $stmt = $pdo->prepare('UPDATE otp_codes SET used=1 WHERE user_id=:uid AND used=0');
    $stmt->execute([':uid' => $userId]);
    
    // Insert new
    $stmt = $pdo->prepare('INSERT INTO otp_codes (user_id, code, expires_at) VALUES (:uid, :code, :exp)');
    $stmt->execute([':uid' => $userId, ':code' => $code, ':exp' => $expiresAt]);
    
    $subject = 'Solidus 3D — Instant Quote OTP';
    $htmlBody = "
    <div style='font-family:sans-serif;max-width:400px;margin:20px auto;border:1px solid #e5e7eb;border-radius:8px;padding:20px;'>
        <h2 style='margin-top:0'>Instant Quote Login</h2>
        <p>Your one-time password (OTP) is:</p>
        <div style='background:#f1f5f9;padding:12px;font-size:24px;letter-spacing:4px;font-weight:bold;text-align:center;border-radius:6px;margin:20px 0;'>{$code}</div>
        <p style='color:#6b7280;font-size:13px;'>Expires in 15 minutes.</p>
    </div>";
    
    send_email($email, $subject, $htmlBody);
    
    // For local dev where mail() fails, we just don't abort. 
    error_log("OTP for $email: $code");
    
    jsonResponse(true, 'OTP sent to your email.');
}

if ($action === 'verify_otp') {
    $email = trim($input['email'] ?? '');
    $code = trim($input['otp'] ?? '');
    
    if (!$email || !$code) jsonResponse(false, 'Email and OTP required.');
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();
    
    if (!$user) jsonResponse(false, 'Session expired or email invalid.');
    
    $stmt = $pdo->prepare('SELECT id FROM otp_codes WHERE user_id=:uid AND code=:code AND used=0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute([':uid'=>$user['id'], ':code'=>$code]);
    $otp = $stmt->fetch();
    
    if (!$otp) jsonResponse(false, 'Invalid or expired OTP.');
    
    // Mark used
    $pdo->prepare('UPDATE otp_codes SET used=1 WHERE id=:id')->execute([':id'=>$otp['id']]);
    
    // Log user in
    setUserSession($user);
    
    jsonResponse(true, 'Verified.');
}
