<?php
declare(strict_types=1);
/**
 * Solidus 3D — Central Email Helper
 * Uses PHP mail() by default. Update credentials for SMTP.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    $from    = 'noreply@solidus3dmodeling.com';
    $fromName = 'Solidus 3D Modeling';

    if ($textBody === '') {
        $textBody = strip_tags($htmlBody);
    }

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        // The user must configure these in their environment or directly here:
        $mail->Username   = getenv('SMTP_USER') ?: 'info@solidus3dmodeling.com';
        $mail->Password   = getenv('SMTP_PASS') ?: 'YourEmailPasswordHere123!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // or ENCRYPTION_STARTTLS
        $mail->Port       = 465; // or 587
        
        // Let's disable SSL verify for local dev just in case Hostinger throws cert errors in local XAMPP
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->addReplyTo($from, $fromName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Solidus 3D PHPMailer failed — To: {$to} | Subject: {$subject}. Error: {$mail->ErrorInfo}");
        return false;
    }
}

function email_layout(string $title, string $content): string
{
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{$title}</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)">
  <tr><td style="background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:32px 40px">
    <h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;letter-spacing:-0.5px">Solidus 3D Modeling</h1>
    <p style="margin:4px 0 0;color:rgba(255,255,255,.75);font-size:13px">{$title}</p>
  </td></tr>
  <tr><td style="padding:32px 40px;color:#374151;font-size:15px;line-height:1.7">
    {$content}
  </td></tr>
  <tr><td style="background:#f8fafc;padding:20px 40px;border-top:1px solid #e5e7eb;font-size:12px;color:#9ca3af">
    &copy; 2021–<?= date('Y') ?> Solidus 3D Modeling. All rights reserved.<br>
    Peenya Industrial Area, Bengaluru, India | info@solidus3dmodeling.com
  </td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
}

function send_new_quote_admin_email(array $quote): void
{
    $subject = "New Quote Request — {$quote['name']} ({$quote['service']})";
    $content = "
        <h2 style='margin:0 0 16px;color:#111827'>New Quote Request Received</h2>
        <table style='width:100%;border-collapse:collapse'>
            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:600;color:#374151;width:30%'>Name</td><td style='padding:10px 0;border-bottom:1px solid #f1f5f9'>{$quote['name']}</td></tr>
            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:600'>Email</td><td style='padding:10px 0;border-bottom:1px solid #f1f5f9'>{$quote['email']}</td></tr>
            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:600'>Phone</td><td style='padding:10px 0;border-bottom:1px solid #f1f5f9'>{$quote['phone']}</td></tr>
            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:600'>Service</td><td style='padding:10px 0;border-bottom:1px solid #f1f5f9'>{$quote['service']}</td></tr>
            <tr><td style='padding:10px 0;border-bottom:1px solid #f1f5f9;font-weight:600'>Timeline</td><td style='padding:10px 0;border-bottom:1px solid #f1f5f9'>{$quote['timeline']}</td></tr>
            <tr><td style='padding:10px 0;font-weight:600'>Details</td><td style='padding:10px 0'>" . nl2br(htmlspecialchars($quote['project_details'])) . "</td></tr>
        </table>
        <p style='margin:24px 0 0'><a href='http://localhost/solidus.3d/admin/' style='background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600'>View in Admin Panel →</a></p>
    ";
    send_email('info@solidus3dmodeling.com', $subject, email_layout($subject, $content));
}

function send_welcome_email(string $toEmail, string $toName, string $tempPassword): void
{
    $subject = 'Your Solidus 3D Account — Welcome!';
    $loginUrl = site_url('login.php');
    $content = "
        <h2 style='margin:0 0 16px;color:#111827'>Welcome, " . htmlspecialchars($toName) . "!</h2>
        <p>Your Solidus 3D account has been created. Use these credentials to track your quote and project status:</p>
        <div style='background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin:20px 0'>
            <p style='margin:0 0 8px'><strong>Email:</strong> " . htmlspecialchars($toEmail) . "</p>
            <p style='margin:0'><strong>Temporary Password:</strong> <code style='background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:4px'>{$tempPassword}</code></p>
        </div>
        <p style='color:#dc2626;font-size:13px'>⚠️ Please change your password after logging in.</p>
        <p><a href='{$loginUrl}' style='background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600'>Login to Your Dashboard →</a></p>
    ";
    send_email($toEmail, $subject, email_layout($subject, $content));
}

function send_blog_published_email(string $postTitle, string $postUrl): void
{
    $subject = "Blog Published: {$postTitle}";
    $content = "
        <h2 style='margin:0 0 16px;color:#111827'>New Post Published</h2>
        <p>Your blog post has been published successfully:</p>
        <div style='background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:20px;margin:20px 0'>
            <strong>" . htmlspecialchars($postTitle) . "</strong>
        </div>
        <p><a href='{$postUrl}' style='background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600'>View Post →</a></p>
    ";
    send_email('info@solidus3dmodeling.com', $subject, email_layout($subject, $content));
}

function send_quote_confirmation_email(string $toEmail, string $toName, string $service): void
{
    $subject = 'Quote Request Received — Solidus 3D';
    $dashUrl = site_url('quote-dashboard.php');
    $content = "
        <h2 style='margin:0 0 16px;color:#111827'>We received your request!</h2>
        <p>Hi " . htmlspecialchars($toName) . ",</p>
        <p>Thank you for contacting Solidus 3D Modeling. We've received your quote request for <strong>" . htmlspecialchars($service) . "</strong>.</p>
        <p>Our team will review it and get back to you within <strong>24 hours</strong>.</p>
        <p><a href='{$dashUrl}' style='background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600'>Track Your Request →</a></p>
        <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'>
        <p style='font-size:13px;color:#6b7280'>Need urgent help? Call us: <strong>+91 7420866709</strong> or email <strong>info@solidus3dmodeling.com</strong></p>
    ";
    send_email($toEmail, $subject, email_layout($subject, $content));
}
