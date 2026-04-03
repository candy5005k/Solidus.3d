<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: quote-dashboard.php'); exit; }
$pageTitle = 'Instant Quote Login';
$pageDescription = 'Access the Solidus 3D Instant Quote Dashboard.';
include __DIR__ . '/includes/header.php';
?>
<style>
.login-split{display:flex;width:100%;min-height:calc(100vh - 72px)}
.login-image{flex:1;background:url('assets/images/hero/login-hero.png') center/cover no-repeat;position:relative;display:none}
.login-image::before{content:'';position:absolute;inset:0;background:linear-gradient(to right,rgba(15,23,42,.8),rgba(37,99,235,.4))}
.login-image-content{position:absolute;bottom:10%;left:10%;color:#fff;max-width:440px;z-index:2}
.login-image-content h2{font-family:var(--D);font-size:clamp(2.5rem,4vw,3.5rem);letter-spacing:1px;margin-bottom:12px;line-height:1}
@media(min-width:900px){.login-image{display:block}}
.login-form-container{flex:1;display:flex;align-items:center;justify-content:center;padding:50px 24px;background:var(--paper,#f8fafc)}
.auth-card{background:#fff;width:100%;max-width:440px;box-shadow:0 12px 40px rgba(0,0,0,.08);border-radius:12px;overflow:hidden;padding:40px 48px;animation:cardIn .55s ease forwards}
@keyframes cardIn{from{opacity:0;transform:translateY(28px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.auth-card h2{font-family:var(--D);font-size:2rem;text-transform:uppercase;margin-bottom:8px;color:var(--ink,#111827)}
.auth-card p{color:var(--mid,#6b7280);margin-bottom:24px;font-size:.95rem}
.f-group{margin-bottom:18px}
.f-group label{display:block;font-family:var(--M);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--ink,#111827);margin-bottom:8px;font-weight:500}
.f-group input{width:100%;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;color:#111827;font-family:var(--B);font-size:15px;padding:14px 16px;outline:none;transition:border-color .2s,box-shadow .2s}
.f-group input:focus{border-color:#2563eb;box-shadow:0 0 0 3px #dbeafe}
.sub-btn{width:100%;background:#2563eb;color:#fff;font-family:var(--M);font-size:13px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;padding:16px;border:none;cursor:pointer;border-radius:6px;transition:background .2s}
.sub-btn:hover{background:#1d4ed8}
.msg{padding:12px;font-size:13px;margin-top:12px;border-radius:6px;display:none}
.msg.ok{display:block;background:#dbeafe;color:#1d4ed8;border-left:3px solid #2563eb}
.msg.err{display:block;background:#fef2f2;color:#b91c1c;border-left:3px solid #e63c2f}
</style>

<div class="login-split">
  <div class="login-image">
    <div class="login-image-content">
      <h2>Instant Quote Portal</h2>
      <p>Secure, NDA-protected platform for fast CAD & 3D Modeling quotes.</p>
    </div>
  </div>
  <div class="login-form-container">
    <div class="auth-card">
      <div id="step-1">
        <h2>Enter Work Email</h2>
        <p>We'll send a secure OTP to verify your identity.</p>
        <div class="f-group"><label>Work Email</label><input type="email" id="q-email" placeholder="name@company.com"></div>
        <button class="sub-btn" onclick="sendOTP()">Send OTP</button>
        <div id="msg-1" class="msg"></div>
      </div>
      
      <div id="step-2" style="display:none">
        <h2>Verify OTP</h2>
        <p>Enter the 6-digit code sent to your email.</p>
        <div class="f-group"><label>Verification Code</label><input type="text" id="q-otp" placeholder="123456" maxlength="6"></div>
        <button class="sub-btn" onclick="verifyOTP()">Verify & Access Dashboard</button>
        <div id="msg-2" class="msg"></div>
        <p style="text-align:center;margin-top:16px;font-size:12px;cursor:pointer;color:#2563eb" onclick="location.reload()">← Use a different email</p>
      </div>
    </div>
  </div>
</div>

<script>
function showMsg(id, text, type) {
    var el = document.getElementById(id);
    el.textContent = text;
    el.className = 'msg ' + type;
}

function sendOTP() {
    var email = document.getElementById('q-email').value;
    if(!email) return showMsg('msg-1', 'Email is required', 'err');
    
    fetch('api/quote-auth.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({action:'send_otp', email:email})
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            document.getElementById('step-1').style.display = 'none';
            document.getElementById('step-2').style.display = 'block';
            sessionStorage.setItem('temp_quote_email', email);
        } else {
            showMsg('msg-1', d.message, 'err');
        }
    }).catch(e=>showMsg('msg-1', 'Network error', 'err'));
}

function verifyOTP() {
    var email = sessionStorage.getItem('temp_quote_email');
    var otp = document.getElementById('q-otp').value;
    if(!otp) return showMsg('msg-2', 'OTP is required', 'err');
    
    fetch('api/quote-auth.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({action:'verify_otp', email:email, otp:otp})
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            showMsg('msg-2', 'Verified! Redirecting...', 'ok');
            setTimeout(()=>location.href = 'quote-dashboard.php', 1000);
        } else {
            showMsg('msg-2', d.message, 'err');
        }
    });
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
