<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
$pageTitle = 'Users — Solidus 3D Admin';
$pageDescription = 'Manage users.';
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:48px;color:#fff}
.page-hero h1{font-family:var(--D);font-size:clamp(1.8rem,3vw,2.6rem);margin-bottom:6px}
.page-hero p{opacity:.85}
.page-wrap{max-width:1000px;margin:40px auto;padding:0 24px}
.u-table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.06)}
.u-table th,.u-table td{padding:14px 16px;text-align:left;font-size:.9rem}
.u-table th{background:#f8fafc;color:#111827;font-weight:600;border-bottom:2px solid #e5e7eb}
.u-table td{border-bottom:1px solid #f1f5f9;color:#4b5563}
.u-table tr:hover td{background:#f8fafc}
</style>
<div class="page-hero"><h1>Users</h1><p>Admin — Manage registered users.</p></div>
<div class="page-wrap">
<table class="u-table">
<thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
<tr>
<td><?php echo $u['id']; ?></td>
<td><?php echo htmlspecialchars($u['name']); ?></td>
<td><?php echo htmlspecialchars($u['email']); ?></td>
<td><?php echo htmlspecialchars($u['role']); ?></td>
<td><?php echo $u['created_at']; ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p style="margin-top:24px"><a href="dashboard.php" style="color:#2563eb;font-weight:600">← Back to Dashboard</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
