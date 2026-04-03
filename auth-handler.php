<?php
/**
 * Solidus 3D — Auth Handler API
 * Receives POST JSON, returns JSON.
 * Actions: register, login, update_profile, change_password,
 *          forgot_password, verify_otp, reset_password, delete_account
 */
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

function jsonResponse(bool $ok, string $msg, array $extra = []): void {
    echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
    exit;
}

// ================================================================
// REGISTER
// ================================================================
if ($action === 'register') {
    $name  = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $pass  = $input['password'] ?? '';

    if (!$name || !$email || !$pass) {
        jsonResponse(false, 'All fields are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Invalid email.');
    }
    if (strlen($pass) < 6) {
        jsonResponse(false, 'Password must be at least 6 characters.');
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Email already registered.');
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password) VALUES (:n, :e, :p)'
    );
    $stmt->execute([':n' => $name, ':e' => $email, ':p' => $hash]);

    $userId = (int) $pdo->lastInsertId();
    setUserSession([
        'id' => $userId, 'name' => $name,
        'email' => $email, 'role' => 'user',
    ]);
    jsonResponse(true, 'Registration successful.', ['redirect' => 'dashboard.php']);
}

// ================================================================
// LOGIN
// ================================================================
if ($action === 'login') {
    $email = trim($input['email'] ?? '');
    $pass  = $input['password'] ?? '';

    if (!$email || !$pass) {
        jsonResponse(false, 'Email and password are required.');
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password'])) {
        jsonResponse(false, 'Invalid email or password.');
    }

    setUserSession($user);
    jsonResponse(true, 'Login successful.', ['redirect' => 'dashboard.php']);
}

// ================================================================
// UPDATE PROFILE (requires login)
// ================================================================
if ($action === 'update_profile') {
    if (!isLoggedIn()) {
        jsonResponse(false, 'You must be logged in.');
    }

    $name  = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $userId = $_SESSION['user_id'];

    if (!$name || !$email) {
        jsonResponse(false, 'Name and email are required.');
    }
    if (strlen($name) < 2) {
        jsonResponse(false, 'Name must be at least 2 characters.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Invalid email address.');
    }

    // Check if email is taken by another user
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e AND id != :id LIMIT 1');
    $stmt->execute([':e' => $email, ':id' => $userId]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'This email is already in use by another account.');
    }

    $stmt = $pdo->prepare('UPDATE users SET name = :n, email = :e WHERE id = :id');
    $stmt->execute([':n' => $name, ':e' => $email, ':id' => $userId]);

    // Update session
    $_SESSION['user_name']  = $name;
    $_SESSION['user_email'] = $email;

    jsonResponse(true, 'Profile updated successfully.');
}

// ================================================================
// CHANGE PASSWORD (requires login)
// ================================================================
if ($action === 'change_password') {
    if (!isLoggedIn()) {
        jsonResponse(false, 'You must be logged in.');
    }

    $currentPass = $input['current_password'] ?? '';
    $newPass     = $input['new_password'] ?? '';
    $userId      = $_SESSION['user_id'];

    if (!$currentPass || !$newPass) {
        jsonResponse(false, 'Both current and new password are required.');
    }
    if (strlen($newPass) < 6) {
        jsonResponse(false, 'New password must be at least 6 characters.');
    }

    // Verify current password
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPass, $user['password'])) {
        jsonResponse(false, 'Current password is incorrect.');
    }

    $hash = password_hash($newPass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE users SET password = :p WHERE id = :id');
    $stmt->execute([':p' => $hash, ':id' => $userId]);

    jsonResponse(true, 'Password changed successfully.');
}

// ================================================================
// FORGOT PASSWORD — Send OTP
// ================================================================
if ($action === 'forgot_password') {
    $email = trim($input['email'] ?? '');

    if (!$email) {
        jsonResponse(false, 'Email is required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(false, 'Invalid email address.');
    }

    // Check user exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Don't reveal whether email exists — still show success
        jsonResponse(true, 'If this email is registered, a verification code has been sent.');
    }

    // Generate 6-digit OTP
    $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Mark old OTPs as used
    $stmt = $pdo->prepare('UPDATE otp_codes SET used = 1 WHERE user_id = :uid AND used = 0');
    $stmt->execute([':uid' => $user['id']]);

    // Insert new OTP
    $stmt = $pdo->prepare(
        'INSERT INTO otp_codes (user_id, code, expires_at) VALUES (:uid, :code, :exp)'
    );
    $stmt->execute([':uid' => $user['id'], ':code' => $code, ':exp' => $expiresAt]);

    // Send OTP via email
    $subject = 'Solidus 3D — Password Reset Code';
    $body = "Your password reset verification code is:\n\n"
          . "    {$code}\n\n"
          . "This code expires in 15 minutes.\n\n"
          . "If you did not request this, please ignore this email.\n\n"
          . "— Solidus 3D Modeling Team";

    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: Solidus 3D <noreply@solidus3dmodeling.com>',
    ];

    $sent = function_exists('mail') && @mail($email, $subject, $body, implode("\r\n", $headers));

    // Log to error log if mail fails (useful for dev)
    if (!$sent) {
        error_log("Solidus 3D OTP for {$email}: {$code} (mail() failed — configure SMTP)");
    }

    jsonResponse(true, 'If this email is registered, a verification code has been sent.');
}

// ================================================================
// VERIFY OTP
// ================================================================
if ($action === 'verify_otp') {
    $email = trim($input['email'] ?? '');
    $code  = trim($input['code'] ?? '');

    if (!$email || !$code) {
        jsonResponse(false, 'Email and verification code are required.');
    }

    // Find user
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse(false, 'Invalid verification code.');
    }

    // Check OTP
    $stmt = $pdo->prepare(
        'SELECT id FROM otp_codes WHERE user_id = :uid AND code = :code AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([':uid' => $user['id'], ':code' => $code]);
    $otp = $stmt->fetch();

    if (!$otp) {
        jsonResponse(false, 'Invalid or expired verification code. Please request a new one.');
    }

    // Generate a temporary reset token
    $token = bin2hex(random_bytes(32));
    $_SESSION['reset_token']   = $token;
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_otp_id']  = $otp['id'];

    jsonResponse(true, 'Code verified successfully.', ['token' => $token]);
}

// ================================================================
// RESET PASSWORD (after OTP verified)
// ================================================================
if ($action === 'reset_password') {
    $email    = trim($input['email'] ?? '');
    $token    = $input['token'] ?? '';
    $newPass  = $input['new_password'] ?? '';

    if (!$email || !$token || !$newPass) {
        jsonResponse(false, 'All fields are required.');
    }
    if (strlen($newPass) < 6) {
        jsonResponse(false, 'Password must be at least 6 characters.');
    }

    // Validate reset token from session
    if (
        empty($_SESSION['reset_token']) ||
        !hash_equals($_SESSION['reset_token'], $token) ||
        empty($_SESSION['reset_user_id'])
    ) {
        jsonResponse(false, 'Invalid or expired reset session. Please start over.');
    }

    $userId = $_SESSION['reset_user_id'];
    $otpId  = $_SESSION['reset_otp_id'] ?? 0;

    // Verify user email matches
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id AND email = :e LIMIT 1');
    $stmt->execute([':id' => $userId, ':e' => $email]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Invalid reset request.');
    }

    // Update password
    $hash = password_hash($newPass, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE users SET password = :p WHERE id = :id');
    $stmt->execute([':p' => $hash, ':id' => $userId]);

    // Mark OTP as used
    if ($otpId) {
        $stmt = $pdo->prepare('UPDATE otp_codes SET used = 1 WHERE id = :id');
        $stmt->execute([':id' => $otpId]);
    }

    // Clean up session reset data
    unset($_SESSION['reset_token'], $_SESSION['reset_user_id'], $_SESSION['reset_otp_id']);

    jsonResponse(true, 'Password reset successfully! You can now sign in with your new password.');
}

// ================================================================
// DELETE ACCOUNT (requires login)
// ================================================================
if ($action === 'delete_account') {
    if (!isLoggedIn()) {
        jsonResponse(false, 'You must be logged in.');
    }

    $userId = $_SESSION['user_id'];

    // Delete OTP codes first (foreign key)
    $stmt = $pdo->prepare('DELETE FROM otp_codes WHERE user_id = :uid');
    $stmt->execute([':uid' => $userId]);

    // Delete user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);

    // Destroy session
    destroySession();

    jsonResponse(true, 'Account deleted successfully.', ['redirect' => 'login.php']);
}

jsonResponse(false, 'Unknown action.');
