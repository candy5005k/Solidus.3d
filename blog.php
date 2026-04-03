<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$selectedCategory = isset($_GET['category']) ? trim((string) $_GET['category']) : 'All';
if (!in_array($selectedCategory, blog_categories(), true)) {
    $selectedCategory = 'All';
}

$pageTitle = 'Blog | Solidus 3D Modeling';
$pageDescription = 'Read Solidus 3D Modeling articles on CAD handoff, reverse engineering, print preparation, and modeling workflows.';
$posts = fetch_blog_posts($selectedCategory, true, 12);

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="site-shell split-panel">
        <div class="hero-copy">
            <p class="eyebrow">Blog</p>
            <h1>A cleaner blog structure with category filters and single-post pages.</h1>
            <p class="lead">This page is now ready to list posts from the database table in your README, while still falling back to sample content until the database is configured.</p>
        </div>
        <div class="card card-dark">
            <p class="eyebrow">Current filter</p>
            <h2><?= h($selectedCategory === 'All' ? 'All categories' : $selectedCategory); ?></h2>
            <p class="section-copy">Use the admin panel to publish new articles from mobile or desktop once the database credentials and admin password hash are configured.</p>
        </div>
    </div>
</section>

<section class="page-section">
    <div class="site-shell">
        <div class="filter-row">
            <?php foreach (blog_categories() as $category): ?>
                <?php $url = $category === 'All' ? site_url('blog.php') : site_url('blog.php?category=' . rawurlencode($category)); ?>
                <a class="<?= $selectedCategory === $category ? 'is-active' : ''; ?>" href="<?= h($url); ?>"><?= h($category); ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($posts === []): ?>
            <div class="card empty-state">
                <h3>No posts found for this category.</h3>
                <p>Try another filter or publish a new article from the admin panel.</p>
            </div>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="card blog-card">
                        <div class="meta-row">
                            <span class="tag"><?= h($post['category']); ?></span>
                            <span><?= h(format_publish_date($post['created_at'])); ?></span>
                        </div>
                        <h3><?= h($post['title']); ?></h3>
                        <p><?= h($post['excerpt']); ?></p>
                        <div class="hero-actions" style="margin-top:18px;">
                            <a class="text-link" href="<?= h(site_url('blog-post.php?slug=' . rawurlencode($post['slug']))); ?>">Read article</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
