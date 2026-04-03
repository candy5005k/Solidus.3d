<?php
$pageTitle = 'Why Solidus 3D — Professional 3D Modeling';
$pageDescription = 'Why choose Solidus 3D for your design needs.';
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#0f172a,#2563eb);padding:72px 48px;color:#fff;text-align:center}
.page-hero h1{font-family:var(--D);font-size:clamp(2.5rem,5vw,4rem);margin-bottom:12px}
.page-hero p{opacity:.85;max-width:600px;margin:0 auto;font-size:1.05rem}
.section{max-width:1200px;margin:0 auto;padding:80px 24px}
.stats{display:flex;justify-content:center;gap:48px;flex-wrap:wrap;margin-bottom:60px}
.stat{text-align:center}
.stat .num{font-family:var(--D);font-size:3rem;color:#2563eb}
.stat .lbl{font-size:.9rem;color:#6b7280}
.usp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:28px}
.usp-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:32px;text-align:center;transition:box-shadow .2s}
.usp-card:hover{box-shadow:0 8px 32px rgba(37,99,235,.1)}
.usp-card h3{font-size:1.1rem;color:#111827;margin-bottom:8px}
.usp-card p{color:#6b7280;font-size:.9rem;line-height:1.6}
</style>
<div class="page-hero"><h1>Why Solidus 3D</h1><p>Trusted by businesses worldwide for precision, quality, and reliability.</p></div>
<div class="section">
<div class="stats">
  <div class="stat"><div class="num">900+</div><div class="lbl">Projects Delivered</div></div>
  <div class="stat"><div class="num">30+</div><div class="lbl">Countries Served</div></div>
  <div class="stat"><div class="num">10+</div><div class="lbl">Years Experience</div></div>
  <div class="stat"><div class="num">100%</div><div class="lbl">NDA Protection</div></div>
</div>
<div class="usp-grid">
  <div class="usp-card"><h3>Unlimited Revisions</h3><p>We iterate until you are 100% satisfied. No extra charges.</p></div>
  <div class="usp-card"><h3>Fast Turnaround</h3><p>Most projects delivered within 3–5 business days.</p></div>
  <div class="usp-card"><h3>Expert Engineers</h3><p>Seasoned CAD engineers with deep domain expertise.</p></div>
  <div class="usp-card"><h3>24/7 Support</h3><p>Dedicated project managers around the clock.</p></div>
</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
