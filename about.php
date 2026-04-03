<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$pageTitle = 'About | Solidus 3D Modeling';
$pageDescription = 'Learn how Solidus 3D Modeling works with product teams on CAD conversion, reverse engineering, visualization, and production-ready assets.';

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="site-shell split-panel">
        <div class="hero-copy">
            <p class="eyebrow">About Solidus.3D</p>
            <h1>A modeling studio built around practical handoff quality.</h1>
            <p class="lead">This site structure is now set up for a more professional company presentation: shared layouts, cleaner content separation, and pages that explain what you do without mixing everything into one file.</p>
        </div>
        <div class="card card-dark">
            <p class="eyebrow">What we optimize for</p>
            <ul class="list-clean">
                <li>Geometry that survives vendor handoff</li>
                <li>Communication that reduces revision churn</li>
                <li>Confidentiality for sensitive client work</li>
                <li>Delivery formats that match the production path</li>
            </ul>
        </div>
    </div>
</section>

<section class="page-section">
    <div class="site-shell grid-3">
        <article class="card">
            <h3>Accuracy first</h3>
            <p>We focus on geometry that is editable, understandable, and usable after it leaves our desk.</p>
        </article>
        <article class="card">
            <h3>Fast alignment</h3>
            <p>Small feedback loops and scoped revision checkpoints help keep projects moving without hidden confusion.</p>
        </article>
        <article class="card">
            <h3>Production awareness</h3>
            <p>Files are delivered with the target process in mind, whether that process is CNC, molding, rendering, or 3D printing.</p>
        </article>
    </div>
</section>

<section class="page-section">
    <div class="site-shell split-panel">
        <div>
            <div class="section-heading">
                <p class="eyebrow">Working Style</p>
                <h2>The new file structure supports a cleaner company story.</h2>
            </div>
            <p class="section-copy">Instead of burying content inside a single large HTML or PHP file, the project now separates page content, reusable includes, assets, blog logic, and admin tools. That makes the website easier to grow and much easier to hand off to future developers.</p>
        </div>
        <div class="card">
            <div class="hero-stats">
                <?php foreach (site_stats() as $stat): ?>
                    <div class="stat-pill">
                        <strong><?= h($stat['value']); ?></strong>
                        <span><?= h($stat['label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
