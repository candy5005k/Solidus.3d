<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
$isAdmin = ($user['role'] ?? '') === 'admin';
$pageTitle = 'Dashboard — Solidus 3D';
$pageDescription = 'Your Solidus 3D dashboard.';
include __DIR__ . '/includes/header.php';
?>
<style>
.dash-hero{background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 50%,#1e3a8a 100%);padding:60px 48px;color:#fff;position:relative;overflow:hidden}
.dash-hero::before{content:'';position:absolute;right:-40px;top:-40px;width:300px;height:300px;background:radial-gradient(circle,rgba(255,255,255,.08),transparent 70%);border-radius:50%}
.dash-hero h1{font-family:var(--D);font-size:clamp(2rem,4vw,3rem);margin-bottom:8px;position:relative}
.dash-hero p{opacity:.85;font-size:1.05rem;position:relative}
.dash-hero-actions{display:flex;gap:12px;margin-top:20px;position:relative}
.dash-hero-actions a{font-family:var(--M);font-size:11px;letter-spacing:.12em;text-transform:uppercase;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;transition:all .2s}
.dash-hero-actions .btn-light{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)}
.dash-hero-actions .btn-light:hover{background:rgba(255,255,255,.25)}
.dash-hero-actions .btn-danger{color:rgba(255,255,255,.7)}
.dash-hero-actions .btn-danger:hover{color:#fca5a5}

.dash-grid{max-width:1200px;margin:40px auto;padding:0 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px}
.dash-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:32px;transition:box-shadow .2s,transform .15s;position:relative;overflow:hidden}
.dash-card:hover{box-shadow:0 8px 32px rgba(37,99,235,.12);transform:translateY(-2px)}
.dash-card-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px}
.dash-card-icon svg{width:22px;height:22px}
.dash-card-icon.blue{background:#dbeafe;color:#2563eb}
.dash-card-icon.green{background:#dcfce7;color:#16a34a}
.dash-card-icon.purple{background:#ede9fe;color:#7c3aed}
.dash-card-icon.amber{background:#fef3c7;color:#d97706}
.dash-card-icon.red{background:#fef2f2;color:#dc2626}
.dash-card-icon.teal{background:#ccfbf1;color:#0d9488}
.dash-card h3{font-family:var(--D);font-size:1.4rem;margin-bottom:8px;color:#111827}
.dash-card p{color:#6b7280;font-size:.95rem;line-height:1.6}
.dash-card a.card-link{display:inline-flex;align-items:center;gap:6px;margin-top:16px;color:#2563eb;font-weight:600;text-decoration:none;font-size:.9rem;transition:gap .15s}
.dash-card a.card-link:hover{gap:10px}

.section-label{max-width:1200px;margin:48px auto 0;padding:0 24px;font-family:var(--M);font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#6b7280;font-weight:600}
</style>

<div class="dash-hero">
  <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
  <p>Manage your Solidus 3D account, projects, and settings.</p>
  <div class="dash-hero-actions">
    <a href="profile.php" class="btn-light">My Profile</a>
    <a href="settings.php" class="btn-light">Settings</a>
    <a href="logout.php" class="btn-danger">↳ Sign Out</a>
  </div>
</div>

<div class="section-label">Quick Actions</div>
<div class="dash-grid">
  <div class="dash-card">
    <div class="dash-card-icon blue">
      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
    </div>
    <h3>Profile</h3>
    <p>View and update your personal information, name, and email.</p>
    <a href="profile.php" class="card-link">Edit Profile →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-icon green">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
    </div>
    <h3>Settings</h3>
    <p>Change your password, manage notifications, and account security.</p>
    <a href="settings.php" class="card-link">Go to Settings →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-icon purple">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
    </div>
    <h3>Get a Quote</h3>
    <p>Request a quote for your next 3D modeling or prototyping project.</p>
    <a href="instant-quote.php" class="card-link">Request Quote →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-icon amber">
      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/></svg>
    </div>
    <h3>Our Services</h3>
    <p>Explore our full range of 3D modeling and manufacturing services.</p>
    <a href="services.php" class="card-link">View Services →</a>
  </div>
</div>

<?php if ($isAdmin): ?>
<div class="section-label">Admin Tools</div>
<div class="dash-grid">
  <div class="dash-card">
    <div class="dash-card-icon red">
      <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
    </div>
    <h3>Blog Admin</h3>
    <p>Publish, edit, or delete blog posts and manage content.</p>
    <a href="admin/" class="card-link">Manage Blog →</a>
  </div>
  <div class="dash-card">
    <div class="dash-card-icon teal">
      <svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
    </div>
    <h3>User Management</h3>
    <p>View and manage all registered user accounts.</p>
    <a href="users.php" class="card-link">Manage Users →</a>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
