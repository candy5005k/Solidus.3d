<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

// ── Admin auth helpers ─────────────────────────────────────────────────────
function admin_logged_in(): bool { return !empty($_SESSION['solidus_admin_logged_in']); }
function admin_redirect(string $tab = ''): void {
    $url = site_url('admin/') . ($tab ? '?tab=' . $tab : '');
    header('Location: ' . $url); exit;
}

// ── Image upload ───────────────────────────────────────────────────────────
function admin_upload_image(array $file): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed.');
    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    if (!isset($allowed[$mime])) throw new RuntimeException('Only JPG/PNG/WebP/GIF allowed.');
    if (!is_dir(BLOG_UPLOAD_DIR)) mkdir(BLOG_UPLOAD_DIR, 0775, true);
    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = BLOG_UPLOAD_DIR . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Storage failed.');
    return BLOG_UPLOAD_WEB_PATH . '/' . $filename;
}

// ── POST handler ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        flash_set('admin','Session expired.','error'); admin_redirect();
    }

    // Login
    if ($action === 'login') {
        if (!admin_is_configured()) { flash_set('admin','Set ADMIN_PASS_HASH first.','warning'); admin_redirect(); }
        $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
        if ($u === ADMIN_USER && password_verify($p, ADMIN_PASS_HASH)) {
            $_SESSION['solidus_admin_logged_in'] = true;
            flash_set('admin','Welcome back!','success');
        } else { flash_set('admin','Wrong username or password.','error'); }
        admin_redirect();
    }

    // Logout
    if ($action === 'logout') { unset($_SESSION['solidus_admin_logged_in']); admin_redirect(); }

    if (!admin_logged_in()) { flash_set('admin','Login first.','error'); admin_redirect(); }

    // Create blog post
    if ($action === 'create_post') {
        if (!blog_table_exists()) { flash_set('admin','Database not ready.','warning'); admin_redirect('blog'); }
        $title   = trim($_POST['title'] ?? '');
        $cat     = trim($_POST['category'] ?? '3D Modeling');
        $content = trim($_POST['content'] ?? '');
        $mtitle  = trim($_POST['meta_title'] ?? '');
        $mdesc   = trim($_POST['meta_desc'] ?? '');
        $mkw     = trim($_POST['meta_keywords'] ?? '');
        $pub     = isset($_POST['published']) ? 1 : 0;
        if (!$title || !$content) { flash_set('admin','Title and content required.','error'); admin_redirect('blog'); }
        try { $img = isset($_FILES['image']) ? admin_upload_image($_FILES['image']) : null; }
        catch (RuntimeException $e) { flash_set('admin',$e->getMessage(),'error'); admin_redirect('blog'); }
        $slug = next_available_slug($title);
        $stmt = $pdo->prepare('INSERT INTO blog_posts (title,slug,content,category,meta_title,meta_desc,meta_keywords,image,published) VALUES (:t,:s,:c,:ca,:mt,:md,:mk,:im,:pu)');
        $stmt->execute([':t'=>$title,':s'=>$slug,':c'=>$content,':ca'=>$cat,':mt'=>$mtitle?:$title,':md'=>$mdesc?:excerpt_text($content),':mk'=>$mkw,':im'=>$img,':pu'=>$pub]);
        if ($pub) {
            $postUrl = site_url("blog/{$slug}");
            send_blog_published_email($title, $postUrl);
        }
        flash_set('admin','Post published! Email notification sent.','success');
        admin_redirect('blog');
    }

    // Toggle post
    if ($action === 'toggle_post' && blog_table_exists()) {
        $id = (int)($_POST['id']??0);
        if ($id > 0) { $pdo->prepare('UPDATE blog_posts SET published=CASE WHEN published=1 THEN 0 ELSE 1 END WHERE id=:id')->execute(['id'=>$id]); }
        flash_set('admin','Post status updated.','success'); admin_redirect('blog');
    }

    // Delete post
    if ($action === 'delete_post' && blog_table_exists()) {
        $id = (int)($_POST['id']??0);
        if ($id > 0) {
            $s = $pdo->prepare('SELECT image FROM blog_posts WHERE id=:id'); $s->execute(['id'=>$id]); $img=$s->fetchColumn();
            $pdo->prepare('DELETE FROM blog_posts WHERE id=:id')->execute(['id'=>$id]);
            if ($img && str_starts_with((string)$img, BLOG_UPLOAD_WEB_PATH.'/')) { @unlink(BLOG_UPLOAD_DIR.'/'.basename((string)$img)); }
        }
        flash_set('admin','Post deleted.','success'); admin_redirect('blog');
    }

    // Update quote status
    if ($action === 'update_quote') {
        $id = (int)($_POST['id']??0);
        $status = $_POST['status']??'new';
        $notes  = trim($_POST['admin_notes']??'');
        $price  = trim($_POST['quoted_price']??'');
        $allowed = ['new','reviewing','quoted','accepted','declined','completed'];
        if ($id > 0 && in_array($status, $allowed)) {
            $priceVal = $price !== '' ? (float)$price : null;
            $pdo->prepare('UPDATE quotes SET status=:s, admin_notes=:n, quoted_price=:p WHERE id=:id')
                ->execute([':s'=>$status,':n'=>$notes?:null,':p'=>$priceVal,':id'=>$id]);
            flash_set('admin','Quote updated.','success');
        }
        admin_redirect('quotes');
    }

    // Delete quote
    if ($action === 'delete_quote') {
        $id = (int)($_POST['id']??0);
        if ($id>0) { $pdo->prepare('DELETE FROM quotes WHERE id=:id')->execute(['id'=>$id]); }
        flash_set('admin','Quote deleted.','success'); admin_redirect('quotes');
    }
}

// ── Data fetching ──────────────────────────────────────────────────────────
$tab   = $_GET['tab'] ?? 'overview';
$flash = flash_get('admin');
$posts = [];
$quotes_list = [];
$users_list  = [];
$quoteToEdit = null;

if (admin_logged_in()) {
    if (blog_table_exists()) {
        $posts = array_map('hydrate_blog_post', $pdo->query('SELECT * FROM blog_posts ORDER BY created_at DESC')->fetchAll() ?: []);
    }
    try {
        $quotes_list = $pdo->query('SELECT * FROM quotes ORDER BY created_at DESC')->fetchAll() ?: [];
        $users_list  = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll() ?: [];
    } catch (Throwable) {}

    // Edit quote modal data
    if (isset($_GET['edit_quote'])) {
        $s = $pdo->prepare('SELECT * FROM quotes WHERE id=:id'); $s->execute(['id'=>(int)$_GET['edit_quote']]);
        $quoteToEdit = $s->fetch() ?: null;
    }
}

// Counts
$totalPosts  = count($posts);
$totalQuotes = count($quotes_list);
$newQuotes   = count(array_filter($quotes_list, fn($q) => $q['status']==='new'));
$totalUsers  = count($users_list);

$statusColors = [
    'new'=>['bg'=>'#dbeafe','color'=>'#1d4ed8'],
    'reviewing'=>['bg'=>'#fef3c7','color'=>'#d97706'],
    'quoted'=>['bg'=>'#ede9fe','color'=>'#7c3aed'],
    'accepted'=>['bg'=>'#dcfce7','color'=>'#059669'],
    'declined'=>['bg'=>'#fef2f2','color'=>'#dc2626'],
    'completed'=>['bg'=>'#ccfbf1','color'=>'#0d9488'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Panel | Solidus 3D</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Mono:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--blue:#2563eb;--blue-dk:#1d4ed8;--blue-lt:#dbeafe;--ink:#111827;--body:#4b5563;--mid:#6b7280;--line:#e5e7eb;--paper:#fff;--bg2:#f8fafc;--dark:#0f172a;--sidebar:220px;--B:'Plus Jakarta Sans',sans-serif;--D:'Bebas Neue',sans-serif;--M:'DM Mono',monospace}
html{font-size:16px}
body{font-family:var(--B);background:var(--bg2);color:var(--body);min-height:100vh}
a{text-decoration:none;color:inherit}
input,textarea,select,button{font-family:var(--B)}

/* ═══ LAYOUT ═══════════════════════════════ */
.admin-layout{display:flex;min-height:100vh}

/* ═══ SIDEBAR ══════════════════════════════ */
.sidebar{width:var(--sidebar);background:var(--dark);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;overflow-y:auto}
.sb-brand{padding:24px 20px 20px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-brand h1{font-family:var(--D);font-size:1.4rem;color:#fff;letter-spacing:.5px}
.sb-brand p{font-family:var(--M);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-top:2px}
.sb-nav{flex:1;padding:16px 0}
.sb-section{padding:0 12px;margin-bottom:4px}
.sb-section-label{font-family:var(--M);font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:rgba(255,255,255,.3);padding:10px 8px 6px;display:block}
.sb-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:.9rem;color:rgba(255,255,255,.6);transition:all .2s;font-weight:500;cursor:pointer}
.sb-link:hover,.sb-link.active{background:rgba(37,99,235,.25);color:#fff}
.sb-link.active{background:var(--blue)}
.sb-link svg{width:18px;height:18px;flex-shrink:0}
.sb-badge{margin-left:auto;background:#dc2626;color:#fff;font-size:10px;font-family:var(--M);padding:2px 7px;border-radius:980px;min-width:20px;text-align:center}
.sb-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.08)}
.sb-logout{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.5);font-size:.85rem;transition:color .2s;cursor:pointer;background:none;border:none;width:100%}
.sb-logout:hover{color:#fca5a5}

/* ═══ MAIN CONTENT ══════════════════════════ */
.admin-main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-height:100vh}
.admin-topbar{background:#fff;border-bottom:1px solid var(--line);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50;box-shadow:0 1px 8px rgba(0,0,0,.04)}
.admin-topbar h2{font-family:var(--D);font-size:1.6rem;color:var(--ink)}
.admin-topbar-right{display:flex;align-items:center;gap:12px}
.admin-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--blue-dk));display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--D);font-size:1.1rem}
.admin-body{padding:32px;flex:1}

/* ═══ FLASH ════════════════════════════════ */
.flash{padding:14px 20px;border-radius:10px;font-size:.9rem;margin-bottom:24px;display:flex;align-items:center;gap:10px;font-weight:500}
.flash.success{background:#dcfce7;color:#15803d;border:1px solid #86efac}
.flash.error{background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}
.flash.warning{background:#fef3c7;color:#92400e;border:1px solid #fde68a}

/* ═══ STAT CARDS ════════════════════════════ */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:32px}
.stat-card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:24px;display:flex;align-items:center;gap:16px}
.stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.stat-icon svg{width:24px;height:24px}
.stat-icon.blue{background:var(--blue-lt);color:var(--blue)}
.stat-icon.green{background:#dcfce7;color:#16a34a}
.stat-icon.purple{background:#ede9fe;color:#7c3aed}
.stat-icon.red{background:#fef2f2;color:#dc2626}
.stat-num{font-family:var(--D);font-size:2.2rem;color:var(--ink);line-height:1}
.stat-lbl{font-size:.8rem;color:var(--mid);font-family:var(--M);letter-spacing:.1em;text-transform:uppercase;margin-top:2px}

/* ═══ CARDS ════════════════════════════════ */
.card{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;margin-bottom:24px}
.card-header{padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between}
.card-header h3{font-family:var(--D);font-size:1.3rem;color:var(--ink)}
.card-body{padding:24px}

/* ═══ FORM ══════════════════════════════════ */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
.form-grid.cols3{grid-template-columns:1fr 1fr 1fr}
@media(max-width:768px){.form-grid,.form-grid.cols3{grid-template-columns:1fr}}
.f-group{display:flex;flex-direction:column;gap:6px}
.f-group.full{grid-column:1/-1}
.f-group label{font-family:var(--M);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink);font-weight:500}
.f-group input,.f-group textarea,.f-group select{background:var(--bg2);border:1px solid var(--line);border-radius:8px;color:var(--ink);font-size:14px;padding:12px 14px;outline:none;transition:border-color .2s,box-shadow .2s;width:100%;font-family:var(--B)}
.f-group input:focus,.f-group textarea:focus,.f-group select:focus{border-color:var(--blue);box-shadow:0 0 0 3px var(--blue-lt)}
.f-group textarea{resize:vertical;min-height:120px}
.f-group .img-preview{width:80px;height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--line);display:block;margin-top:8px}
.char-count{font-size:11px;color:var(--mid);margin-top:2px;font-family:var(--M)}
.toggle-check{display:flex;align-items:center;gap:10px;cursor:pointer;font-size:.9rem;color:var(--ink);font-weight:500}
.toggle-check input{width:18px;height:18px;accent-color:var(--blue)}

/* ═══ BUTTONS ═══════════════════════════════ */
.btn{font-family:var(--M);font-size:12px;letter-spacing:.12em;text-transform:uppercase;font-weight:600;padding:11px 22px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all .2s;text-decoration:none}
.btn-primary{background:var(--blue);color:#fff;box-shadow:0 4px 14px rgba(37,99,235,.25)}
.btn-primary:hover{background:var(--blue-dk);transform:translateY(-1px)}
.btn-secondary{background:var(--bg2);color:var(--ink);border:1px solid var(--line)}
.btn-secondary:hover{border-color:var(--blue);color:var(--blue)}
.btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5}
.btn-danger:hover{background:#dc2626;color:#fff}
.btn-sm{padding:7px 14px;font-size:11px}
.btn-group{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:20px;padding-top:20px;border-top:1px solid var(--line)}

/* ═══ TABLE ══════════════════════════════════ */
.data-table{width:100%;border-collapse:collapse}
.data-table th{background:var(--bg2);font-family:var(--M);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--mid);padding:12px 16px;text-align:left;border-bottom:2px solid var(--line);white-space:nowrap}
.data-table td{padding:14px 16px;border-bottom:1px solid var(--line);font-size:.9rem;vertical-align:middle}
.data-table tr:last-child td{border-bottom:none}
.data-table tr:hover td{background:#f8fbff}
.data-table .actions{display:flex;gap:8px;align-items:center}

/* ═══ STATUS BADGE ═══════════════════════════ */
.badge{display:inline-block;padding:4px 12px;border-radius:980px;font-family:var(--M);font-size:9px;letter-spacing:.14em;text-transform:uppercase;font-weight:600}

/* ═══ POST THUMBNAIL ═══════════════════════ */
.post-thumb{width:42px;height:42px;border-radius:8px;object-fit:cover;background:var(--bg2);display:block}

/* ═══ MODAL ════════════════════════════════ */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:flex;align-items:center;justify-content:center;padding:24px}
.modal-box{background:#fff;border-radius:16px;width:100%;max-width:600px;max-height:90vh;overflow-y:auto;padding:32px;box-shadow:0 24px 80px rgba(0,0,0,.2)}
.modal-box h3{font-family:var(--D);font-size:1.5rem;margin-bottom:20px;color:var(--ink)}

/* ═══ RESPONSIVE ═══════════════════════════ */
@media(max-width:768px){
  .sidebar{transform:translateX(-100%);transition:transform .3s}
  .sidebar.open{transform:translateX(0)}
  .admin-main{margin-left:0}
  .admin-topbar,.admin-body{padding-left:16px;padding-right:16px}
  .stats-row{grid-template-columns:1fr 1fr}
}

/* ═══ LOGIN PAGE ════════════════════════════ */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--dark) 0%,#1e3a5f 50%,var(--blue) 100%);padding:24px}
.login-card{background:#fff;border-radius:20px;padding:40px 48px;width:100%;max-width:400px;box-shadow:0 24px 80px rgba(0,0,0,.3)}
.login-card h1{font-family:var(--D);font-size:2rem;color:var(--ink);margin-bottom:4px}
.login-card p{color:var(--mid);font-size:.9rem;margin-bottom:28px}
</style>
</head>
<body>

<?php if (!admin_logged_in()): ?>
<!-- ═══════════ LOGIN ═══════════ -->
<div class="login-wrap">
  <div class="login-card">
    <h1>Admin Login</h1>
    <p>Solidus 3D Modeling Panel</p>
    <?php if ($flash): ?>
      <div class="flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>
    <form method="POST" action="<?= h(site_url('admin/')) ?>">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="action" value="login">
      <div class="f-group" style="margin-bottom:16px">
        <label>Username</label>
        <input type="text" name="username" required autofocus autocomplete="username" placeholder="admin">
      </div>
      <div class="f-group" style="margin-bottom:24px">
        <label>Password</label>
        <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Sign In →</button>
    </form>
    <?php if (!admin_is_configured()): ?>
      <p style="margin-top:16px;font-size:.8rem;color:#dc2626;font-family:var(--M)">⚠ Set ADMIN_PASS_HASH in config.php first!</p>
    <?php endif; ?>
  </div>
</div>

<?php else: ?>
<!-- ═══════════ FULL ADMIN PANEL ═══════════ -->
<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-brand">
      <h1>Solidus 3D</h1>
      <p>Admin Panel</p>
    </div>
    <nav class="sb-nav">
      <div class="sb-section">
        <span class="sb-section-label">Main</span>
        <a class="sb-link <?= $tab==='overview'?'active':'' ?>" href="?tab=overview">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/></svg>
          Overview
        </a>
      </div>
      <div class="sb-section">
        <span class="sb-section-label">Content</span>
        <a class="sb-link <?= $tab==='blog'?'active':'' ?>" href="?tab=blog">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
          Blog Posts
        </a>
        <a class="sb-link <?= $tab==='new_post'?'active':'' ?>" href="?tab=new_post">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
          New Post
        </a>
      </div>
      <div class="sb-section">
        <span class="sb-section-label">Business</span>
        <a class="sb-link <?= $tab==='quotes'?'active':'' ?>" href="?tab=quotes">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
          Quotes
          <?php if ($newQuotes>0): ?><span class="sb-badge"><?= $newQuotes ?></span><?php endif; ?>
        </a>
        <a class="sb-link <?= $tab==='users'?'active':'' ?>" href="?tab=users">
          <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
          Users
        </a>
      </div>
      <div class="sb-section">
        <span class="sb-section-label">Website</span>
        <a class="sb-link" href="<?= h(site_url('index.html')) ?>" target="_blank">
          <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16A8 8 0 0010 2zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd"/></svg>
          View Website
        </a>
      </div>
    </nav>
    <div class="sb-footer">
      <form method="POST" action="<?= h(site_url('admin/')) ?>">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="sb-logout">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/></svg>
          Logout
        </button>
      </form>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="admin-main">
    <div class="admin-topbar">
      <button onclick="document.getElementById('sidebar').classList.toggle('open')" style="background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;color:var(--mid)">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
      </button>
      <h2><?= ['overview'=>'Overview','blog'=>'Blog Posts','new_post'=>'New Post','quotes'=>'Quotes','users'=>'Users'][$tab] ?? 'Admin' ?></h2>
      <div class="admin-topbar-right">
        <div class="admin-avatar">A</div>
      </div>
    </div>

    <div class="admin-body">
      <?php if ($flash): ?>
        <div class="flash <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
      <?php endif; ?>

      <!-- ═══ OVERVIEW ═══ -->
      <?php if ($tab === 'overview'): ?>
        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-icon blue"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg></div>
            <div><div class="stat-num"><?= $totalPosts ?></div><div class="stat-lbl">Blog Posts</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon red"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg></div>
            <div><div class="stat-num"><?= $newQuotes ?></div><div class="stat-lbl">New Quotes</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon purple"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"/></svg></div>
            <div><div class="stat-num"><?= $totalQuotes ?></div><div class="stat-lbl">Total Quotes</div></div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg></div>
            <div><div class="stat-num"><?= $totalUsers ?></div><div class="stat-lbl">Registered Users</div></div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
          <!-- Recent quotes -->
          <div class="card">
            <div class="card-header"><h3>Recent Quotes</h3><a href="?tab=quotes" class="btn btn-secondary btn-sm">View All</a></div>
            <div class="card-body" style="padding:0">
              <?php foreach(array_slice($quotes_list,0,5) as $q):
                $sc=$statusColors[$q['status']]??$statusColors['new']; ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--line)">
                  <div>
                    <div style="font-weight:600;font-size:.9rem;color:var(--ink)"><?= h($q['name']) ?></div>
                    <div style="font-size:.8rem;color:var(--mid)"><?= h($q['service']) ?></div>
                  </div>
                  <span class="badge" style="background:<?=$sc['bg']?>;color:<?=$sc['color']?>"><?= h($q['status']) ?></span>
                </div>
              <?php endforeach; ?>
              <?php if(empty($quotes_list)): ?><p style="padding:20px;color:var(--mid);font-size:.9rem">No quotes yet.</p><?php endif; ?>
            </div>
          </div>
          <!-- Recent posts -->
          <div class="card">
            <div class="card-header"><h3>Recent Posts</h3><a href="?tab=blog" class="btn btn-secondary btn-sm">View All</a></div>
            <div class="card-body" style="padding:0">
              <?php foreach(array_slice($posts,0,5) as $p): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--line)">
                  <?php if($p['image']): ?><img src="<?=h($p['image'])?>" class="post-thumb" alt=""><?php else: ?><div style="width:42px;height:42px;border-radius:8px;background:var(--blue-lt);flex-shrink:0"></div><?php endif; ?>
                  <div style="min-width:0">
                    <div style="font-weight:600;font-size:.88rem;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($p['title']) ?></div>
                    <div style="font-size:.78rem;color:var(--mid)"><?= h($p['category']) ?> · <?= (int)$p['published']?'Published':'Draft' ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if(empty($posts)): ?><p style="padding:20px;color:var(--mid);font-size:.9rem">No posts yet.</p><?php endif; ?>
            </div>
          </div>
        </div>

      <!-- ═══ NEW POST ═══ -->
      <?php elseif ($tab === 'new_post'): ?>
        <div class="card">
          <div class="card-header"><h3>Write New Blog Post</h3></div>
          <div class="card-body">
            <?php if (!blog_table_exists()): ?>
              <div class="flash warning">⚠ Database not connected. Run schema.sql first.</div>
            <?php else: ?>
            <form method="POST" action="<?=h(site_url('admin/'))?>" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
              <input type="hidden" name="action" value="create_post">

              <div class="form-grid">
                <div class="f-group full"><label>Post Title *</label><input type="text" name="title" required placeholder="How to choose the right 3D file format..." id="post-title" oninput="genMeta(this.value)"></div>
                <div class="f-group">
                  <label>Category</label>
                  <select name="category">
                    <option value="3D Modeling">3D Modeling</option>
                    <option value="Engineering">Engineering</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Design Tips">Design Tips</option>
                    <option value="Case Study">Case Study</option>
                    <option value="Industry News">Industry News</option>
                  </select>
                </div>
                <div class="f-group">
                  <label>Feature Image</label>
                  <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
                  <img id="img-preview" class="img-preview" style="display:none" alt="Preview">
                </div>
              </div>

              <div class="f-group" style="margin-bottom:16px">
                <label>Post Content * <span style="font-family:var(--M);font-size:10px;color:var(--mid)">(write naturally — paragraphs separated by blank lines)</span></label>
                <textarea name="content" required rows="14" placeholder="Write your blog post here...&#10;&#10;Use blank lines to separate paragraphs.&#10;&#10;Be specific and helpful..." id="post-content" oninput="updateCount(this)"></textarea>
                <div class="char-count" id="char-count">0 characters</div>
              </div>

              <div class="form-grid" style="background:var(--bg2);border-radius:10px;padding:20px;border:1px solid var(--line)">
                <div style="grid-column:1/-1;font-family:var(--M);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--mid);margin-bottom:12px">SEO Settings (auto-filled — you can edit)</div>
                <div class="f-group"><label>Meta Title</label><input type="text" name="meta_title" id="meta-title" placeholder="Auto from post title..."><div class="char-count" id="mt-count">0/70</div></div>
                <div class="f-group"><label>Meta Keywords</label><input type="text" name="meta_keywords" placeholder="3D modeling, CAD, manufacturing..."></div>
                <div class="f-group full"><label>Meta Description</label><textarea name="meta_desc" id="meta-desc" rows="2" placeholder="Auto-generated from content..."></textarea><div class="char-count" id="md-count">0/160</div></div>
              </div>

              <div class="btn-group">
                <label class="toggle-check"><input type="checkbox" name="published" value="1" checked> Publish immediately + send email notification</label>
                <button type="submit" class="btn btn-primary">Publish Post →</button>
                <a href="?tab=blog" class="btn btn-secondary">Cancel</a>
              </div>
            </form>
            <?php endif; ?>
          </div>
        </div>

      <!-- ═══ BLOG LIST ═══ -->
      <?php elseif ($tab === 'blog'): ?>
        <div style="display:flex;justify-content:flex-end;margin-bottom:20px">
          <a href="?tab=new_post" class="btn btn-primary">+ New Post</a>
        </div>
        <div class="card">
          <div class="card-body" style="padding:0">
            <table class="data-table">
              <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach($posts as $p): ?>
                <tr>
                  <td><?php if($p['image']): ?><img src="<?=h($p['image'])?>" class="post-thumb" alt=""><?php else: ?><div style="width:42px;height:42px;border-radius:8px;background:var(--blue-lt)"></div><?php endif; ?></td>
                  <td style="font-weight:600;color:var(--ink);max-width:280px"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= h($p['title']) ?></div></td>
                  <td><span class="badge" style="background:var(--blue-lt);color:var(--blue)"><?= h($p['category']) ?></span></td>
                  <td>
                    <?php if((int)$p['published']): ?>
                      <span class="badge" style="background:#dcfce7;color:#15803d">Published</span>
                    <?php else: ?>
                      <span class="badge" style="background:var(--bg2);color:var(--mid)">Draft</span>
                    <?php endif; ?>
                  </td>
                  <td style="color:var(--mid);font-size:.85rem;white-space:nowrap"><?= h(date('M j, Y',strtotime($p['created_at']))) ?></td>
                  <td>
                    <div class="actions">
                      <a href="<?=h(site_url('blog/'.$p['slug']))?>" target="_blank" class="btn btn-secondary btn-sm">View</a>
                      <form method="POST" action="<?=h(site_url('admin/'))?>">
                        <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                        <input type="hidden" name="action" value="toggle_post">
                        <input type="hidden" name="id" value="<?=h((string)$p['id'])?>">
                        <button type="submit" class="btn btn-secondary btn-sm"><?=(int)$p['published']?'Unpublish':'Publish'?></button>
                      </form>
                      <form method="POST" action="<?=h(site_url('admin/'))?>" onsubmit="return confirm('Delete this post?')">
                        <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="id" value="<?=h((string)$p['id'])?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($posts)): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--mid)">No blog posts yet. <a href="?tab=new_post" style="color:var(--blue)">Create one →</a></td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      <!-- ═══ QUOTES ═══ -->
      <?php elseif ($tab === 'quotes'): ?>
        <div class="card">
          <div class="card-body" style="padding:0">
            <table class="data-table">
              <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Service</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach($quotes_list as $q):
                  $sc=$statusColors[$q['status']]??$statusColors['new']; ?>
                  <tr>
                    <td style="font-family:var(--M);font-size:.8rem;color:var(--mid)">#<?=str_pad((string)$q['id'],4,'0',STR_PAD_LEFT)?></td>
                    <td style="font-weight:600;color:var(--ink)"><?=h($q['name'])?></td>
                    <td style="color:var(--mid)"><?=h($q['email'])?></td>
                    <td><span style="font-size:.85rem"><?=h(mb_substr($q['service'],0,30))?></span></td>
                    <td><span class="badge" style="background:<?=$sc['bg']?>;color:<?=$sc['color']?>"><?=h($q['status'])?></span></td>
                    <td style="font-size:.83rem;color:var(--mid);white-space:nowrap"><?=h(date('M j, Y',strtotime($q['created_at'])))?></td>
                    <td>
                      <div class="actions">
                        <a href="?tab=quotes&edit_quote=<?=$q['id']?>" class="btn btn-secondary btn-sm">Edit</a>
                        <form method="POST" action="?tab=quotes" onsubmit="return confirm('Delete this quote request?')">
                          <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
                          <input type="hidden" name="action" value="delete_quote">
                          <input type="hidden" name="id" value="<?=$q['id']?>">
                          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if(empty($quotes_list)): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mid)">No quote requests yet.</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Edit quote modal -->
        <?php if ($quoteToEdit): ?>
        <div class="modal-overlay" onclick="if(event.target===this)location.href='?tab=quotes'">
          <div class="modal-box">
            <h3>Quote #<?=str_pad((string)$quoteToEdit['id'],4,'0',STR_PAD_LEFT)?> — <?=h($quoteToEdit['name'])?></h3>
            <div style="background:var(--bg2);border-radius:8px;padding:16px;margin-bottom:20px;font-size:.88rem">
              <strong><?=h($quoteToEdit['service'])?></strong><br>
              <span style="color:var(--mid)"><?=h($quoteToEdit['email'])?> · <?=h($quoteToEdit['phone'] ?: 'No phone')?></span><br>
              <p style="margin:10px 0 0;color:var(--body)"><?=h($quoteToEdit['project_details'])?></p>
            </div>
            <form method="POST" action="?tab=quotes">
              <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
              <input type="hidden" name="action" value="update_quote">
              <input type="hidden" name="id" value="<?=$quoteToEdit['id']?>">
              <div class="form-grid">
                <div class="f-group">
                  <label>Status</label>
                  <select name="status">
                    <?php foreach(['new','reviewing','quoted','accepted','declined','completed'] as $s): ?>
                      <option value="<?=$s?>" <?=$quoteToEdit['status']===$s?'selected':''?>><?=ucfirst($s)?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="f-group"><label>Quoted Price (₹)</label><input type="number" name="quoted_price" step="0.01" value="<?=h($quoteToEdit['quoted_price']??'')?>"></div>
                <div class="f-group full"><label>Admin Notes (visible to client)</label><textarea name="admin_notes" rows="3"><?=h($quoteToEdit['admin_notes']??'')?></textarea></div>
              </div>
              <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="?tab=quotes" class="btn btn-secondary">Cancel</a>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>

      <!-- ═══ USERS ═══ -->
      <?php elseif ($tab === 'users'): ?>
        <div class="card">
          <div class="card-body" style="padding:0">
            <table class="data-table">
              <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Quotes</th></tr></thead>
              <tbody>
                <?php foreach($users_list as $u):
                  $uqCount = count(array_filter($quotes_list, fn($q) => (int)$q['user_id'] === (int)$u['id'])); ?>
                  <tr>
                    <td style="font-family:var(--M);font-size:.8rem;color:var(--mid)">#<?=$u['id']?></td>
                    <td style="font-weight:600;color:var(--ink)"><?=h($u['name'])?></td>
                    <td style="color:var(--mid)"><?=h($u['email'])?></td>
                    <td><span class="badge" style="<?=$u['role']==='admin'?'background:var(--blue-lt);color:var(--blue)':'background:var(--bg2);color:var(--mid)' ?>"><?=h($u['role'])?></span></td>
                    <td style="font-size:.83rem;color:var(--mid)"><?=h(date('M j, Y',strtotime($u['created_at'])))?></td>
                    <td style="font-family:var(--M);font-size:.85rem;color:var(--blue)"><?=$uqCount?> quote<?=$uqCount!==1?'s':''?></td>
                  </tr>
                <?php endforeach; ?>
                <?php if(empty($users_list)): ?><tr><td colspan="6" style="text-align:center;padding:40px;color:var(--mid)">No users yet.</td></tr><?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </div><!-- /admin-body -->
  </main>
</div><!-- /admin-layout -->
<?php endif; ?>

<script>
// Auto-fill SEO meta from title + content
function genMeta(title) {
  var mt = document.getElementById('meta-title');
  if (mt && !mt.dataset.edited) { mt.value = title; updateMtCount(); }
}
function updateMtCount() {
  var mt = document.getElementById('meta-title');
  var c  = document.getElementById('mt-count');
  if (mt && c) c.textContent = mt.value.length + '/70';
}
function updateCount(el) {
  var c = document.getElementById('char-count');
  if (c) c.textContent = el.value.length + ' characters';
  // Auto meta desc from first 160 chars of content
  var md = document.getElementById('meta-desc');
  if (md && !md.dataset.edited) {
    var plain = el.value.replace(/\n/g,' ').trim();
    md.value = plain.substring(0, 160);
    var mdc = document.getElementById('md-count');
    if (mdc) mdc.textContent = md.value.length + '/160';
  }
}
document.addEventListener('DOMContentLoaded', function() {
  var mt = document.getElementById('meta-title');
  if (mt) mt.addEventListener('input', function() { this.dataset.edited = '1'; updateMtCount(); });
  var md = document.getElementById('meta-desc');
  if (md) md.addEventListener('input', function() { this.dataset.edited = '1'; var c=document.getElementById('md-count'); if(c)c.textContent=this.value.length+'/160'; });
});

// Image preview
function previewImage(input) {
  var preview = document.getElementById('img-preview');
  if (!preview) return;
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
</body>
</html>
