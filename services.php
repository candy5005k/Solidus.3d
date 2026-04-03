<?php
declare(strict_types=1);

$staticServices = __DIR__ . '/services/index.html';

if (is_file($staticServices)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($staticServices);
    exit;
}

http_response_code(500);
echo 'Services page missing. Upload services/index.html to public_html/services/.';
