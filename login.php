<?php
/**
 * ============================================================
 *  Solidus 3D Modeling &mdash; Login / Signup Page
 *  File: login.php
 *
 *  BACKEND SETUP (MySQL + PHP):
 *  -----------------------------
 *  1. Create database & run SQL from auth-handler.php schema
 *  2. Set API_URL below to point to your auth-handler.php
 *  3. If user is already logged in, redirect to dashboard
 *
 *  SQL TABLES NEEDED (run in phpMyAdmin):
 *  -------------------------------------
 *  CREATE TABLE users (
 *    id         INT AUTO_INCREMENT PRIMARY KEY,
 *    name       VARCHAR(120) NOT NULL,
 *    email      VARCHAR(180) NOT NULL UNIQUE,
 *    verified   TINYINT(1) DEFAULT 0,
 *    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
 *  );
 *  CREATE TABLE otp_tokens (
 *    id         INT AUTO_INCREMENT PRIMARY KEY,
 *    email      VARCHAR(180) NOT NULL,
 *    otp        VARCHAR(6) NOT NULL,
 *    purpose    ENUM('register','login') DEFAULT 'login',
 *    expires_at DATETIME NOT NULL,
 *    used       TINYINT(1) DEFAULT 0,
 *    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
 *  );
 * ============================================================
 */

session_start();

// -- CHANGE: Redirect to dashboard if already logged in ------
// if (!empty($_SESSION['user_id'])) {
//     header('Location: /dashboard.php');
//     exit;
// }

// -- CHANGE: Set your auth API URL ---------------------------
$api_url = 'auth-handler.php'; // same folder as login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
<meta name="robots" content="noindex,nofollow">
<title>Sign In / Register &mdash; Solidus 3D Modeling</title>
<meta name="description" content="Sign in or create your Solidus 3D account to get a quote, track your projects, and manage your files.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ==========================================================
   VARIABLES & RESET
========================================================== */
:root{
  --ink:#0e0e0e; --paper:#f5f2ee; --cream:#ede9e3;
  --cyan:#00c8b4; --cyan2:#00ffe8; --mid:#6b6b6b;
  --light:#ccc8c0; --white:#fff; --red:#e63c2f;
  --D:'Bebas Neue',sans-serif;
  --B:'DM Sans',sans-serif;
  --M:'DM Mono',monospace;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;font-size:16px}
body{background:var(--paper);color:var(--ink);font-family:var(--B);overflow-x:hidden;cursor:none;min-height:100vh;display:flex;flex-direction:column}

/* -- Custom cursor -- */
#cur{position:fixed;width:12px;height:12px;background:var(--cyan);border-radius:50%;pointer-events:none;z-index:9999;transform:translate(-50%,-50%);transition:width .2s,height .2s;mix-blend-mode:multiply}
#curR{position:fixed;width:36px;height:36px;border:1.5px solid var(--ink);border-radius:50%;pointer-events:none;z-index:9998;transform:translate(-50%,-50%);opacity:.28}

/* ==========================================================
   NAV &mdash; same as main site
========================================================== */
nav{
  position:sticky;top:0;left:0;right:0;z-index:500;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 48px;height:68px;
  background:rgba(245,242,238,.97);
  backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
  border-bottom:1px solid rgba(0,0,0,.08);
  box-shadow:0 2px 20px rgba(0,0,0,.04);
}
.logo{font-family:var(--D);font-size:26px;letter-spacing:.06em;color:var(--ink);text-decoration:none}
.logo b{color:var(--cyan)}
.nav-links{display:flex;gap:4px;list-style:none;margin:0;align-items:center}
.nav-links>li{position:relative}
.nav-links>li>a{
  font-family:var(--M);font-size:11px;letter-spacing:.12em;text-transform:uppercase;
  color:var(--mid);text-decoration:none;padding:8px 14px;
  display:flex;align-items:center;gap:5px;transition:color .2s;border-radius:4px;
}
.nav-links>li>a:hover{color:var(--ink);background:rgba(0,0,0,.04)}
/* Dropdown */
.nav-arr{font-size:8px;opacity:.5;transition:transform .2s}
.dd{position:absolute;top:calc(100% + 8px);left:0;background:var(--white);border:1px solid rgba(0,0,0,.09);box-shadow:0 12px 40px rgba(0,0,0,.1);min-width:248px;padding:8px 0;opacity:0;visibility:hidden;transform:translateY(8px);transition:opacity .2s,transform .2s,visibility .2s;z-index:600;border-radius:8px}
.nav-links>li:hover .dd{opacity:1;visibility:visible;transform:translateY(0)}
.nav-links>li:hover .nav-arr{transform:rotate(180deg)}
.dd a{display:flex;align-items:center;gap:12px;padding:11px 20px;font-size:13px;color:var(--ink);text-decoration:none;transition:background .15s,color .15s}
.dd a:hover{background:rgba(0,200,180,.07);color:var(--cyan)}
.dd svg{width:17px;height:17px;flex-shrink:0}
.dd-div{height:1px;background:rgba(0,0,0,.06);margin:5px 0}
.nbtn{background:var(--ink);color:var(--paper);font-family:var(--M);font-size:11px;letter-spacing:.14em;text-transform:uppercase;padding:11px 24px;border:none;cursor:none;text-decoration:none;transition:background .2s}
.nbtn:hover{background:var(--cyan);color:var(--ink)}
.ham{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:4px}
.ham span{display:block;width:24px;height:2px;background:var(--ink);transition:all .3s}
/* Mobile drawer */
.mob{position:fixed;top:68px;left:0;right:0;bottom:0;background:var(--paper);z-index:450;display:flex;flex-direction:column;padding:40px 32px;gap:20px;transform:translateX(100%);transition:transform .3s}
.mob.open{transform:translateX(0)}
.mob a{font-family:var(--D);font-size:2.2rem;letter-spacing:.06em;text-transform:uppercase;color:var(--ink);text-decoration:none}
.mob a:hover{color:var(--cyan)}

/* ==========================================================
   PAGE MAIN &mdash; auth card centered
========================================================== */
main{
  flex:1;
  display:flex;align-items:center;justify-content:center;
  padding:60px 24px;
  position:relative;overflow:hidden;
  background:
    radial-gradient(ellipse 60% 50% at 10% 80%, rgba(0,200,180,.05) 0%, transparent 70%),
    radial-gradient(ellipse 50% 60% at 90% 20%, rgba(0,0,0,.03) 0%, transparent 70%),
    var(--paper);
}
/* Grid background texture */
main::before{
  content:'';position:absolute;inset:0;z-index:0;pointer-events:none;
  background-image:linear-gradient(rgba(0,0,0,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,0,0,.03) 1px,transparent 1px);
  background-size:48px 48px;
}
/* Big watermark */
main::after{
  content:'3D';
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  font-family:var(--D);font-size:40vw;color:rgba(0,0,0,.025);
  pointer-events:none;letter-spacing:-.02em;white-space:nowrap;z-index:0;
  animation:bgFloat 8s ease-in-out infinite;
}
@keyframes bgFloat{0%,100%{transform:translate(-50%,-50%) scale(1)}50%{transform:translate(-50%,-50%) scale(1.03)}}

/* ==========================================================
   AUTH CARD
========================================================== */
#auth-card{
  background:var(--white);
  width:100%;max-width:520px;
  position:relative;z-index:1;
  box-shadow:0 24px 80px rgba(0,0,0,.13), 0 2px 8px rgba(0,0,0,.06);
  animation:cardIn .55s cubic-bezier(.22,1,.36,1) forwards;
}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
/* Animated top accent bar */
#auth-card::before{
  content:'';display:block;height:3px;
  background:linear-gradient(90deg,var(--cyan),var(--cyan2),var(--cyan));
  background-size:200% 100%;
  animation:barSlide 2.5s linear infinite;
}
@keyframes barSlide{from{background-position:0%}to{background-position:200%}}

/* STEP SLIDES */
.steps-wrap{overflow:hidden;position:relative}
.steps{display:flex;transition:transform .48s cubic-bezier(.22,1,.36,1)}
.step{flex:0 0 100%;padding:44px 48px 40px}

/* -- Logo + dots -- */
.card-logo{font-family:var(--D);font-size:22px;letter-spacing:.06em;color:var(--ink);display:block;margin-bottom:24px;text-decoration:none}
.card-logo b{color:var(--cyan)}
.step-dots{display:flex;gap:6px;margin-bottom:28px}
.step-dot{width:6px;height:6px;border-radius:50%;background:var(--light);transition:background .3s,width .3s}
.step-dot.act{background:var(--cyan);width:20px;border-radius:3px}

/* -- Text -- */
.step-eyebrow{font-family:var(--M);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--cyan);display:flex;align-items:center;gap:8px;margin-bottom:10px}
.step-eyebrow::before{content:'//'}
.step-title{font-family:var(--D);font-size:clamp(2rem,4vw,2.8rem);letter-spacing:.03em;text-transform:uppercase;color:var(--ink);line-height:.9;margin-bottom:10px}
.step-sub{font-size:.88rem;color:var(--mid);font-weight:300;line-height:1.7;margin-bottom:28px}
.step-sub strong{color:var(--ink);font-weight:500}

/* -- Back button -- */
.back-btn{display:inline-flex;align-items:center;gap:7px;font-family:var(--M);font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--mid);background:none;border:none;cursor:pointer;padding:0;margin-bottom:20px;transition:color .2s}
.back-btn:hover{color:var(--ink)}
.back-btn svg{width:13px;height:13px}

/* -- Form fields -- */
.f-group{margin-bottom:18px}
.f-group label{display:block;font-family:var(--M);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--mid);margin-bottom:7px}
.f-group input{
  width:100%;background:var(--paper);border:1.5px solid transparent;
  color:var(--ink);font-family:var(--B);font-size:15px;font-weight:400;
  padding:13px 16px;outline:none;
  transition:border-color .2s,background .2s,box-shadow .2s;
}
.f-group input:focus{border-color:var(--cyan);background:var(--white);box-shadow:0 0 0 3px rgba(0,200,180,.1)}
.f-group input.err{border-color:var(--red) !important}
.f-err{font-family:var(--M);font-size:10px;color:var(--red);letter-spacing:.08em;margin-top:5px;display:none}
.f-group.has-err .f-err{display:block}

/* -- OTP boxes -- */
.otp-boxes{display:flex;gap:8px;margin-bottom:16px}
.otp-box{
  flex:1;height:64px;text-align:center;
  font-family:var(--D);font-size:2.2rem;color:var(--ink);
  background:var(--paper);border:1.5px solid transparent;
  outline:none;transition:border-color .2s,background .2s,box-shadow .2s;
  -moz-appearance:textfield;
}
.otp-box::-webkit-outer-spin-button,.otp-box::-webkit-inner-spin-button{-webkit-appearance:none}
.otp-box:focus{border-color:var(--cyan);background:var(--white);box-shadow:0 0 0 3px rgba(0,200,180,.1)}
.otp-box.filled{border-color:rgba(0,200,180,.35);background:rgba(0,200,180,.04)}
.otp-box.shake{animation:shake .38s ease}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-5px)}75%{transform:translateX(5px)}}

/* OTP meta row */
.otp-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;font-family:var(--M);font-size:10px;color:var(--mid);letter-spacing:.08em}
.otp-timer{display:flex;align-items:center;gap:5px}
.otp-timer svg{width:12px;height:12px}
#otp-cd{font-weight:500;color:var(--ink);transition:color .3s}
#otp-cd.exp{color:var(--red)}
.resend-btn{background:none;border:none;cursor:pointer;font-family:var(--M);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--cyan);padding:0;transition:color .2s}
.resend-btn:hover{color:var(--ink)}
.resend-btn:disabled{opacity:.35;cursor:not-allowed}

/* -- Submit button -- */
.sub-btn{
  width:100%;background:var(--ink);color:var(--white);
  font-family:var(--M);font-size:12px;letter-spacing:.16em;text-transform:uppercase;
  font-weight:500;padding:15px 28px;border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:10px;
  transition:background .2s,transform .15s;position:relative;overflow:hidden;
}
.sub-btn:hover:not(:disabled){background:var(--cyan);color:var(--ink);transform:translateY(-1px)}
.sub-btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.sub-btn .sp{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:none}
.sub-btn.ld .bt{opacity:.35}
.sub-btn.ld .sp{display:block}
@keyframes spin{to{transform:rotate(360deg)}}
.key-hint{font-family:var(--M);font-size:10px;color:var(--light);text-align:center;margin-top:10px;letter-spacing:.08em}

/* -- Status message -- */
.s-msg{padding:12px 16px;font-family:var(--M);font-size:11px;letter-spacing:.09em;margin-top:14px;display:none;border-left:3px solid transparent}
.s-msg.ok{display:block;background:rgba(0,200,180,.08);border-color:var(--cyan);color:#005e55}
.s-msg.er{display:block;background:rgba(230,60,47,.08);border-color:var(--red);color:var(--red)}

/* ==========================================================
   SUCCESS STATE
========================================================== */
#auth-success{display:none;padding:52px 48px;text-align:center}
.succ-icon{width:72px;height:72px;margin:0 auto 24px}
.succ-icon svg{width:100%;height:100%}
.succ-ring{stroke-dasharray:220;stroke-dashoffset:220;animation:drawRing .6s ease forwards .2s}
.succ-check{stroke-dasharray:42;stroke-dashoffset:42;animation:drawCheck .4s ease forwards .75s}
@keyframes drawRing{to{stroke-dashoffset:0}}
@keyframes drawCheck{to{stroke-dashoffset:0}}
#auth-success h2{font-family:var(--D);font-size:2.5rem;text-transform:uppercase;letter-spacing:.04em;color:var(--ink);margin-bottom:10px}
#auth-success p{font-size:.9rem;color:var(--mid);font-weight:300;line-height:1.7;margin-bottom:28px}
#success-name{color:var(--cyan);font-weight:500}
.succ-actions{display:flex;flex-direction:column;gap:10px;align-items:center}

/* ==========================================================
   TOAST NOTIFICATION POPUP (from main site message system)
========================================================== */
#toast{
  position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(20px);
  background:var(--ink);color:var(--white);
  padding:14px 24px;z-index:8000;
  display:flex;align-items:center;gap:12px;
  font-family:var(--M);font-size:11px;letter-spacing:.1em;
  opacity:0;pointer-events:none;
  transition:opacity .3s,transform .35s cubic-bezier(.22,1,.36,1);
  max-width:90vw;
  box-shadow:0 8px 32px rgba(0,0,0,.2);
}
#toast.show{opacity:1;transform:translateX(-50%) translateY(0);pointer-events:all}
#toast.ok-t{ border-left:3px solid var(--cyan) }
#toast.err-t{ border-left:3px solid var(--red) }
#toast svg{width:14px;height:14px;flex-shrink:0}
#toast-close{background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;padding:0;font-size:16px;line-height:1;transition:color .2s;margin-left:8px}
#toast-close:hover{color:var(--white)}

/* ==========================================================
   FOOTER &mdash; same as main site
========================================================== */
footer{background:#080808;padding:48px 48px 24px}
.ft{max-width:1400px;margin:0 auto}
.ft-top{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;padding-bottom:40px;border-bottom:1px solid rgba(255,255,255,.05)}
.ft-brand p{font-size:.78rem;color:rgba(255,255,255,.28);font-weight:300;line-height:1.7;max-width:240px;margin-top:10px}
.fc h5{font-family:var(--M);font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:var(--cyan);margin-bottom:16px}
.fc ul{list-style:none;display:flex;flex-direction:column;gap:8px}
.fc a{font-size:.78rem;color:rgba(255,255,255,.28);text-decoration:none;transition:color .2s}
.fc a:hover{color:var(--white)}
.ft-bot{display:flex;justify-content:space-between;align-items:center;padding-top:20px;flex-wrap:wrap;gap:10px}
.ft-bot p{font-family:var(--M);font-size:10px;color:rgba(255,255,255,.18)}

/* ==========================================================
   RESPONSIVE
========================================================== */
@media(max-width:768px){
  nav{padding:0 20px}
  .nav-links,.nbtn{display:none}
  .ham{display:flex}
  .step{padding:32px 28px 28px}
  #auth-success{padding:36px 28px}
  .ft-top{grid-template-columns:1fr 1fr}
  footer{padding:40px 20px 20px}
  .otp-box{height:52px;font-size:1.8rem}
  .otp-boxes{gap:6px}
}
@media(max-width:480px){
  .step{padding:28px 20px 24px}
  .ft-top{grid-template-columns:1fr}
  .otp-box{height:46px;font-size:1.5rem}
  .otp-boxes{gap:4px}
}
</style>
</head>
<body>

<!-- Cursor -->
<div id="cur"></div>
<div id="curR"></div>

<!-- Toast notification -->
<div id="toast" role="alert">
  <svg id="toast-icon" viewBox="0 0 16 16" fill="none">
    <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.4"/>
    <path id="toast-icon-path" d="M5 8l2.5 2.5L11 5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  <span id="toast-msg">Message here</span>
  <button id="toast-close" onclick="hideToast()" aria-label="Close">&times;</button>
</div>

<!-- Mobile Drawer -->
<div class="mob" id="mob">
  <a href="index.php" onclick="closeMob()">Home</a>
  <a href="index.php#services" onclick="closeMob()">Services</a>
  <a href="index.php#portfolio" onclick="closeMob()">Our Work</a>
  <a href="index.php#why" onclick="closeMob()">Why Us</a>
  <a href="index.php#process" onclick="closeMob()">Process</a>
  <a href="index.php#testi" onclick="closeMob()">Reviews</a>
  <a href="index.php#contact" onclick="closeMob()">Contact</a>
</div>

<!-- ==============================================
     NAV
============================================== -->
<nav>
  <a href="index.php" class="logo">SOLIDUS<b>.</b>3D</a>
  <ul class="nav-links">
    <li>
      <a href="index.php#services">Services <span class="nav-arr">&#9660;</span></a>
      <div class="dd">
        <a href="index.php#services">
          <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="#00c8b4" stroke-width="1.5"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12" stroke="#00c8b4" stroke-width="1.5" stroke-linecap="round"/></svg>
          Mechanical Modeling
        </a>
        <a href="index.php#services">
          <svg viewBox="0 0 24 24" fill="none"><rect x="2" y="7" width="8" height="10" rx="1" stroke="#00c8b4" stroke-width="1.5"/><path d="M14 5l6 7-6 7M14 12h6" stroke="#00c8b4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          2D to 3D Conversion
        </a>
        <a href="index.php#services">
          <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="4" stroke="#00c8b4" stroke-width="1.5"/><path d="M5 20c0-5 3-8 7-8s7 3 7 8" stroke="#00c8b4" stroke-width="1.5" stroke-linecap="round"/></svg>
          Sculpture &amp; Artistic Design
        </a>
        <div class="dd-div"></div>
        <a href="index.php#services">
          <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="4" r="2.5" stroke="#00c8b4" stroke-width="1.5"/><path d="M9 8h6M12 8v5M9 13l-2 4M15 13l2 4M9 13h6" stroke="#00c8b4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          BJD Doll Design
        </a>
        <a href="index.php#services">
          <svg viewBox="0 0 24 24" fill="none"><rect x="4" y="14" width="16" height="6" rx="1" stroke="#00c8b4" stroke-width="1.5"/><path d="M8 14V5h8v9" stroke="#00c8b4" stroke-width="1.5" stroke-linecap="round"/><circle cx="17" cy="17" r="1.5" fill="#00c8b4" opacity=".7"/></svg>
          3D Print-Ready Models
        </a>
        <a href="index.php#services">
          <svg viewBox="0 0 24 24" fill="none"><path d="M3 21h18M5 21V10l7-7 7 7v11" stroke="#00c8b4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="9" y="14" width="6" height="7" stroke="#00c8b4" stroke-width="1.5"/></svg>
          Building &amp; Architecture
        </a>
      </div>
    </li>
    <li><a href="index.php#portfolio">Our Work</a></li>
    <li><a href="index.php#why">Why Us</a></li>
    <li><a href="index.php#process">Process</a></li>
    <li><a href="index.php#testi">Reviews</a></li>
    <li><a href="index.php#faq">FAQ</a></li>
  </ul>
  <a href="index.php#contact" class="nbtn">Get a Quote</a>
  <button class="ham" id="hbtn" aria-label="Open menu"><span></span><span></span><span></span></button>
</nav>

<!-- ==============================================
     MAIN &mdash; AUTH CARD
============================================== -->
<main>
  <div id="auth-card" role="main">

    <!-- ================ STEP SLIDES ================ -->
    <div class="steps-wrap" id="steps-wrap">
      <div class="steps" id="steps">

        <!-- STEP 1: EMAIL -->
        <div class="step" id="step-email">
          <a href="index.php" class="card-logo">SOLIDUS<b>.</b>3D</a>
          <div class="step-dots">
            <div class="step-dot act" id="d1a"></div>
            <div class="step-dot" id="d1b"></div>
            <div class="step-dot" id="d1c"></div>
          </div>
          <div class="step-eyebrow">Start Here</div>
          <h1 class="step-title">Enter Your<br>Email.</h1>
          <p class="step-sub">We&apos;ll send a one-time verification code. No password needed &mdash; ever.</p>

          <div class="f-group" id="fg-email">
            <label for="inp-email">Email Address</label>
            <input type="email" id="inp-email" placeholder="you@company.com" autocomplete="email" autocapitalize="off" spellcheck="false">
            <div class="f-err" id="fe-email">Please enter a valid email address.</div>
          </div>

          <button class="sub-btn" id="btn-email" onclick="AUTH.submitEmail()">
            <span class="bt">Continue</span>
            <div class="sp"></div>
          </button>
          <div class="s-msg" id="msg-email"></div>
        </div>

        <!-- STEP 2: NAME (new users only) -->
        <div class="step" id="step-name">
          <a href="index.php" class="card-logo">SOLIDUS<b>.</b>3D</a>
          <button class="back-btn" onclick="AUTH.goTo(0)">
            <svg viewBox="0 0 14 14" fill="none"><path d="M9 2L3 7l6 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back
          </button>
          <div class="step-dots">
            <div class="step-dot" id="d2a"></div>
            <div class="step-dot act" id="d2b"></div>
            <div class="step-dot" id="d2c"></div>
          </div>
          <div class="step-eyebrow">New Account</div>
          <h2 class="step-title">What&apos;s<br>Your Name?</h2>
          <p class="step-sub">You&apos;re new here &mdash; welcome! We just need your name to get started.</p>

          <div class="f-group" id="fg-name">
            <label for="inp-name">Full Name</label>
            <input type="text" id="inp-name" placeholder="John Doe" autocomplete="name" autocapitalize="words">
            <div class="f-err" id="fe-name">Please enter your name (minimum 2 characters).</div>
          </div>

          <button class="sub-btn" id="btn-name" onclick="AUTH.submitName()">
            <span class="bt">Send Verification Code</span>
            <div class="sp"></div>
          </button>
          <div class="s-msg" id="msg-name"></div>
        </div>

        <!-- STEP 3: OTP -->
        <div class="step" id="step-otp">
          <a href="index.php" class="card-logo">SOLIDUS<b>.</b>3D</a>
          <button class="back-btn" onclick="AUTH.goTo(0)">
            <svg viewBox="0 0 14 14" fill="none"><path d="M9 2L3 7l6 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back
          </button>
          <div class="step-dots">
            <div class="step-dot" id="d3a"></div>
            <div class="step-dot" id="d3b"></div>
            <div class="step-dot act" id="d3c"></div>
          </div>
          <div class="step-eyebrow">Verification</div>
          <h2 class="step-title">Check Your<br>Inbox.</h2>
          <p class="step-sub">6-digit code sent to <strong id="otp-to">your email</strong>. Check spam if not found.</p>

          <!-- 6 individual OTP boxes -->
          <div class="otp-boxes" id="otp-boxes">
            <input class="otp-box" type="number" inputmode="numeric" id="ob0" autocomplete="one-time-code" aria-label="Digit 1">
            <input class="otp-box" type="number" inputmode="numeric" id="ob1" aria-label="Digit 2">
            <input class="otp-box" type="number" inputmode="numeric" id="ob2" aria-label="Digit 3">
            <input class="otp-box" type="number" inputmode="numeric" id="ob3" aria-label="Digit 4">
            <input class="otp-box" type="number" inputmode="numeric" id="ob4" aria-label="Digit 5">
            <input class="otp-box" type="number" inputmode="numeric" id="ob5" aria-label="Digit 6">
          </div>

          <div class="otp-meta">
            <div class="otp-timer">
              <svg viewBox="0 0 14 14" fill="none">
                <circle cx="7" cy="7" r="6" stroke="#6b6b6b" stroke-width="1.2"/>
                <path d="M7 4v3.5l2.5 1.5" stroke="#6b6b6b" stroke-width="1.2" stroke-linecap="round"/>
              </svg>
              Expires in <span id="otp-cd">10:00</span>
            </div>
            <button class="resend-btn" id="btn-resend" onclick="AUTH.resend()" disabled>Resend Code</button>
          </div>

          <button class="sub-btn" id="btn-otp" onclick="AUTH.submitOTP()" disabled>
            <span class="bt">Verify &amp; Sign In</span>
            <div class="sp"></div>
          </button>
          <div class="s-msg" id="msg-otp"></div>
          <p class="key-hint">Press Enter to verify &nbsp;&middot;&nbsp; All 6 digits required</p>
        </div>

      </div><!-- /steps -->
    </div><!-- /steps-wrap -->

    <!-- ================ SUCCESS ================ -->
    <div id="auth-success">
      <div class="succ-icon">
        <svg viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle class="succ-ring" cx="36" cy="36" r="33" stroke="#00c8b4" stroke-width="2" fill="none" stroke-linecap="round"/>
          <path class="succ-check" d="M19 36l13 13 21-22" stroke="#00c8b4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
      </div>
      <h2>You&apos;re In.</h2>
      <p>Welcome, <span id="success-name">there</span>. Your account is verified. You can now get a quote, track projects, and manage your files.</p>
      <div class="succ-actions">
        <a href="index.php#contact" class="sub-btn" style="text-decoration:none;display:inline-flex;max-width:260px">
          <span class="bt">Get Your Quote &#8599;</span>
        </a>
        <a href="index.php" style="font-family:var(--M);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--mid);text-decoration:none;margin-top:4px;transition:color .2s" onmouseover="this.style.color='var(--ink)'" onmouseout="this.style.color='var(--mid)'">
          Back to Home
        </a>
      </div>
    </div>

  </div><!-- /auth-card -->
</main>

<!-- ==============================================
     FOOTER
============================================== -->
<footer>
  <div class="ft">
    <div class="ft-top">
      <div class="ft-brand">
        <a href="index.php" class="logo" style="color:#fff">SOLIDUS<b>.</b>3D</a>
        <p>Precision 3D modeling studio serving B2B companies worldwide &mdash; NDA-protected, unlimited revisions, globally delivered.</p>
      </div>
      <div class="fc">
        <h5>Services</h5>
        <ul>
          <li><a href="index.php#services">Mechanical Modeling</a></li>
          <li><a href="index.php#services">2D to 3D Conversion</a></li>
          <li><a href="index.php#services">Sculpture Design</a></li>
          <li><a href="index.php#services">BJD Doll Design</a></li>
          <li><a href="index.php#services">Print-Ready Models</a></li>
          <li><a href="index.php#services">Architecture Models</a></li>
        </ul>
      </div>
      <div class="fc">
        <h5>Company</h5>
        <ul>
          <li><a href="index.php#portfolio">Our Work</a></li>
          <li><a href="index.php#why">Why Solidus 3D</a></li>
          <li><a href="index.php#process">Our Process</a></li>
          <li><a href="index.php#testi">Reviews</a></li>
          <li><a href="index.php#faq">FAQ</a></li>
          <li><a href="index.php#contact">Get a Quote</a></li>
        </ul>
      </div>
      <div class="fc">
        <h5>Legal</h5>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms &amp; Conditions</a></li>
          <li><a href="#">NDA Policy</a></li>
          <li><a href="#">Sitemap</a></li>
        </ul>
      </div>
    </div>
    <div class="ft-bot">
      <p>&copy; 2021&ndash;2026 Solidus 3D Modeling. All Rights Reserved.</p>
      <p>One Studio. <span style="color:var(--cyan)">Every 3D Discipline.</span></p>
    </div>
  </div>
</footer>

<!-- ==============================================
     JAVASCRIPT
     API_URL points to auth-handler.php (same folder)
============================================== -->
<script>
/* -- CONFIG -------------------------------------------------
   CHANGE: set to your auth-handler.php path
   Examples:
     'auth-handler.php'          (same folder)
     '/api/auth-handler.php'     (api subfolder)
     'https://yourdomain.com/auth-handler.php'
------------------------------------------------------------ */
var API_URL = 'auth-handler.php';

/* ========================================================
   CURSOR
======================================================== */
var cur = document.getElementById('cur');
var curR = document.getElementById('curR');
var mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove', function(e){
  mx=e.clientX; my=e.clientY;
  cur.style.left=mx+'px'; cur.style.top=my+'px';
});
(function tick(){
  rx+=(mx-rx)*.12; ry+=(my-ry)*.12;
  curR.style.left=rx+'px'; curR.style.top=ry+'px';
  requestAnimationFrame(tick);
})();
document.querySelectorAll('a,button,input').forEach(function(el){
  el.addEventListener('mouseenter',function(){cur.style.width='20px';cur.style.height='20px'});
  el.addEventListener('mouseleave',function(){cur.style.width='12px';cur.style.height='12px'});
});

/* ========================================================
   MOBILE NAV
======================================================== */
function closeMob(){
  document.getElementById('mob').classList.remove('open');
  document.getElementById('hbtn').setAttribute('aria-expanded','false');
}
document.getElementById('hbtn').addEventListener('click', function(){
  var o = document.getElementById('mob').classList.toggle('open');
  this.setAttribute('aria-expanded', o);
});

/* ========================================================
   TOAST NOTIFICATION
   showToast('message', 'ok')  or  showToast('message', 'err')
======================================================== */
var toastTimer = null;
function showToast(msg, type) {
  var t = document.getElementById('toast');
  var m = document.getElementById('toast-msg');
  var p = document.getElementById('toast-icon-path');
  if (!t || !m) return;
  m.textContent = msg;
  t.className = 'show ' + (type === 'err' ? 'err-t' : 'ok-t');
  // Icon: check for ok, X for err
  if (p) {
    p.setAttribute('d', type === 'err'
      ? 'M4 4l8 8M12 4l-8 8'
      : 'M4 8l3 3L12 5');
  }
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(hideToast, 4000);
}
function hideToast() {
  var t = document.getElementById('toast');
  if (t) t.classList.remove('show');
}

/* ========================================================
   AUTH MODULE
======================================================== */
var AUTH = (function(){

  /* State */
  var state = { email:'', name:'', purpose:'login', otpTimer:null, step:0 };
  var OTP_SECS = 600; // 10 minutes

  /* -- API call -- */
  function api(action, data) {
    return fetch(API_URL + '?action=' + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
      body: JSON.stringify(Object.assign({ action: action }, data))
    })
    .then(function(r){ return r.json(); })
    .catch(function(){ return { status: 'error', message: 'Network error. Please check your connection.' }; });
  }

  /* -- Loading state -- */
  function setLoad(id, on) {
    var btn = document.getElementById(id);
    if (!btn) return;
    btn.disabled = on;
    btn.classList.toggle('ld', on);
  }

  /* -- Show message under field -- */
  function showMsg(id, txt, type) {
    var el = document.getElementById(id);
    if (!el) return;
    el.className = 's-msg ' + type;
    el.textContent = txt;
  }
  function clearMsg(id) {
    var el = document.getElementById(id);
    if (el) { el.className = 's-msg'; el.textContent = ''; }
  }

  /* -- Field validation helpers -- */
  function fieldErr(groupId, errId, show) {
    var g = document.getElementById(groupId);
    var e = document.getElementById(errId);
    if (g) g.classList.toggle('has-err', show);
    if (e) e.style.display = show ? 'block' : 'none';
    var inp = g ? g.querySelector('input') : null;
    if (inp) inp.classList.toggle('err', show);
  }

  /* -- Slide to step index -- */
  function goTo(idx) {
    state.step = idx;
    var el = document.getElementById('steps');
    if (el) el.style.transform = 'translateX(-' + (idx * 100) + '%)';
    // Update all dot sets
    var sets = [
      ['d1a','d1b','d1c'],
      ['d2a','d2b','d2c'],
      ['d3a','d3b','d3c']
    ];
    sets.forEach(function(set){
      set.forEach(function(id, i){
        var d = document.getElementById(id);
        if (d) d.classList.toggle('act', i === idx);
      });
    });
    // Focus
    var focusMap = { 0: 'inp-email', 1: 'inp-name', 2: 'ob0' };
    setTimeout(function(){
      var el2 = document.getElementById(focusMap[idx]);
      if (el2) el2.focus();
    }, 460);
    if (idx !== 2) stopTimer();
  }

  /* -- STEP 1: submit email -- */
  function submitEmail() {
    var email = document.getElementById('inp-email').value.trim().toLowerCase();
    fieldErr('fg-email','fe-email', false);
    clearMsg('msg-email');
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      fieldErr('fg-email','fe-email', true);
      document.getElementById('inp-email').focus();
      return;
    }
    state.email = email;
    setLoad('btn-email', true);

    api('check_email', { email: email })
      .then(function(res){
        if (res.status === 'ok') {
          if (res.data && res.data.exists) {
            state.purpose = 'login';
            sendOTP(email, 'login');
          } else {
            state.purpose = 'register';
            setLoad('btn-email', false);
            goTo(1);
          }
        } else {
          setLoad('btn-email', false);
          showMsg('msg-email', res.message || 'Something went wrong.', 'er');
          showToast(res.message || 'Error checking email.', 'err');
        }
      });
  }

  /* -- STEP 2: submit name -- */
  function submitName() {
    var name = document.getElementById('inp-name').value.trim();
    fieldErr('fg-name','fe-name', false);
    clearMsg('msg-name');
    if (!name || name.length < 2) {
      fieldErr('fg-name','fe-name', true);
      document.getElementById('inp-name').focus();
      return;
    }
    state.name = name;
    setLoad('btn-name', true);
    sendOTP(state.email, 'register');
  }

  /* -- Send OTP -- */
  function sendOTP(email, purpose) {
    api('send_otp', { email: email, purpose: purpose, name: state.name })
      .then(function(res){
        setLoad('btn-email', false);
        setLoad('btn-name', false);
        if (res.status === 'ok') {
          // Show OTP destination
          var el = document.getElementById('otp-to');
          if (el) el.textContent = email;
          clearOTP();
          clearMsg('msg-otp');
          goTo(2);
          startTimer(OTP_SECS);
          showToast('Code sent to ' + email, 'ok');
        } else {
          var msgId = (purpose === 'login') ? 'msg-email' : 'msg-name';
          showMsg(msgId, res.message || 'Failed to send OTP.', 'er');
          showToast(res.message || 'Failed to send code.', 'err');
        }
      });
  }

  /* -- STEP 3: submit OTP -- */
  function submitOTP() {
    var otp = getOTP();
    if (otp.length < 6) return;
    clearMsg('msg-otp');
    setLoad('btn-otp', true);
    stopTimer();

    api('verify_otp', { email: state.email, otp: otp })
      .then(function(res){
        if (res.status === 'ok') {
          var action = (state.purpose === 'register') ? 'register' : 'login';
          var body = { email: state.email };
          if (action === 'register') body.name = state.name;
          return api(action, body).then(function(r2){
            setLoad('btn-otp', false);
            if (r2.status === 'ok') {
              var uname = (r2.data && r2.data.name) ? r2.data.name : (state.name || state.email);
              showSuccess(uname);
              showToast('Welcome, ' + uname + '!', 'ok');
              // Dispatch event for any other page JS to catch
              window.dispatchEvent(new CustomEvent('solidus:auth', { detail: r2.data }));
              /* CHANGE: redirect after login if needed */
              // setTimeout(function(){ window.location.href = '/dashboard.php'; }, 2000);
            } else {
              showMsg('msg-otp', r2.message || 'Authentication failed.', 'er');
              showToast(r2.message || 'Authentication failed.', 'err');
              startTimer(OTP_SECS);
            }
          });
        } else {
          setLoad('btn-otp', false);
          showMsg('msg-otp', res.message || 'Invalid or expired OTP.', 'er');
          showToast('Invalid OTP. Please try again.', 'err');
          shakeOTP();
          startTimer(OTP_SECS);
        }
      });
  }

  /* -- Resend OTP -- */
  function resend() {
    var btn = document.getElementById('btn-resend');
    if (btn) btn.disabled = true;
    clearOTP();
    clearMsg('msg-otp');
    sendOTP(state.email, state.purpose);
  }

  /* -- Show success screen -- */
  function showSuccess(name) {
    var sw = document.getElementById('steps-wrap');
    var ss = document.getElementById('auth-success');
    var sn = document.getElementById('success-name');
    if (sw) sw.style.display = 'none';
    if (ss) ss.style.display = 'block';
    if (sn) sn.textContent = name;
  }

  /* -- OTP input helpers -- */
  function initOTP() {
    for (var i = 0; i < 6; i++) {
      (function(idx){
        var inp = document.getElementById('ob' + idx);
        if (!inp) return;
        inp.addEventListener('input', function(){
          var v = this.value.replace(/\D/g,'').slice(-1);
          this.value = v;
          this.classList.toggle('filled', v !== '');
          if (v && idx < 5) document.getElementById('ob'+(idx+1)).focus();
          checkOTPFull();
        });
        inp.addEventListener('keydown', function(e){
          if (e.key === 'Backspace' && !this.value && idx > 0) {
            var p = document.getElementById('ob'+(idx-1));
            if (p) { p.value=''; p.classList.remove('filled'); p.focus(); }
          }
          if (e.key === 'Enter') submitOTP();
        });
        inp.addEventListener('paste', function(e){
          e.preventDefault();
          var t = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
          for (var j=0; j<t.length&&j<6; j++) {
            var el = document.getElementById('ob'+j);
            if (el) { el.value=t[j]; el.classList.add('filled'); }
          }
          checkOTPFull();
          var last = Math.min(t.length,5);
          var le = document.getElementById('ob'+last);
          if (le) le.focus();
        });
      })(i);
    }
  }
  function checkOTPFull(){
    var v='';
    for(var i=0;i<6;i++){var e=document.getElementById('ob'+i);v+=e?(e.value||''):'';}
    var btn=document.getElementById('btn-otp');
    if(btn) btn.disabled=(v.length<6);
  }
  function getOTP(){
    var v='';
    for(var i=0;i<6;i++){var e=document.getElementById('ob'+i);v+=e?(e.value||''):'';}
    return v;
  }
  function clearOTP(){
    for(var i=0;i<6;i++){var e=document.getElementById('ob'+i);if(e){e.value='';e.classList.remove('filled','shake');}}
    var btn=document.getElementById('btn-otp');
    if(btn) btn.disabled=true;
  }
  function shakeOTP(){
    for(var i=0;i<6;i++){
      var e=document.getElementById('ob'+i);
      if(e){e.classList.remove('shake');void e.offsetWidth;e.classList.add('shake');}
    }
  }

  /* -- OTP Countdown timer -- */
  function startTimer(seconds) {
    stopTimer();
    var rem = seconds;
    var cd = document.getElementById('otp-cd');
    var rb = document.getElementById('btn-resend');
    if (rb) rb.disabled = true;
    state.otpTimer = setInterval(function(){
      rem--;
      if (rem <= 0) {
        stopTimer();
        if (cd) { cd.textContent = 'Expired'; cd.classList.add('exp'); }
        if (rb) rb.disabled = false;
        showMsg('msg-otp', 'OTP expired. Please request a new code.', 'er');
        return;
      }
      var m=Math.floor(rem/60), s=rem%60;
      if (cd) { cd.textContent = m+':'+(s<10?'0':'')+s; cd.classList.remove('exp'); }
      // Enable resend after 60 seconds
      if (rem === seconds - 60 && rb) rb.disabled = false;
    }, 1000);
  }
  function stopTimer() {
    if (state.otpTimer) { clearInterval(state.otpTimer); state.otpTimer = null; }
  }

  /* -- Enter key on email & name inputs -- */
  var ei = document.getElementById('inp-email');
  var ni = document.getElementById('inp-name');
  if (ei) ei.addEventListener('keydown', function(e){ if(e.key==='Enter') submitEmail(); });
  if (ni) ni.addEventListener('keydown', function(e){ if(e.key==='Enter') submitName(); });

  /* -- INIT -- */
  initOTP();
  // Auto-focus email on load
  setTimeout(function(){ var e=document.getElementById('inp-email'); if(e) e.focus(); }, 300);

  return {
    submitEmail: submitEmail,
    submitName:  submitName,
    submitOTP:   submitOTP,
    resend:      resend,
    goTo:        goTo,
  };

})(); // END AUTH
</script>

</body>
</html>
