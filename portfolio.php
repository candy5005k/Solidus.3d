<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Portfolio | Solidus 3D Modeling';
$pageDescription = 'Review project types and case-study style examples from Solidus 3D Modeling.';
$projects = site_portfolio_items();

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="site-shell split-panel">
        <div class="hero-copy">
            <p class="eyebrow">Portfolio</p>
            <h1>Project examples arranged for a cleaner case-study style view.</h1>
            <p class="lead">This page now gives you a dedicated place to show work samples instead of mixing proof, services, and contact copy on the homepage.</p>
        </div>
        <div class="card card-dark">
            <p class="eyebrow">Use this page for</p>
            <ul class="list-clean">
                <li>Representative project types</li>
                <li>Outcome-driven case snippets</li>
                <li>Industry-specific credibility</li>
                <li>Lead-generation proof before contact</li>
            </ul>
        </div>
    </div>
</section>

<section class="page-section">
    <div class="site-shell portfolio-grid">
        <?php foreach ($projects as $project): ?>
            <article class="card portfolio-card">
                <div class="meta-row"><span class="tag"><?= h($project['category']); ?></span></div>
                <h3><?= h($project['title']); ?></h3>
                <p><?= h($project['summary']); ?></p>
                <div class="card-list">
                    <strong>Outcome</strong>
                    <p><?= h($project['outcome']); ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="page-section">
    <div class="site-shell split-panel">
        <div class="card">
            <p class="eyebrow">Need a custom example set?</p>
            <h3>We can tailor this page to specific industries.</h3>
            <p>Once you have final project thumbnails, renders, or approved client excerpts, this structure is ready for more detailed portfolio cards and filtered category sections.</p>
        </div>
        <div class="card card-dark">
            <p class="eyebrow">Next step</p>
            <h2>Use the contact page to collect project-specific requirements.</h2>
            <div class="hero-actions" style="margin-top:22px;">
                <a class="button button-primary" href="<?= h(site_url('instant-quote.php')); ?>">Request a Quote</a>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
