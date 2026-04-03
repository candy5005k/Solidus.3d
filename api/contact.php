<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

function contact_is_ajax(): bool
{
    return strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

function contact_redirect_target(string $target): string
{
    $fallback = site_url('contact.php');
    if ($target === '') {
        return $fallback;
    }

    if (str_starts_with($target, base_url()) || str_starts_with($target, '/')) {
        return $target;
    }

    return $fallback;
}

function contact_respond(bool $ok, string $message, string $type, string $redirect, int $statusCode = 200): void
{
    if (contact_is_ajax()) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'ok' => $ok,
            'message' => $message,
            'type' => $type,
        ]);
        exit;
    }

    flash_set('contact', $message, $type);
    header('Location: ' . $redirect);
    exit;
}

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    contact_respond(false, 'Invalid request method.', 'error', site_url('contact.php'), 405);
}

$redirect = contact_redirect_target(trim((string) ($_POST['redirect'] ?? '')));

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    contact_respond(false, 'Your session expired. Please refresh the page and try again.', 'error', $redirect, 422);
}

if (trim((string) ($_POST['website'] ?? '')) !== '') {
    contact_respond(true, 'Thanks, your request has been captured.', 'success', $redirect);
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$company = trim((string) ($_POST['company'] ?? ''));
$service = trim((string) ($_POST['service'] ?? ''));
$timeline = trim((string) ($_POST['timeline'] ?? 'Flexible'));
$projectDetails = trim((string) ($_POST['project_details'] ?? ''));

if ($name === '' || $email === '' || $phone === '' || $service === '' || $projectDetails === '') {
    contact_respond(false, 'Please fill in all required fields before sending the request.', 'error', $redirect, 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    contact_respond(false, 'Please use a valid email address.', 'error', $redirect, 422);
}

$subject = 'New Solidus 3D enquiry from ' . $name;
$body = "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Phone: {$phone}\n"
    . "Company: {$company}\n"
    . "Service: {$service}\n"
    . "Timeline: {$timeline}\n\n"
    . "Project Details:\n{$projectDetails}\n";

$host = parse_url(base_url(), PHP_URL_HOST) ?: 'solidus3dmodeling.com';
$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: Solidus 3D Modeling <noreply@' . $host . '>',
    'Reply-To: ' . $email,
];

$sent = function_exists('mail') && @mail(SUPPORT_EMAIL, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    contact_respond(false, 'The form is ready, but email delivery is not configured on this server yet. Configure PHP mail or SMTP in api/contact.php.', 'warning', $redirect, 500);
}

contact_respond(true, 'Thanks, your project brief was sent successfully. We will get back to you within 24 hours.', 'success', $redirect);
