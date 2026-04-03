<?php
// includes/header.php
require_once __DIR__ . '/config.php';
$cssPath = __DIR__ . '/../assets/css/main.css';
$jsPath  = __DIR__ . '/../assets/js/main.js';
$cssVer  = file_exists($cssPath) ? filemtime($cssPath) : time();
$jsVer   = file_exists($jsPath)  ? filemtime($jsPath)  : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Solidus 3D Modeling & CAD Design Services'; ?></title>
  <meta name="description" content="<?= isset($pageDesc) ? htmlspecialchars($pageDesc) : 'Professional 3D modeling, CAD design, and reality capture services.'; ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Mono:wght@400;500&family=IBM+Plex+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= h(asset_url('css/main.css')); ?>?v=<?= $cssVer; ?>">
  <script defer src="<?= h(asset_url('js/main.js')); ?>?v=<?= $jsVer; ?>"></script>
</head>
<body>
<div class="mob" id="mob">
    <a href="services/index.html" onclick="cn()">Services</a>
    <a href="services/index.html#manufacturing-services" onclick="cn()">Manufacturing</a>
    <a href="services/index.html#cad-services" onclick="cn()">CAD &amp; Design</a>
    <a href="#portfolio" onclick="cn()">Our Work</a>
    <a href="#why" onclick="cn()">Why Us</a>
    <a href="#process" onclick="cn()">Process</a>
    <a href="#testi" onclick="cn()">Reviews</a>
    <a href="#faq" onclick="cn()">FAQ</a>
    <a href="instant-quote.php" class="ctap" style="margin-top:8px;text-align:center" onclick="cn()">Get a Quote</a>
  </div>
<nav id="nav" role="navigation" aria-label="Main navigation">
    <!-- Logo -->
    <a href="#hero" class="logo-link" aria-label="Solidus 3D Modeling Home">
      <img id="logo-default" src="assets/images/logo-solidus-main.png" alt="Solidus 3D Modeling" height="44" style="display:block;width:auto">
      <img id="logo-scrolled" src="assets/images/logo-solidus-light.png" alt="Solidus 3D Modeling" height="44" style="display:none;width:auto">
    </a>
    <ul class="nl" role="list">
      <li>
        <a href="services/index.html">Services <span class="nav-arr">&#9660;</span></a>
        <div class="dd" role="menu" aria-label="Services"><!-- mega-menu -->
          <div class="dd-inner">

            <!-- COLUMN 1: Design & CAD -->
            <div class="dd-col">
              <div class="dd-col-head">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#2563eb" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Design &amp; CAD
              </div>
              <a href="services/index.html#cad-modeling">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="#2563eb" stroke-width="1.5"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                Design Services
              </a>
              <a href="services/index.html#cad-modeling">
                <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#2563eb" stroke-width="1.5"/><path d="M8 12h8M12 8v8" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                3D CAD Modeling
              </a>
              <a href="services/index.html#reverse-engineering">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 7h10a4 4 0 010 8h-1" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/><path d="M10 19l-4-4 4-4" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="17" cy="7" r="3" stroke="#2563eb" stroke-width="1.5"/></svg>
                Reverse Engineering
              </a>
              <a href="services/index.html#2d-to-3d-conversion">
                <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="8" height="10" rx="1" stroke="#2563eb" stroke-width="1.5"/><path d="M14 5l6 7-6 7M14 12h6" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                2D to 3D Conversion
              </a>
              <a href="services/index.html#sculpture">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2C9 5 6 9 6 13a6 6 0 0012 0c0-4-3-8-6-11z" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round"/></svg>
                Sculpture Designs
              </a>
              <a href="services/index.html#architecture">
                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="5" width="10" height="14" rx="1.5" stroke="#2563eb" stroke-width="1.5"/><path d="M18 7v10M16 9l4 4-4 4" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Architecture (2D/3D Plans)
              </a>
            </div>

            <!-- COLUMN 2: 3D Printing & Manufacturing -->
            <div class="dd-col">
              <div class="dd-col-head">
                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="14" width="16" height="6" rx="1" stroke="#2563eb" stroke-width="1.5"/><path d="M8 14V5h8v9" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                3D Printing &amp; Manufacturing
              </div>
              <a href="services/index.html#3d-printing-service">
                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="14" width="16" height="6" rx="1" stroke="#2563eb" stroke-width="1.5"/><path d="M8 14V5h8v9" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/><circle cx="17" cy="17" r="1.5" fill="#2563eb" opacity=".7"/></svg>
                3D Printing Service
              </a>
              <a href="services/index.html#cnc-machining-support">
                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="6" width="12" height="8" rx="1.5" stroke="#2563eb" stroke-width="1.5"/><path d="M16 10h4M20 8v4M8 16v3M6 19h8" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                CNC Machining Support
              </a>
              <a href="services/index.html#vacuum-casting">
                <svg viewBox="0 0 24 24" fill="none"><path d="M6 5h12v4l-3 4v5l-6 1v-6L6 9V5z" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round"/></svg>
                Vacuum Casting
              </a>
              <a href="services/index.html#profile-cutting">
                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="5" width="10" height="14" rx="1.5" stroke="#2563eb" stroke-width="1.5"/><path d="M18 7v10M16 9l4 4-4 4" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Profile Cutting
              </a>
              <a href="services/index.html#machine">
                <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="8" height="10" rx="1" stroke="#2563eb" stroke-width="1.5"/><path d="M14 5l6 7-6 7M14 12h6" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Machine Process / Plant Design
              </a>
            </div>

            <!-- COLUMN 3: Specialty / Creative -->
            <div class="dd-col">
              <div class="dd-col-head">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round"/></svg>
                Specialty &amp; Creative
              </div>
              <a href="services/index.html#trophy">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 4h10v8a5 5 0 01-10 0V4z" stroke="#2563eb" stroke-width="1.5" stroke-linejoin="round"/><path d="M7 7H4a2 2 0 000 4h3M17 7h3a2 2 0 010 4h-3M12 17v3M9 20h6" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                Trophy Design
              </a>
              <a href="services/index.html#display">
                <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#2563eb" stroke-width="1.5"/><path d="M8 21h8M12 17v4" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                Display Modeling
              </a>
              <a href="services/index.html#showcase">
                <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="6" width="12" height="8" rx="1.5" stroke="#2563eb" stroke-width="1.5"/><path d="M16 10h4M20 8v4M8 16v3M6 19h8" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                Showcase Design
              </a>
              <a href="services/index.html#character">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="#2563eb" stroke-width="1.5"/><path d="M4 20c0-4 3.58-7 8-7s8 3 8 7" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round"/></svg>
                Character Modeling
              </a>
            </div>

          </div><!-- /.dd-inner -->
          <div class="dd-footer">
            <a href="services/index.html">View Full Service Catalog &rarr;</a>
            <span>Trusted by 200+ global clients</span>
          </div>
        </div><!-- /.dd -->
      </li>
      <li><a href="#portfolio">Our Work</a></li>
      <li><a href="#why">Why Us</a></li>
      <li><a href="#process">Process</a></li>
      <li><a href="#testi">Reviews</a></li>
      <li><a href="#faq">FAQ</a></li>
    </ul>
    <a href="instant-quote.php" class="nbtn">Get a Quote</a>
    <button class="ham" id="hbtn" aria-label="Open menu"
      aria-expanded="false"><span></span><span></span><span></span></button>
  </nav>