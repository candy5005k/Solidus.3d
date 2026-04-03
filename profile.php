<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
$pageTitle = 'My Profile — Solidus 3D';
$pageDescription = 'View and manage your profile.';
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:48px;color:#fff}
.page-hero h1{font-family:var(--D);font-size:clamp(1.8rem,3vw,2.6rem);margin-bottom:6px}
.page-hero p{opacity:.85}
.page-wrap{max-width:800px;margin:40px auto;padding:0 24px 60px}

/* Profile Card */
.profile-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.profile-header{background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);padding:32px 40px;display:flex;align-items:center;gap:20px;border-bottom:1px solid #e5e7eb}
.profile-avatar{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1d4ed8);display:flex;align-items:center;justify-content:center;font-family:var(--D);font-size:2rem;color:#fff;flex-shrink:0;box-shadow:0 4px 16px rgba(37,99,235,.3)}
.profile-meta h2{font-family:var(--D);font-size:1.6rem;color:#111827;margin:0 0 2px}
.profile-meta span{font-size:.85rem;color:#6b7280;font-family:var(--M);letter-spacing:.08em;text-transform:uppercase}
.profile-body{padding:32px 40px}

/* Form styles */
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.form-group{margin-bottom:0}
.form-group.full{grid-column:1/-1}
.form-group label{display:block;font-family:var(--M);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#111827;margin-bottom:8px;font-weight:500}
.form-group input{width:100%;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;color:#111827;font-family:var(--B);font-size:15px;padding:14px 16px;outline:none;transition:border-color .2s,box-shadow .2s}
.form-group input:focus{border-color:#2563eb;box-shadow:0 0 0 3px #dbeafe}
.form-group input:disabled{background:#f1f5f9;color:#9ca3af;cursor:not-allowed}
.form-actions{display:flex;align-items:center;gap:16px;margin-top:28px;padding-top:24px;border-top:1px solid #e5e7eb}
.save-btn{background:#2563eb;color:#fff;font-family:var(--M);font-size:13px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;padding:14px 32px;border:none;cursor:pointer;border-radius:8px;transition:background .2s,transform .15s}
.save-btn:hover{background:#1d4ed8;transform:translateY(-1px)}
.save-btn:disabled{background:#93c5fd;cursor:not-allowed;transform:none}
.cancel-btn{background:transparent;color:#6b7280;font-family:var(--M);font-size:12px;letter-spacing:.1em;text-transform:uppercase;padding:14px 24px;border:1px solid #e5e7eb;cursor:pointer;border-radius:8px;transition:all .2s;text-decoration:none}
.cancel-btn:hover{border-color:#2563eb;color:#2563eb}

/* Messages */
.msg{padding:14px 18px;font-size:13px;border-radius:8px;margin-bottom:20px;display:none;align-items:center;gap:10px}
.msg.ok{display:flex;background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd}
.msg.err{display:flex;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}
.msg svg{flex-shrink:0;width:18px;height:18px}

/* Role badge */
.role-badge{display:inline-block;padding:4px 12px;border-radius:980px;font-family:var(--M);font-size:10px;letter-spacing:.14em;text-transform:uppercase;font-weight:600}
.role-badge.admin{background:#dbeafe;color:#1d4ed8}
.role-badge.user{background:#f1f5f9;color:#6b7280}

.back-link{display:inline-flex;align-items:center;gap:6px;color:#2563eb;font-weight:600;font-size:.9rem;text-decoration:none;margin-top:24px;transition:gap .15s}
.back-link:hover{gap:10px}
</style>

<div class="page-hero">
  <h1>My Profile</h1>
  <p>View and update your personal information.</p>
</div>
<div class="page-wrap">

  <div id="profile-msg" class="msg"></div>

  <div class="profile-card">
    <div class="profile-header">
      <div class="profile-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
      <div class="profile-meta">
        <h2 id="display-name"><?php echo htmlspecialchars($user['name']); ?></h2>
        <span class="role-badge <?php echo $user['role']; ?>"><?php echo htmlspecialchars($user['role']); ?></span>
      </div>
    </div>
    <div class="profile-body">
      <form id="profile-form" onsubmit="return saveProfile(event)">
        <div class="form-row">
          <div class="form-group">
            <label for="pf-name">Full Name</label>
            <input type="text" id="pf-name" value="<?php echo htmlspecialchars($user['name']); ?>" required minlength="2">
          </div>
          <div class="form-group">
            <label for="pf-email">Email Address</label>
            <input type="email" id="pf-email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Account Role</label>
            <input type="text" value="<?php echo htmlspecialchars(ucfirst($user['role'])); ?>" disabled>
          </div>
          <div class="form-group">
            <label>User ID</label>
            <input type="text" value="#<?php echo $user['id']; ?>" disabled>
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="save-btn" id="save-btn">Save Changes</button>
          <a href="dashboard.php" class="cancel-btn">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

<script>
function showMsg(txt, type) {
  var m = document.getElementById('profile-msg');
  m.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="' +
    (type === 'ok'
      ? 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z'
      : 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z') +
    '" clip-rule="evenodd"/></svg>' + txt;
  m.className = 'msg ' + type;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function saveProfile(e) {
  e.preventDefault();
  var btn = document.getElementById('save-btn');
  btn.disabled = true;
  btn.textContent = 'Saving...';

  var name = document.getElementById('pf-name').value;
  var email = document.getElementById('pf-email').value;

  fetch('auth-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update_profile', name: name, email: email })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.success) {
      showMsg(d.message, 'ok');
      document.getElementById('display-name').textContent = name;
      document.querySelector('.profile-avatar').textContent = name.charAt(0).toUpperCase();
    } else {
      showMsg(d.message, 'err');
    }
  })
  .catch(function() { showMsg('Network error. Please try again.', 'err'); })
  .finally(function() { btn.disabled = false; btn.textContent = 'Save Changes'; });

  return false;
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
