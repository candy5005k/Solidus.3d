<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$post = fetch_blog_post_by_slug($slug, true);

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post Not Found | Solidus 3D Modeling';
    $pageDescription = 'The requested blog article could not be found.';
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="page-section">
        <div class="site-shell">
            <div class="card empty-state">
                <p class="eyebrow">404</p>
                <h2>That article is not available.</h2>
                <p>You can go back to the blog list or contact us for a direct answer.</p>
                <div class="hero-actions" style="justify-content:center; margin-top:20px;">
                    <a class="button button-primary" href="<?= h(site_url('blog.php')); ?>">Back to Blog</a>
                    <a class="button button-secondary" href="<?= h(site_url('contact.php')); ?>">Contact Us</a>
                </div>
            </div>
        </div>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['meta_title'] !== '' ? $post['meta_title'] : $post['title'];
$pageDescription = $post['meta_desc'] !== '' ? $post['meta_desc'] : $post['excerpt'];
$pageKeywords = $post['meta_keywords'] !== '' ? $post['meta_keywords'] : 'Solidus 3D Modeling blog';
$relatedPosts = array_values(array_filter(fetch_blog_posts(null, true, 4), static function (array $item) use ($post): bool {
    return $item['slug'] !== $post['slug'];
}));

include __DIR__ . '/includes/header.php';
?>
<section class="post-hero">
    <div class="site-shell">
        <div class="meta-row">
            <span class="tag"><?= h($post['category']); ?></span>
            <span><?= h(format_publish_date($post['created_at'])); ?></span>
        </div>
        <h1><?= h($post['title']); ?></h1>
        <p class="lead"><?= h($post['excerpt']); ?></p>
    </div>
</section>

<section class="page-section" style="padding-top:0;">
    <div class="site-shell post-body">
        <article class="post-content">
            <?= render_text_blocks($post['content']); ?>
        </article>
        <aside class="card aside-card">
            <p class="eyebrow">Need this solved in your project?</p>
            <h3>Use the contact page to send the brief.</h3>
            <p>We can review your files, timeline, and output format requirements before the work starts.</p>
            <div class="hero-actions" style="margin-top:18px;">
                <a class="button button-primary" href="<?= h(site_url('contact.php')); ?>">Start a Project</a>
            </div>
        </aside>
    </div>
</section>

<?php if ($relatedPosts !== []): ?>
<section class="page-section" style="padding-top:0;">
    <div class="site-shell">
        <div class="section-heading">
            <p class="eyebrow">More Articles</p>
            <h2>Related reading from the same site structure.</h2>
        </div>
        <div class="blog-grid">
            <?php foreach (array_slice($relatedPosts, 0, 3) as $related): ?>
                <article class="card blog-card">
                    <div class="meta-row">
                        <span class="tag"><?= h($related['category']); ?></span>
                        <span><?= h(format_publish_date($related['created_at'])); ?></span>
                    </div>
                    <h3><?= h($related['title']); ?></h3>
                    <p><?= h($related['excerpt']); ?></p>
                    <a class="text-link" href="<?= h(site_url('blog-post.php?slug=' . rawurlencode($related['slug']))); ?>">Read article</a>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
