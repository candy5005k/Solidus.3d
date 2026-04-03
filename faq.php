<?php
$pageTitle = 'FAQ — Solidus 3D Modeling';
$pageDescription = 'Frequently asked questions about our 3D services.';
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#0f172a,#2563eb);padding:72px 48px;color:#fff;text-align:center}
.page-hero h1{font-family:var(--D);font-size:clamp(2.5rem,5vw,4rem);margin-bottom:12px}
.page-hero p{opacity:.85;max-width:600px;margin:0 auto;font-size:1.05rem}
.section{max-width:800px;margin:0 auto;padding:80px 24px}
.faq-item{border-bottom:1px solid #e5e7eb;padding:20px 0}
.faq-q{font-weight:600;font-size:1.05rem;color:#111827;cursor:pointer;display:flex;justify-content:space-between;align-items:center}
.faq-q::after{content:'+';font-size:1.4rem;color:#2563eb;transition:transform .2s}
.faq-item.open .faq-q::after{transform:rotate(45deg)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .3s;color:#6b7280;font-size:.95rem;line-height:1.7}
.faq-item.open .faq-a{max-height:200px;padding-top:12px}
</style>
<div class="page-hero"><h1>FAQ</h1><p>Answers to common questions about our services.</p></div>
<div class="section">
  <div class="faq-item"><div class="faq-q" onclick="this.parentElement.classList.toggle('open')">What file formats do you deliver?</div><div class="faq-a">We deliver in STEP, STL, IGES, OBJ, 3MF, and any format your pipeline requires.</div></div>
  <div class="faq-item"><div class="faq-q" onclick="this.parentElement.classList.toggle('open')">Do you sign NDAs?</div><div class="faq-a">Yes, every project is covered under a strict NDA. Your designs remain your intellectual property.</div></div>
  <div class="faq-item"><div class="faq-q" onclick="this.parentElement.classList.toggle('open')">How long does a typical project take?</div><div class="faq-a">Most projects are delivered within 3–5 business days. Complex projects may take 1–2 weeks.</div></div>
  <div class="faq-item"><div class="faq-q" onclick="this.parentElement.classList.toggle('open')">Are revisions included?</div><div class="faq-a">Yes, we offer unlimited revisions at no additional cost until you are fully satisfied.</div></div>
  <div class="faq-item"><div class="faq-q" onclick="this.parentElement.classList.toggle('open')">What industries do you serve?</div><div class="faq-a">Automotive, aerospace, medical devices, consumer products, architecture, art, and more.</div></div>
  <div class="faq-item"><div class="faq-q" onclick="this.parentElement.classList.toggle('open')">How do I get started?</div><div class="faq-a">Simply submit a quote request via our Contact page with your project details, and we'll respond within 24 hours.</div></div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
