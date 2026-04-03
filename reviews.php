<?php
$pageTitle = 'Reviews — Solidus 3D Modeling';
$pageDescription = 'Client testimonials and reviews.';
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#0f172a,#2563eb);padding:72px 48px;color:#fff;text-align:center}
.page-hero h1{font-family:var(--D);font-size:clamp(2.5rem,5vw,4rem);margin-bottom:12px}
.page-hero p{opacity:.85;max-width:600px;margin:0 auto;font-size:1.05rem}
.section{max-width:1000px;margin:0 auto;padding:80px 24px}
.rev-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:28px}
.rev-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:32px;position:relative}
.rev-card::before{content:'\201C';font-size:4rem;color:#dbeafe;font-family:serif;position:absolute;top:12px;left:24px;line-height:1}
.rev-card p{color:#4b5563;font-size:.95rem;line-height:1.7;margin-top:24px}
.rev-card .author{margin-top:16px;font-weight:600;color:#111827;font-size:.9rem}
.rev-card .role{color:#6b7280;font-size:.8rem}
.stars{color:#f59e0b;margin-top:12px;letter-spacing:2px}
</style>
<div class="page-hero"><h1>Client Reviews</h1><p>What our clients say about our precision modeling.</p></div>
<div class="section">
<div class="rev-grid">
  <div class="rev-card"><p>Exceptional quality and attention to detail. The CNC files were production-ready from day one.</p><div class="stars">★★★★★</div><div class="author">Michael R.</div><div class="role">Manufacturing Director, AutoParts Inc.</div></div>
  <div class="rev-card"><p>Fast turnaround and unlimited revisions. The team went above and beyond for our sculpture project.</p><div class="stars">★★★★★</div><div class="author">Sarah K.</div><div class="role">Creative Director, ArtVision Studio</div></div>
  <div class="rev-card"><p>Best 3D modeling service we've worked with. Strict NDA compliance and global delivery.</p><div class="stars">★★★★★</div><div class="author">David L.</div><div class="role">CEO, TechBuild Solutions</div></div>
  <div class="rev-card"><p>Our architectural models are always delivered on time with impeccable accuracy. Highly recommended.</p><div class="stars">★★★★★</div><div class="author">Anita P.</div><div class="role">Architect, DesignHaus</div></div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
