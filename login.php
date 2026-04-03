<?php
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }
$pageTitle = 'Sign In / Register — Solidus 3D';
$pageDescription = 'Sign in or create your Solidus 3D account.';
include __DIR__ . '/includes/header.php';
?>
<style>
.login-split{display:flex;width:100%;min-height:calc(100vh - 72px)}
.login-image{flex:1;background:url('assets/images/hero/login-hero.png') center/cover no-repeat;position:relative;display:none}
.login-image::before{content:'';position:absolute;inset:0;background:linear-gradient(to right,rgba(15,23,42,.8),rgba(37,99,235,.4))}
.login-image-content{position:absolute;bottom:10%;left:10%;color:#fff;max-width:440px;z-index:2}
.login-image-content h2{font-family:var(--D);font-size:clamp(2.5rem,4vw,3.5rem);letter-spacing:1px;margin-bottom:12px;line-height:1}
.login-image-content p{font-size:1.05rem;opacity:.9;font-weight:300;line-height:1.6}
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
.toggle-link{text-align:center;margin-top:16px;font-size:.9rem;color:var(--mid,#6b7280)}
.toggle-link a{color:#2563eb;cursor:pointer;text-decoration:underline}
.msg{padding:12px;font-size:13px;margin-top:12px;border-radius:6px;display:none}
.msg.ok{display:block;background:#dbeafe;color:#1d4ed8;border-left:3px solid #2563eb}
.msg.err{display:block;background:#fef2f2;color:#b91c1c;border-left:3px solid #e63c2f}
</style>

<div class="login-split">
  <div class="login-image">
    <div class="login-image-content">
      <h2>Precision 3D Modeling</h2>
      <p>Enterprise-grade 3D design services with unlimited revisions, NDA protection, and global delivery.</p>
    </div>
  </div>
  <div class="login-form-container">
    <div class="auth-card">
      <!-- LOGIN FORM -->
      <div id="login-view">
        <h2>Sign In</h2>
        <p>Enter your credentials to continue.</p>
        <div class="f-group"><label>Email</label><input type="email" id="login-email"></div>
        <div class="f-group"><label>Password</label><input type="password" id="login-pass"></div>
        <button class="sub-btn" onclick="doLogin()">Sign In</button>
        <div id="login-msg" class="msg"></div>
        <p class="toggle-link" style="margin-top:12px"><a href="forgot-password.php">Forgot your password?</a></p>
        <p class="toggle-link">Don't have an account? <a onclick="showView('register-view')">Register</a></p>
      </div>
      <!-- REGISTER FORM -->
      <div id="register-view" style="display:none">
        <h2>Create Account</h2>
        <p>Join Solidus 3D Modeling today.</p>
        <div class="f-group"><label>Full Name</label><input type="text" id="reg-name"></div>
        <div class="f-group"><label>Email</label><input type="email" id="reg-email"></div>
        <div class="f-group"><label>Password</label><input type="password" id="reg-pass"></div>
        <button class="sub-btn" onclick="doRegister()">Create Account</button>
        <div id="reg-msg" class="msg"></div>
        <p class="toggle-link">Already have an account? <a onclick="showView('login-view')">Sign In</a></p>
      </div>
    </div>
  </div>
</div>

<script>
var API='auth-handler.php';
function showView(id){
  document.getElementById('login-view').style.display='none';
  document.getElementById('register-view').style.display='none';
  document.getElementById(id).style.display='block';
}
function showMsg(elId,txt,type){
  var m=document.getElementById(elId);
  m.textContent=txt;m.className='msg '+type;
}
function doLogin(){
  var email=document.getElementById('login-email').value;
  var pass=document.getElementById('login-pass').value;
  fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'login',email:email,password:pass})
  }).then(r=>r.json()).then(d=>{
    if(d.success){showMsg('login-msg',d.message,'ok');setTimeout(()=>location.href=d.redirect,600);}
    else showMsg('login-msg',d.message,'err');
  }).catch(()=>showMsg('login-msg','Network error','err'));
}
function doRegister(){
  var name=document.getElementById('reg-name').value;
  var email=document.getElementById('reg-email').value;
  var pass=document.getElementById('reg-pass').value;
  fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({action:'register',name:name,email:email,password:pass})
  }).then(r=>r.json()).then(d=>{
    if(d.success){showMsg('reg-msg',d.message,'ok');setTimeout(()=>location.href=d.redirect,600);}
    else showMsg('reg-msg',d.message,'err');
  }).catch(()=>showMsg('reg-msg','Network error','err'));
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
