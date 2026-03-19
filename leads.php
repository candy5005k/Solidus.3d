<?php
// ═══════════════════════════════════════════════════════════════
// leads.php — UNIVERSAL LEADS API
// PLACE AT: public_html/api/leads.php
//
// HANDLES:
//   POST /api/leads.php  → Save lead + send emails
//   GET  /api/leads.php  → Returns unlock token (floor plan / brochure access)
//
// FORM TYPES (sent as form_type in POST body):
//   enquiry      → General enquiry form
//   floor_plan   → Unlock floor plan images
//   brochure     → Download brochure (sends email with message)
//   site_visit   → Book a site visit
//
// WORKS FOR: Azalea microsite + Propnmore main site
// Just pass site_name: 'azalea' or 'propnmore' in the request
// ═══════════════════════════════════════════════════════════════

declare(strict_types=1);
require_once __DIR__ . '/config.php';

// ── Step 1: Set CORS + Content-Type headers ──
setCorsHeaders();

// ══════════════════════════════════════════════
// POST — Save lead, send emails, return token
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read JSON body from fetch() calls
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    // Fallback to $_POST for traditional form submits
    if (empty($body)) $body = $_POST;

    // ── Validate required fields ──
    $name  = clean($body['name']  ?? '');
    $phone = clean($body['phone'] ?? '');
    $email = clean($body['email'] ?? '');

    if (empty($name) || empty($phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and phone are required.']);
        exit;
    }

    // ── Collect all fields ──
    $form_type    = clean($body['form_type']    ?? 'enquiry');
    $site_name    = clean($body['site_name']    ?? DEFAULT_SITE);
    $interested   = clean($body['interested_in'] ?? '');
    $message      = clean($body['message']      ?? '');
    $visit_date   = clean($body['visit_date']   ?? '');
    $visit_time   = clean($body['visit_time']   ?? '');
    $ip           = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua           = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

    // ── Save to database ──
    $db   = getDB();
    $stmt = $db->prepare("
        INSERT INTO leads
          (site_name, form_type, name, phone, email, interested_in, message, visit_date, visit_time, ip_address, user_agent)
        VALUES
          (:site, :ftype, :name, :phone, :email, :interest, :msg, :vdate, :vtime, :ip, :ua)
    ");
    $stmt->execute([
        ':site'     => $site_name,
        ':ftype'    => $form_type,
        ':name'     => $name,
        ':phone'    => $phone,
        ':email'    => $email ?: null,
        ':interest' => $interested ?: null,
        ':msg'      => $message ?: null,
        ':vdate'    => $visit_date ?: null,
        ':vtime'    => $visit_time ?: null,
        ':ip'       => $ip,
        ':ua'       => $ua,
    ]);
    $lead_id = (int)$db->lastInsertId();

    // ── If floor plan unlock: record which plan ──
    if ($form_type === 'floor_plan') {
        $plan = clean($body['plan_type'] ?? $interested);
        if ($plan) {
            $db->prepare("INSERT INTO floor_plan_unlocks (lead_id, plan_type) VALUES (?, ?)")
               ->execute([$lead_id, $plan]);
        }
    }

    // ── Generate secure unlock token (for floor plan + brochure) ──
    // This token is returned to the browser and proves form was submitted
    $token = base64_encode($lead_id . ':' . hash_hmac('sha256', $lead_id . $phone, DB_PASS));

    // ── Send notification email to YOU (the team) ──
    sendTeamNotification($form_type, $site_name, $name, $phone, $email, $interested, $message, $visit_date, $visit_time, $lead_id);

    // ── Send confirmation email to the USER ──
    if (!empty($email)) {
        sendUserConfirmation($form_type, $name, $email, $interested, $visit_date, $visit_time);
    }

    // ── Response ──
    $responses = [
        'enquiry'    => 'Thank you! Our team will contact you within 24 hours.',
        'floor_plan' => 'Floor plan unlocked! Our team will also reach out shortly.',
        'brochure'   => 'Brochure details sent! Please check your email. If not received, please contact ' . SUPPORT_PHONE,
        'site_visit' => 'Site visit booked! Our team will call you within 2 hours to confirm.',
    ];

    echo json_encode([
        'success' => true,
        'message' => $responses[$form_type] ?? $responses['enquiry'],
        'token'   => $token,
        'lead_id' => $lead_id,
    ]);
    exit;
}

// ══════════════════════════════════════════════
// GET — Verify token → allow floor plan / brochure access
// Called by JS before showing floor plan image or brochure
// ══════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $token = clean($_GET['token'] ?? '');

    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No token provided.']);
        exit;
    }

    // Decode token
    $decoded = base64_decode($token);
    if (!$decoded || !str_contains($decoded, ':')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid token.']);
        exit;
    }

    [$lead_id, $provided_hash] = explode(':', $decoded, 2);
    $lead_id = (int)$lead_id;

    // Look up lead in DB
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, phone, form_type, interested_in FROM leads WHERE id = ?");
    $stmt->execute([$lead_id]);
    $lead = $stmt->fetch();

    if (!$lead) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token not found.']);
        exit;
    }

    // Verify hash
    $expected = hash_hmac('sha256', $lead['id'] . $lead['phone'], DB_PASS);
    if (!hash_equals($expected, $provided_hash)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Token invalid.']);
        exit;
    }

    echo json_encode([
        'success'      => true,
        'access'       => true,
        'form_type'    => $lead['form_type'],
        'interested_in'=> $lead['interested_in'],
    ]);
    exit;
}

// ── Method not allowed ──
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
exit;


// ══════════════════════════════════════════════
// HELPER: Send notification email to the TEAM
// ══════════════════════════════════════════════
function sendTeamNotification(
    string $form_type, string $site, string $name, string $phone,
    string $email, string $interested, string $message,
    string $visit_date, string $visit_time, int $lead_id
): void {

    $form_labels = [
        'enquiry'    => '🏠 New Enquiry',
        'floor_plan' => '📐 Floor Plan Unlock',
        'brochure'   => '📄 Brochure Request',
        'site_visit' => '📅 Site Visit Booking',
    ];
    $label = $form_labels[$form_type] ?? 'New Lead';

    $subject = "[{$label}] {$name} — {$phone} | " . strtoupper($site);

    $body = "
<html><body style='font-family:Arial,sans-serif;background:#f5f5f5;padding:20px'>
<div style='max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden'>

  <div style='background:#C9A84C;padding:24px 30px'>
    <h1 style='color:#080806;margin:0;font-size:22px'>{$label}</h1>
    <p style='color:#080806;margin:6px 0 0;opacity:.7'>Site: " . strtoupper($site) . " &nbsp;|&nbsp; Lead #" . $lead_id . "</p>
  </div>

  <div style='padding:30px'>
    <table style='width:100%;border-collapse:collapse'>
      <tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666;width:140px'>Name</td>
          <td style='padding:10px 0;border-bottom:1px solid #eee;font-weight:bold'>{$name}</td></tr>
      <tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666'>Phone</td>
          <td style='padding:10px 0;border-bottom:1px solid #eee;font-weight:bold'><a href='tel:{$phone}'>{$phone}</a></td></tr>
      <tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666'>Email</td>
          <td style='padding:10px 0;border-bottom:1px solid #eee'>" . ($email ?: '—') . "</td></tr>
      <tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666'>Interested In</td>
          <td style='padding:10px 0;border-bottom:1px solid #eee'>" . ($interested ?: '—') . "</td></tr>
      <tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666'>Form Type</td>
          <td style='padding:10px 0;border-bottom:1px solid #eee'>{$form_type}</td></tr>";

    if ($visit_date) {
        $body .= "<tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666'>Visit Date</td>
              <td style='padding:10px 0;border-bottom:1px solid #eee'>{$visit_date} at {$visit_time}</td></tr>";
    }
    if ($message) {
        $body .= "<tr><td style='padding:10px 0;border-bottom:1px solid #eee;color:#666;vertical-align:top'>Message</td>
              <td style='padding:10px 0;border-bottom:1px solid #eee'>{$message}</td></tr>";
    }

    $body .= "
    </table>
    <div style='margin-top:24px;padding:16px;background:#fff9ee;border-left:4px solid #C9A84C;border-radius:4px'>
      <strong>Action Required:</strong> Call {$name} on <a href='tel:{$phone}'>{$phone}</a> within 24 hours.
    </div>
  </div>

  <div style='background:#f5f5f5;padding:16px 30px;font-size:12px;color:#999;text-align:center'>
    AZALEA — BKR Lifespaces &nbsp;|&nbsp; Kalyani Nagar, Pune &nbsp;|&nbsp; Lead received at " . date('d M Y, h:i A') . "
  </div>
</div>
</body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: {$email}\r\n";

    mail(NOTIFY_EMAIL, $subject, $body, $headers);
}


// ══════════════════════════════════════════════
// HELPER: Send confirmation email to the USER
// ══════════════════════════════════════════════
function sendUserConfirmation(
    string $form_type, string $name, string $email,
    string $interested, string $visit_date, string $visit_time
): void {

    $messages = [
        'enquiry' => [
            'subject' => 'Thank you for your enquiry — AZALEA, Kalyani Nagar',
            'heading' => 'We have received your enquiry!',
            'body'    => 'Our team will get in touch with you within 24 hours.',
        ],
        'floor_plan' => [
            'subject' => 'Your AZALEA Floor Plan — Kalyani Nagar',
            'heading' => 'Floor Plan Unlocked!',
            'body'    => 'You have unlocked the AZALEA floor plan. Please visit our website to view it. Our team will also reach out with more details shortly.',
        ],
        'brochure' => [
            'subject' => 'Your AZALEA Brochure — Kalyani Nagar',
            'heading' => 'Brochure Request Received!',
            'body'    => 'Thank you for your interest in AZALEA. Our team will share the detailed brochure with you shortly.<br><br>
                         <strong>If you do not receive it, please contact us:</strong><br>
                         📞 <a href="tel:' . SUPPORT_PHONE . '">' . SUPPORT_PHONE . '</a>',
        ],
        'site_visit' => [
            'subject' => 'Site Visit Confirmed — AZALEA, Kalyani Nagar',
            'heading' => 'Site Visit Booked!',
            'body'    => 'Your site visit' . ($visit_date ? " on <strong>{$visit_date} at {$visit_time}</strong>" : '') . ' has been noted. Our team will call you within 2 hours to confirm the appointment.',
        ],
    ];

    $m = $messages[$form_type] ?? $messages['enquiry'];

    $body = "
<html><body style='font-family:Arial,sans-serif;background:#f5f5f5;padding:20px'>
<div style='max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden'>

  <div style='background:#080806;padding:28px 30px;text-align:center'>
    <h1 style='color:#C9A84C;margin:0;font-size:28px;letter-spacing:6px'>AZALEA</h1>
    <p style='color:rgba(255,255,255,.6);margin:6px 0 0;font-size:13px'>3 &amp; 4 BHK Super Luxe Residences, Kalyani Nagar</p>
  </div>

  <div style='padding:36px 30px'>
    <h2 style='color:#080806;margin:0 0 8px'>{$m['heading']}</h2>
    <p style='color:#666;font-size:15px'>Dear {$name},</p>
    <p style='color:#444;font-size:15px;line-height:1.7'>{$m['body']}</p>

    <div style='margin-top:28px;padding:20px;background:#fff9ee;border-radius:6px;border:1px solid #C9A84C44'>
      <p style='margin:0;font-size:14px;color:#444'><strong>📍 Project:</strong> AZALEA, Plot 26 &amp; 27, Lane 3B, Kalyani Nagar, Pune — 411006</p>
      <p style='margin:8px 0 0;font-size:14px;color:#444'><strong>📞 Sales:</strong> <a href='tel:" . SUPPORT_PHONE . "' style='color:#C9A84C'>" . SUPPORT_PHONE . "</a></p>
      <p style='margin:8px 0 0;font-size:14px;color:#444'><strong>✉️ Email:</strong> <a href='mailto:" . NOTIFY_EMAIL . "' style='color:#C9A84C'>" . NOTIFY_EMAIL . "</a></p>
    </div>

    <p style='margin-top:28px;font-size:13px;color:#999'>MahaRERA Reg. No: P52100080938 | maharera.mahaonline.gov.in</p>
  </div>

  <div style='background:#080806;padding:16px 30px;font-size:11px;color:#555;text-align:center'>
    Redevelopment by BKR Lifespaces &nbsp;|&nbsp; Sales Partner: More Spaces &nbsp;|&nbsp; Artistic impressions only. T&amp;C apply.
  </div>
</div>
</body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">\r\n";

    mail($email, $m['subject'], $body, $headers);
}
