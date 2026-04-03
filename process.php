<?php
$pageTitle = 'Our Process — Solidus 3D Modeling';
$pageDescription = 'Our step-by-step 3D design process.';
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#0f172a,#2563eb);padding:72px 48px;color:#fff;text-align:center}
.page-hero h1{font-family:var(--D);font-size:clamp(2.5rem,5vw,4rem);margin-bottom:12px}
.page-hero p{opacity:.85;max-width:600px;margin:0 auto;font-size:1.05rem}
.section{max-width:900px;margin:0 auto;padding:80px 24px}
.timeline{position:relative;padding-left:40px}
.timeline::before{content:'';position:absolute;left:12px;top:0;bottom:0;width:2px;background:#dbeafe}
.step{position:relative;margin-bottom:48px}
.step .dot{position:absolute;left:-40px;top:4px;width:26px;height:26px;border-radius:50%;background:#2563eb;border:4px solid #dbeafe}
.step h3{font-family:var(--D);font-size:1.5rem;color:#111827;margin-bottom:6px}
.step p{color:#6b7280;font-size:.95rem;line-height:1.7}
.step .num{font-family:var(--M);font-size:11px;color:#2563eb;letter-spacing:.14em;text-transform:uppercase;margin-bottom:4px}
</style>
<div class="page-hero"><h1>Our Process</h1><p>A transparent, collaborative workflow from concept to delivery.</p></div>
<div class="section">
<div class="timeline">
  <div class="step"><div class="dot"></div><div class="num">Step 01</div><h3>Discovery &amp; Brief</h3><p>We review your requirements, reference files, and technical specifications to define the project scope.</p></div>
  <div class="step"><div class="dot"></div><div class="num">Step 02</div><h3>Concept &amp; Draft</h3><p>Our team creates initial concepts and drafts for your review and feedback.</p></div>
  <div class="step"><div class="dot"></div><div class="num">Step 03</div><h3>3D Modeling</h3><p>Full 3D modeling with the precision and detail your project demands.</p></div>
  <div class="step"><div class="dot"></div><div class="num">Step 04</div><h3>Review &amp; Revisions</h3><p>You review the model and request unlimited revisions until satisfied.</p></div>
  <div class="step"><div class="dot"></div><div class="num">Step 05</div><h3>Final Delivery</h3><p>Receive production-ready files in your preferred format (STEP, STL, IGES, etc.).</p></div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
