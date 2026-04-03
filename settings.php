<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
$user = currentUser();
$pageTitle = 'Settings — Solidus 3D';
$pageDescription = 'Manage your account settings.';
include __DIR__ . '/includes/header.php';
?>
<style>
.page-hero{background:linear-gradient(135deg,#2563eb,#1d4ed8);padding:48px;color:#fff}
.page-hero h1{font-family:var(--D);font-size:clamp(1.8rem,3vw,2.6rem);margin-bottom:6px}
.page-hero p{opacity:.85}
.page-wrap{max-width:800px;margin:40px auto;padding:0 24px 60px}

/* Settings cards */
.settings-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:0;margin-bottom:24px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.04)}
.settings-card-header{display:flex;align-items:center;gap:14px;padding:24px 32px;border-bottom:1px solid #f1f5f9}
.settings-card-header .icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.settings-card-header .icon.blue{background:#dbeafe;color:#2563eb}
.settings-card-header .icon.amber{background:#fef3c7;color:#d97706}
.settings-card-header .icon.red{background:#fef2f2;color:#dc2626}
.settings-card-header .icon svg{width:20px;height:20px}
.settings-card-header h3{font-family:var(--D);font-size:1.3rem;color:#111827;margin:0}
.settings-card-header p{color:#6b7280;font-size:.85rem;margin:2px 0 0}
.settings-card-body{padding:28px 32px}

/* Form elements */
.s-group{margin-bottom:18px}
.s-group label{display:block;font-family:var(--M);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#111827;margin-bottom:8px;font-weight:500}
.s-group input{width:100%;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;color:#111827;font-family:var(--B);font-size:15px;padding:14px 16px;outline:none;transition:border-color .2s,box-shadow .2s}
.s-group input:focus{border-color:#2563eb;box-shadow:0 0 0 3px #dbeafe}
.s-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.s-row{grid-template-columns:1fr}}
.save-btn{background:#2563eb;color:#fff;font-family:var(--M);font-size:13px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;padding:14px 32px;border:none;cursor:pointer;border-radius:8px;transition:background .2s,transform .15s;margin-top:8px}
.save-btn:hover{background:#1d4ed8;transform:translateY(-1px)}
.save-btn:disabled{background:#93c5fd;cursor:not-allowed;transform:none}
.del-btn{background:#dc2626;color:#fff;font-family:var(--M);font-size:13px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;padding:14px 32px;border:none;cursor:pointer;border-radius:8px;transition:background .2s,transform .15s;margin-top:8px}
.del-btn:hover{background:#b91c1c;transform:translateY(-1px)}

/* Messages */
.msg{padding:14px 18px;font-size:13px;border-radius:8px;margin-bottom:16px;display:none;align-items:center;gap:10px}
.msg.ok{display:flex;background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd}
.msg.err{display:flex;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}

/* Password strength meter */
.strength-bar{height:4px;border-radius:2px;background:#e5e7eb;margin-top:8px;overflow:hidden}
.strength-bar .fill{height:100%;border-radius:2px;width:0;transition:width .3s,background .3s}
.strength-text{font-size:11px;margin-top:4px;color:#6b7280;font-family:var(--M)}

.back-link{display:inline-flex;align-items:center;gap:6px;color:#2563eb;font-weight:600;font-size:.9rem;text-decoration:none;margin-top:24px;transition:gap .15s}
.back-link:hover{gap:10px}

/* Toggle switch */
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid #f1f5f9}
.toggle-row:last-child{border-bottom:none}
.toggle-info h4{font-size:.95rem;color:#111827;margin:0 0 2px;font-weight:600}
.toggle-info p{font-size:.85rem;color:#6b7280;margin:0}
.toggle-switch{position:relative;width:48px;height:26px;flex-shrink:0}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;cursor:pointer;inset:0;background:#d1d5db;border-radius:980px;transition:.2s}
.toggle-slider::before{content:'';position:absolute;height:20px;width:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s}
.toggle-switch input:checked + .toggle-slider{background:#2563eb}
.toggle-switch input:checked + .toggle-slider::before{transform:translateX(22px)}
</style>

<div class="page-hero">
  <h1>Settings</h1>
  <p>Account preferences and security.</p>
</div>
<div class="page-wrap">

  <!-- CHANGE PASSWORD -->
  <div class="settings-card">
    <div class="settings-card-header">
      <div class="icon blue">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
      </div>
      <div>
        <h3>Change Password</h3>
        <p>Update your password to keep your account secure.</p>
      </div>
    </div>
    <div class="settings-card-body">
      <div id="pwd-msg" class="msg"></div>
      <form onsubmit="return changePassword(event)">
        <div class="s-group">
          <label for="current-pass">Current Password</label>
          <input type="password" id="current-pass" required minlength="6" autocomplete="current-password">
        </div>
        <div class="s-row">
          <div class="s-group">
            <label for="new-pass">New Password</label>
            <input type="password" id="new-pass" required minlength="6" autocomplete="new-password" oninput="checkStrength(this.value)">
            <div class="strength-bar"><div class="fill" id="str-fill"></div></div>
            <div class="strength-text" id="str-text"></div>
          </div>
          <div class="s-group">
            <label for="confirm-pass">Confirm New Password</label>
            <input type="password" id="confirm-pass" required minlength="6" autocomplete="new-password">
          </div>
        </div>
        <button type="submit" class="save-btn" id="pwd-btn">Update Password</button>
      </form>
    </div>
  </div>

  <!-- NOTIFICATION PREFERENCES -->
  <div class="settings-card">
    <div class="settings-card-header">
      <div class="icon amber">
        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
      </div>
      <div>
        <h3>Notifications</h3>
        <p>Manage your email notification preferences.</p>
      </div>
    </div>
    <div class="settings-card-body">
      <div class="toggle-row">
        <div class="toggle-info">
          <h4>Project Updates</h4>
          <p>Receive email updates when your project status changes.</p>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="toggle-row">
        <div class="toggle-info">
          <h4>Quote Confirmations</h4>
          <p>Get a confirmation email when you submit a quote request.</p>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="toggle-row">
        <div class="toggle-info">
          <h4>Newsletter & Tips</h4>
          <p>Occasional emails with 3D modeling tips and company updates.</p>
        </div>
        <label class="toggle-switch">
          <input type="checkbox">
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>
  </div>

  <!-- DANGER ZONE -->
  <div class="settings-card" style="border-color:#fca5a5">
    <div class="settings-card-header" style="border-bottom-color:#fef2f2">
      <div class="icon red">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
      </div>
      <div>
        <h3>Danger Zone</h3>
        <p>Irreversible actions. Proceed with caution.</p>
      </div>
    </div>
    <div class="settings-card-body">
      <p style="color:#6b7280;font-size:.9rem;margin-bottom:16px">Once you delete your account, all your data will be permanently removed. This action cannot be undone.</p>
      <button class="del-btn" onclick="deleteAccount()">Delete My Account</button>
    </div>
  </div>

  <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</div>

<script>
function showMsg(elId, txt, type) {
  var m = document.getElementById(elId);
  m.textContent = txt;
  m.className = 'msg ' + type;
}

function checkStrength(val) {
  var fill = document.getElementById('str-fill');
  var txt = document.getElementById('str-text');
  var score = 0;
  if (val.length >= 6) score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  var levels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
  var colors = ['#e5e7eb', '#dc2626', '#f59e0b', '#059669', '#2563eb', '#7c3aed'];
  var widths = [0, 20, 40, 60, 80, 100];
  fill.style.width = widths[score] + '%';
  fill.style.background = colors[score];
  txt.textContent = val.length > 0 ? levels[score] : '';
  txt.style.color = colors[score];
}

function changePassword(e) {
  e.preventDefault();
  var current = document.getElementById('current-pass').value;
  var newPass = document.getElementById('new-pass').value;
  var confirm = document.getElementById('confirm-pass').value;

  if (newPass !== confirm) {
    showMsg('pwd-msg', 'New passwords do not match.', 'err');
    return false;
  }
  if (newPass.length < 6) {
    showMsg('pwd-msg', 'Password must be at least 6 characters.', 'err');
    return false;
  }

  var btn = document.getElementById('pwd-btn');
  btn.disabled = true;
  btn.textContent = 'Updating...';

  fetch('auth-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'change_password', current_password: current, new_password: newPass })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    showMsg('pwd-msg', d.message, d.success ? 'ok' : 'err');
    if (d.success) {
      document.getElementById('current-pass').value = '';
      document.getElementById('new-pass').value = '';
      document.getElementById('confirm-pass').value = '';
      checkStrength('');
    }
  })
  .catch(function() { showMsg('pwd-msg', 'Network error. Please try again.', 'err'); })
  .finally(function() { btn.disabled = false; btn.textContent = 'Update Password'; });

  return false;
}

function deleteAccount() {
  if (!confirm('Are you sure you want to delete your account? This action cannot be undone.')) return;
  if (!confirm('This is your FINAL confirmation. All your data will be permanently deleted.')) return;

  fetch('auth-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete_account' })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.success) {
      alert('Account deleted successfully.');
      location.href = d.redirect || 'login.php';
    } else {
      alert(d.message || 'Failed to delete account.');
    }
  })
  .catch(function() { alert('Network error. Please try again.'); });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
