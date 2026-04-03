<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db_credentials_ready(): bool
{
    return DB_NAME !== '' && DB_USER !== '';
}

function db(): ?PDO
{
    static $pdo = false;

    if ($pdo !== false) {
        return $pdo;
    }

    if (!db_credentials_ready()) {
        $pdo = null;
        return $pdo;
    }

    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        $pdo = null;
    }

    return $pdo;
}

function blog_table_exists(): bool
{
    static $exists = null;

    if ($exists !== null) {
        return $exists;
    }

    $pdo = db();
    if (!$pdo) {
        $exists = false;
        return $exists;
    }

    try {
        $statement = $pdo->query("SHOW TABLES LIKE 'blog_posts'");
        $exists = (bool) $statement->fetchColumn();
    } catch (Throwable $exception) {
        $exists = false;
    }

    return $exists;
}

function hydrate_blog_post(array $post): array
{
    $post['title'] = (string) ($post['title'] ?? '');
    $post['slug'] = (string) ($post['slug'] ?? slugify($post['title']));
    $post['category'] = (string) ($post['category'] ?? '3D Modeling');
    $post['content'] = (string) ($post['content'] ?? '');
    $post['meta_title'] = (string) ($post['meta_title'] ?? $post['title']);
    $post['meta_desc'] = (string) ($post['meta_desc'] ?? excerpt_text($post['content']));
    $post['meta_keywords'] = (string) ($post['meta_keywords'] ?? '');
    $post['image'] = $post['image'] ?? null;
    $post['published'] = (int) ($post['published'] ?? 1);
    $post['created_at'] = (string) ($post['created_at'] ?? date('Y-m-d H:i:s'));
    $post['excerpt'] = excerpt_text($post['meta_desc'] !== '' ? $post['meta_desc'] : $post['content']);
    return $post;
}

function fetch_blog_posts(?string $category = null, bool $publishedOnly = true, int $limit = 12): array
{
    if (blog_table_exists()) {
        $sql = 'SELECT * FROM blog_posts';
        $conditions = [];
        $params = [];

        if ($publishedOnly) {
            $conditions[] = 'published = 1';
        }

        if ($category && $category !== 'All') {
            $conditions[] = 'category = :category';
            $params['category'] = $category;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY created_at DESC LIMIT ' . max(1, $limit);
        $statement = db()->prepare($sql);
        $statement->execute($params);
        $posts = $statement->fetchAll();
        return array_map('hydrate_blog_post', $posts ?: []);
    }

    $posts = array_map('hydrate_blog_post', sample_blog_posts());

    if ($publishedOnly) {
        $posts = array_values(array_filter($posts, static function (array $post): bool {
            return (int) $post['published'] === 1;
        }));
    }

    if ($category && $category !== 'All') {
        $posts = array_values(array_filter($posts, static function (array $post) use ($category): bool {
            return $post['category'] === $category;
        }));
    }

    return array_slice($posts, 0, $limit);
}

function fetch_blog_post_by_slug(string $slug, bool $publishedOnly = true): ?array
{
    if ($slug === '') {
        return null;
    }

    if (blog_table_exists()) {
        $sql = 'SELECT * FROM blog_posts WHERE slug = :slug';
        if ($publishedOnly) {
            $sql .= ' AND published = 1';
        }
        $sql .= ' LIMIT 1';

        $statement = db()->prepare($sql);
        $statement->execute(['slug' => $slug]);
        $post = $statement->fetch();
        return $post ? hydrate_blog_post($post) : null;
    }

    foreach (sample_blog_posts() as $post) {
        $post = hydrate_blog_post($post);
        if ($post['slug'] === $slug && (!$publishedOnly || (int) $post['published'] === 1)) {
            return $post;
        }
    }

    return null;
}

function next_available_slug(string $title): string
{
    $base = slugify($title);
    $slug = $base;
    $counter = 2;

    if (!blog_table_exists()) {
        return $slug;
    }

    $pdo = db();
    while (true) {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM blog_posts WHERE slug = :slug');
        $statement->execute(['slug' => $slug]);
        if ((int) $statement->fetchColumn() === 0) {
            return $slug;
        }
        $slug = $base . '-' . $counter;
        $counter++;
    }
}
