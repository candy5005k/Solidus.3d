<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid method']));
}

$user = currentUser();
if (!$user) {
    die(json_encode(['success' => false, 'message' => 'Please log in again.']));
}

$service = trim($_POST['service'] ?? '');
$timeline = trim($_POST['timeline'] ?? '');
$details = trim($_POST['details'] ?? '');
$ndaOtp = trim($_POST['nda_otp'] ?? '');

if (!$service || !$details || !$ndaOtp) {
    die(json_encode(['success' => false, 'message' => 'Please fill in all required fields.']));
}

// Verify the NDA OTP from the user
$stmt = $pdo->prepare('SELECT id FROM otp_codes WHERE user_id=:uid AND code=:code AND expires_at > NOW() ORDER BY id DESC LIMIT 1');
$stmt->execute([':uid' => $user['id'], ':code' => $ndaOtp]);
$otpRec = $stmt->fetch();

if (!$otpRec) {
    die(json_encode(['success' => false, 'message' => 'Invalid OTP for NDA signature. Check your email for the code sent during login.']));
}

// Mark OTP used again! Wait, the OTP might have already been marked used upon login. 
// If it was marked used upon login, our query above will fail if we check `used=0`.
// Since we want them to re-enter the SAME OTP they used to login, we just check if it matches an OTP they generated recently (even if used).
// Oh wait, I removed the `AND used=0` in the query above! So it will succeed if they use the same recent code.

// Handle file upload
$filePath = null;
if (!empty($_FILES['attachment']['name'])) {
    $uploadDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileInfo = pathinfo($_FILES['attachment']['name']);
    $ext = strtolower($fileInfo['extension']);
    $allowed = ['pdf','doc','docx','step','stl','obj','iges','zip','png','jpg'];
    
    if (!in_array($ext, $allowed) || $_FILES['attachment']['size'] > 20000000) {
         die(json_encode(['success' => false, 'message' => 'Invalid file format or file over 20MB.']));
    }
    
    $newName = uniqid('quote_') . '.' . $ext;
    $dest = $uploadDir . $newName;
    
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
        $filePath = 'assets/uploads/' . $newName;
    }
}

// Insert quote
$stmt = $pdo->prepare('INSERT INTO quotes (user_id, service, timeline, project_details, attachment_url) VALUES (:uid, :svc, :tl, :dt, :att)');
$stmt->execute([
    ':uid' => $user['id'],
    ':svc' => $service,
    ':tl' => $timeline,
    ':dt' => $details,
    ':att' => $filePath
]);

$quoteId = $pdo->lastInsertId();

// Send Priority Email to Admin
$subject = "PRIORITY INSTANT QUOTE: {$user['email']}";
$body = "New Instant Quote Request received:\n\n"
      . "From: {$user['email']}\n"
      . "Service: {$service}\n"
      . "Timeline: {$timeline}\n\n"
      . "Details:\n{$details}\n\n"
      . "NDA Signed via OTP: $ndaOtp\n\n"
      . "Log in to the Admin Panel to review this lead.";
      
$headers = "From: Solidus 3D <noreply@solidus3dmodeling.com>\r\nContent-Type: text/plain; charset=UTF-8";
@mail('support@solidus3dmodeling.com', $subject, $body, $headers);

// We redirect back to dashboard 
header('Location: ../quote-dashboard.php?success=1');
exit;
