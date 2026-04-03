<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$pageTitle = 'Forgot Password — Solidus 3D';
$pageDescription = 'Reset your Solidus 3D account password.';
include __DIR__ . '/includes/header.php';
?>
<style>
.reset-split{display:flex;width:100%;min-height:calc(100vh - 72px)}
.reset-image{flex:1;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#2563eb 100%);position:relative;display:none;overflow:hidden}
.reset-image::before{content:'';position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle cx="60" cy="60" r="80" fill="rgba(255,255,255,.03)"/><circle cx="160" cy="140" r="100" fill="rgba(255,255,255,.02)"/></svg>') center/cover}
.reset-image-content{position:absolute;bottom:12%;left:10%;color:#fff;max-width:440px;z-index:2}
.reset-image-content h2{font-family:var(--D);font-size:clamp(2.5rem,4vw,3.5rem);letter-spacing:1px;margin-bottom:12px;line-height:1}
.reset-image-content p{font-size:1.05rem;opacity:.85;font-weight:300;line-height:1.6}
@media(min-width:900px){.reset-image{display:block}}
.reset-form-container{flex:1;display:flex;align-items:center;justify-content:center;padding:50px 24px;background:#f8fafc}
.reset-card{background:#fff;width:100%;max-width:440px;box-shadow:0 12px 40px rgba(0,0,0,.08);border-radius:16px;overflow:hidden;padding:40px 48px;animation:cardIn .55s ease forwards}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.reset-card h2{font-family:var(--D);font-size:2rem;text-transform:uppercase;margin-bottom:8px;color:#111827}
.reset-card p.desc{color:#6b7280;margin-bottom:24px;font-size:.95rem;line-height:1.6}
.f-group{margin-bottom:18px}
.f-group label{display:block;font-family:var(--M);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#111827;margin-bottom:8px;font-weight:500}
.f-group input{width:100%;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;color:#111827;font-family:var(--B);font-size:15px;padding:14px 16px;outline:none;transition:border-color .2s,box-shadow .2s}
.f-group input:focus{border-color:#2563eb;box-shadow:0 0 0 3px #dbeafe}
.sub-btn{width:100%;background:#2563eb;color:#fff;font-family:var(--M);font-size:13px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;padding:16px;border:none;cursor:pointer;border-radius:8px;transition:background .2s}
.sub-btn:hover{background:#1d4ed8}
.sub-btn:disabled{background:#93c5fd;cursor:not-allowed}
.toggle-link{text-align:center;margin-top:16px;font-size:.9rem;color:#6b7280}
.toggle-link a{color:#2563eb;text-decoration:underline}
.msg{padding:14px 18px;font-size:13px;border-radius:8px;margin-bottom:16px;display:none;align-items:center;gap:8px}
.msg.ok{display:flex;background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd}
.msg.err{display:flex;background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}

/* OTP input styling */
.otp-group{display:flex;gap:10px;justify-content:center;margin-bottom:24px}
.otp-input{width:48px;height:56px;text-align:center;font-family:var(--D);font-size:1.6rem;background:#f8fafc;border:2px solid #e5e7eb;border-radius:10px;color:#111827;outline:none;transition:border-color .2s,box-shadow .2s}
.otp-input:focus{border-color:#2563eb;box-shadow:0 0 0 3px #dbeafe}
.resend-link{text-align:center;font-size:.85rem;color:#6b7280;margin-top:12px}
.resend-link a{color:#2563eb;cursor:pointer;text-decoration:underline}

/* Step indicator */
.steps{display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:32px}
.step-dot{width:10px;height:10px;border-radius:50%;background:#e5e7eb;transition:background .3s,transform .3s}
.step-dot.active{background:#2563eb;transform:scale(1.2)}
.step-dot.done{background:#059669}
.step-line{width:40px;height:2px;background:#e5e7eb}
.step-line.done{background:#059669}
</style>

<div class="reset-split">
  <div class="reset-image">
    <div class="reset-image-content">
      <h2>Account Recovery</h2>
      <p>We'll send a 6-digit verification code to your registered email to help you reset your password securely.</p>
    </div>
  </div>
  <div class="reset-form-container">
    <div class="reset-card">

      <!-- STEP 1: Enter Email -->
      <div id="step-email">
        <div class="steps">
          <div class="step-dot active"></div>
          <div class="step-line"></div>
          <div class="step-dot"></div>
          <div class="step-line"></div>
          <div class="step-dot"></div>
        </div>
        <h2>Forgot Password</h2>
        <p class="desc">Enter your registered email and we'll send you a verification code.</p>
        <div id="email-msg" class="msg"></div>
        <div class="f-group">
          <label for="reset-email">Email Address</label>
          <input type="email" id="reset-email" placeholder="you@company.com" required>
        </div>
        <button class="sub-btn" id="email-btn" onclick="sendOTP()">Send Verification Code</button>
        <p class="toggle-link"><a href="login.php">← Back to Sign In</a></p>
      </div>

      <!-- STEP 2: Enter OTP -->
      <div id="step-otp" style="display:none">
        <div class="steps">
          <div class="step-dot done"></div>
          <div class="step-line done"></div>
          <div class="step-dot active"></div>
          <div class="step-line"></div>
          <div class="step-dot"></div>
        </div>
        <h2>Verify Code</h2>
        <p class="desc">Enter the 6-digit code sent to <strong id="otp-email-display"></strong></p>
        <div id="otp-msg" class="msg"></div>
        <div class="otp-group">
          <input type="text" class="otp-input" maxlength="1" data-otp="0" autofocus>
          <input type="text" class="otp-input" maxlength="1" data-otp="1">
          <input type="text" class="otp-input" maxlength="1" data-otp="2">
          <input type="text" class="otp-input" maxlength="1" data-otp="3">
          <input type="text" class="otp-input" maxlength="1" data-otp="4">
          <input type="text" class="otp-input" maxlength="1" data-otp="5">
        </div>
        <button class="sub-btn" id="otp-btn" onclick="verifyOTP()">Verify Code</button>
        <p class="resend-link">Didn't receive it? <a onclick="sendOTP()">Resend Code</a></p>
        <p class="toggle-link" style="margin-top:12px"><a href="login.php">← Back to Sign In</a></p>
      </div>

      <!-- STEP 3: New Password -->
      <div id="step-reset" style="display:none">
        <div class="steps">
          <div class="step-dot done"></div>
          <div class="step-line done"></div>
          <div class="step-dot done"></div>
          <div class="step-line done"></div>
          <div class="step-dot active"></div>
        </div>
        <h2>New Password</h2>
        <p class="desc">Choose a strong new password for your account.</p>
        <div id="reset-msg" class="msg"></div>
        <div class="f-group">
          <label for="new-pwd">New Password</label>
          <input type="password" id="new-pwd" placeholder="Min. 6 characters" required minlength="6">
        </div>
        <div class="f-group">
          <label for="confirm-pwd">Confirm Password</label>
          <input type="password" id="confirm-pwd" placeholder="Re-enter password" required minlength="6">
        </div>
        <button class="sub-btn" id="reset-btn" onclick="resetPassword()">Reset Password</button>
      </div>

    </div>
  </div>
</div>

<script>
var resetEmail = '';
var resetToken = '';

function showMsg(elId, txt, type) {
  var m = document.getElementById(elId);
  m.textContent = txt;
  m.className = 'msg ' + type;
}

function showStep(stepId) {
  document.getElementById('step-email').style.display = 'none';
  document.getElementById('step-otp').style.display = 'none';
  document.getElementById('step-reset').style.display = 'none';
  document.getElementById(stepId).style.display = 'block';
}

// OTP input auto-advance
document.querySelectorAll('.otp-input').forEach(function(input, idx, all) {
  input.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value && idx < all.length - 1) all[idx + 1].focus();
  });
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Backspace' && !this.value && idx > 0) all[idx - 1].focus();
  });
  input.addEventListener('paste', function(e) {
    e.preventDefault();
    var data = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
    for (var i = 0; i < Math.min(data.length, 6); i++) {
      all[i].value = data[i];
    }
    if (data.length >= 6) all[5].focus();
  });
});

function sendOTP() {
  var email = document.getElementById('reset-email').value;
  if (!email) { showMsg('email-msg', 'Please enter your email.', 'err'); return; }

  var btn = document.getElementById('email-btn');
  btn.disabled = true;
  btn.textContent = 'Sending...';

  fetch('auth-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'forgot_password', email: email })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.success) {
      resetEmail = email;
      document.getElementById('otp-email-display').textContent = email;
      showStep('step-otp');
      document.querySelector('.otp-input').focus();
    } else {
      showMsg('email-msg', d.message, 'err');
    }
  })
  .catch(function() { showMsg('email-msg', 'Network error. Please try again.', 'err'); })
  .finally(function() { btn.disabled = false; btn.textContent = 'Send Verification Code'; });
}

function verifyOTP() {
  var code = '';
  document.querySelectorAll('.otp-input').forEach(function(inp) { code += inp.value; });
  if (code.length !== 6) { showMsg('otp-msg', 'Please enter the full 6-digit code.', 'err'); return; }

  var btn = document.getElementById('otp-btn');
  btn.disabled = true;
  btn.textContent = 'Verifying...';

  fetch('auth-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'verify_otp', email: resetEmail, code: code })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.success) {
      resetToken = d.token || '';
      showStep('step-reset');
    } else {
      showMsg('otp-msg', d.message, 'err');
    }
  })
  .catch(function() { showMsg('otp-msg', 'Network error. Please try again.', 'err'); })
  .finally(function() { btn.disabled = false; btn.textContent = 'Verify Code'; });
}

function resetPassword() {
  var pwd = document.getElementById('new-pwd').value;
  var confirm = document.getElementById('confirm-pwd').value;
  if (pwd !== confirm) { showMsg('reset-msg', 'Passwords do not match.', 'err'); return; }
  if (pwd.length < 6) { showMsg('reset-msg', 'Password must be at least 6 characters.', 'err'); return; }

  var btn = document.getElementById('reset-btn');
  btn.disabled = true;
  btn.textContent = 'Resetting...';

  fetch('auth-handler.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'reset_password', email: resetEmail, token: resetToken, new_password: pwd })
  })
  .then(function(r) { return r.json(); })
  .then(function(d) {
    if (d.success) {
      showMsg('reset-msg', d.message + ' Redirecting to login...', 'ok');
      setTimeout(function() { location.href = 'login.php'; }, 1500);
    } else {
      showMsg('reset-msg', d.message, 'err');
    }
  })
  .catch(function() { showMsg('reset-msg', 'Network error. Please try again.', 'err'); })
  .finally(function() { btn.disabled = false; btn.textContent = 'Reset Password'; });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
