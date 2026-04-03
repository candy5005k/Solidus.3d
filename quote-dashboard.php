<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/config/database.php';

$user = currentUser();
$pageTitle = 'My Quotes — Solidus 3D';
$pageDescription = 'Track your project quote requests with Solidus 3D Modeling.';

// Fetch user's quotes
$stmt = $pdo->prepare('SELECT * FROM quotes WHERE user_id = :uid ORDER BY created_at DESC');
$stmt->execute([':uid' => $user['id']]);
$quotes = $stmt->fetchAll();

// Status badge config
$statusConfig = [
    'new'       => ['label' => 'New',        'color' => '#3b82f6', 'bg' => '#dbeafe'],
    'reviewing' => ['label' => 'Reviewing',  'color' => '#d97706', 'bg' => '#fef3c7'],
    'quoted'    => ['label' => 'Quoted',     'color' => '#7c3aed', 'bg' => '#ede9fe'],
    'accepted'  => ['label' => 'Accepted',   'color' => '#059669', 'bg' => '#dcfce7'],
    'declined'  => ['label' => 'Declined',   'color' => '#dc2626', 'bg' => '#fef2f2'],
    'completed' => ['label' => 'Completed',  'color' => '#0d9488', 'bg' => '#ccfbf1'],
];

include __DIR__ . '/includes/header.php';
?>
<style>
/* ── Variables ── */
:root{--blue:#2563eb;--blue-dk:#1d4ed8;--ink:#111827;--body:#4b5563;--mid:#6b7280;--line:#e5e7eb;--paper:#ffffff;--bg2:#f8fafc;--dark:#0f172a}

/* ── Dashboard layout ── */
.qd-page{min-height:100vh;background:var(--bg2);padding-bottom:60px}
.qd-hero{background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 55%,#1e3a8a 100%);padding:48px;color:#fff;position:relative;overflow:hidden}
.qd-hero::after{content:'';position:absolute;right:-60px;top:-60px;width:280px;height:280px;background:radial-gradient(circle,rgba(255,255,255,.1),transparent 70%);border-radius:50%}
.qd-hero h1{font-family:'Bebas Neue',sans-serif;font-size:clamp(2rem,4vw,3rem);margin:0 0 6px;position:relative}
.qd-hero p{margin:0;opacity:.8;font-size:1rem;position:relative}
.qd-hero-bar{display:flex;align-items:center;gap:16px;margin-top:20px;flex-wrap:wrap;position:relative}
.qd-hero-btn{font-family:'DM Mono',monospace;font-size:11px;letter-spacing:.12em;text-transform:uppercase;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;transition:all .2s}
.qd-hero-btn.white{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)}
.qd-hero-btn.white:hover{background:rgba(255,255,255,.25)}
.qd-hero-btn.outline{color:rgba(255,255,255,.7);font-size:12px}
.qd-hero-btn.outline:hover{color:#fca5a5}

/* ── Content ── */
.qd-wrap{max-width:1100px;margin:0 auto;padding:40px 24px}
.qd-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;flex-wrap:wrap;gap:16px}
.qd-top h2{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--ink);margin:0}
.new-quote-btn{background:var(--blue);color:#fff;font-family:'DM Mono',monospace;font-size:12px;letter-spacing:.12em;text-transform:uppercase;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:8px;transition:background .2s,transform .15s;box-shadow:0 4px 16px rgba(37,99,235,.25)}
.new-quote-btn:hover{background:var(--blue-dk);transform:translateY(-1px)}

/* ── Quote cards ── */
.quote-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:28px 32px;margin-bottom:20px;box-shadow:0 2px 12px rgba(0,0,0,.04);transition:box-shadow .2s,transform .15s}
.quote-card:hover{box-shadow:0 8px 32px rgba(37,99,235,.1);transform:translateY(-2px)}
.qc-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
.qc-service{font-family:'Bebas Neue',sans-serif;font-size:1.4rem;color:var(--ink);margin:0 0 4px}
.qc-meta{display:flex;gap:16px;flex-wrap:wrap;margin-top:6px}
.qc-meta span{font-size:.82rem;color:var(--mid);font-family:'DM Mono',monospace}
.status-badge{display:inline-block;padding:5px 14px;border-radius:980px;font-family:'DM Mono',monospace;font-size:10px;letter-spacing:.14em;text-transform:uppercase;font-weight:600;white-space:nowrap}
.qc-details{margin-top:16px;padding-top:16px;border-top:1px solid var(--line);font-size:.9rem;color:var(--body);line-height:1.6}
.qc-admin-note{background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:14px 18px;margin-top:16px;font-size:.88rem;color:#92400e}
.qc-admin-note strong{display:block;margin-bottom:4px;color:#78350f;font-size:.8rem;text-transform:uppercase;letter-spacing:.08em}
.qc-price{display:inline-block;background:#dbeafe;color:#1d4ed8;font-family:'DM Mono',monospace;font-size:1rem;font-weight:700;padding:8px 18px;border-radius:8px;margin-top:12px}

/* ── Empty state ── */
.empty-state{text-align:center;padding:80px 24px;background:#fff;border:2px dashed var(--line);border-radius:20px}
.empty-state svg{width:64px;height:64px;color:#d1d5db;margin:0 auto 20px;display:block}
.empty-state h3{font-family:'Bebas Neue',sans-serif;font-size:1.8rem;color:var(--ink);margin:0 0 8px}
.empty-state p{color:var(--mid);margin:0 0 24px}

/* ── Stats row ── */
.qd-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:32px}
.stat-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:20px 24px;text-align:center}
.stat-card .num{font-family:'Bebas Neue',sans-serif;font-size:2.2rem;color:var(--blue)}
.stat-card .lbl{font-size:.8rem;color:var(--mid);font-family:'DM Mono',monospace;letter-spacing:.1em;text-transform:uppercase}

@media(max-width:600px){
  .qd-hero{padding:32px 24px}
  .qd-wrap{padding:24px 16px}
  .quote-card{padding:20px}
}
</style>

<div class="qd-page">
  <div class="qd-hero">
    <h1>My Quotes</h1>
    <p>Welcome back, <?= h($user['name']) ?> — track all your project requests here.</p>
    <div class="qd-hero-bar">
      <a href="profile.php" class="qd-hero-btn white">My Profile</a>
      <a href="settings.php" class="qd-hero-btn white">Settings</a>
      <a href="logout.php" class="qd-hero-btn outline">↳ Sign Out</a>
    </div>
  </div>

  <div class="qd-wrap">

    <!-- Stats -->
    <?php
    $totalQ    = count($quotes);
    $newQ      = count(array_filter($quotes, fn($q) => $q['status'] === 'new'));
    $activeQ   = count(array_filter($quotes, fn($q) => in_array($q['status'], ['reviewing','quoted','accepted'])));
    $doneQ     = count(array_filter($quotes, fn($q) => $q['status'] === 'completed'));
    ?>
    <div class="qd-stats">
      <div class="stat-card"><div class="num"><?= $totalQ ?></div><div class="lbl">Total Requests</div></div>
      <div class="stat-card"><div class="num"><?= $newQ ?></div><div class="lbl">New / Pending</div></div>
      <div class="stat-card"><div class="num"><?= $activeQ ?></div><div class="lbl">In Progress</div></div>
      <div class="stat-card"><div class="num"><?= $doneQ ?></div><div class="lbl">Completed</div></div>
    </div>

    </div>

    <!-- NEW QUOTE FORM -->
    <div class="quote-card" style="margin-bottom: 40px; border-top: 4px solid var(--blue)">
      <h3 style="margin-top:0;font-family:'Bebas Neue',sans-serif;font-size:24px;color:var(--ink)">Request Instant Quote</h3>
      <p style="color:var(--mid);margin-bottom:24px;font-size:14px">Upload your CAD/3D files carefully. We review these to provide accurate manufacturing quotes. NDA applies.</p>
      
      <form id="instant-quote-form" enctype="multipart/form-data" method="POST" action="api/submit-quote.php" style="display:grid;gap:20px">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
          <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px">Service Required</label>
            <select name="service" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:6px;background:#f8fafc;outline:none" required>
              <option value="3D CAD Modeling">3D CAD Modeling</option>
              <option value="Reverse Engineering">Reverse Engineering</option>
              <option value="CNC Machining">CNC Machining</option>
              <option value="3D Printing">3D Printing</option>
              <option value="Vacuum Casting">Vacuum Casting</option>
            </select>
          </div>
          <div>
            <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px">Timeline</label>
            <select name="timeline" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:6px;background:#f8fafc;outline:none">
              <option value="Standard (7-10 Days)">Standard (7-10 Days)</option>
              <option value="Express (3-5 Days)">Express (3-5 Days)</option>
              <option value="Urgent (24-48 Hours)">Urgent (24-48 Hours)</option>
            </select>
          </div>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px">Upload File (Optional)</label>
          <input type="file" name="attachment" style="width:100%;padding:12px;border:1px dashed #cbd5e1;border-radius:6px;background:#f8fafc" accept=".pdf,.doc,.docx,.step,.stl,.obj,.iges,.zip,.png,.jpg">
          <small style="color:#64748b;font-size:11px;display:block;margin-top:4px">Max 20MB. Accepted formats: STL, STEP, OBJ, IGES, ZIP, PDF.</small>
        </div>

        <div>
          <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:1px">Project Brief / Details</label>
          <textarea name="details" rows="4" style="width:100%;padding:12px;border:1px solid #e5e7eb;border-radius:6px;background:#f8fafc;outline:none;font-family:inherit" placeholder="Describe materials, quantities, specific tolerances, etc." required></textarea>
        </div>

        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:20px;display:flex;flex-direction:column;gap:12px">
            <h4 style="margin:0;color:#1e3a8a;font-family:'DM Mono',monospace;font-size:14px">🔒 NDA & Terms Agreement</h4>
            <p style="margin:0;font-size:13px;color:#3b82f6;line-height:1.5">To submit this quote and accept our standard Non-Disclosure Agreement (NDA), please re-enter the OTP sent to your email.</p>
            <div>
                 <input type="text" name="nda_otp" placeholder="Enter Registration OTP" style="padding:10px 14px;border:1px solid #bfdbfe;border-radius:6px;outline:none;font-family:'DM Mono',monospace" required maxlength="6">
            </div>
        </div>
        
        <div>
          <button type="submit" class="new-quote-btn" style="border:none;cursor:pointer;font-size:14px">Submit Quote Request</button>
        </div>
      </form>
    </div>

    <!-- Header for list -->
    <div class="qd-top">
      <h2>Your Past Requests</h2>
    </div>

    <!-- Quote list -->
    <?php if (empty($quotes)): ?>
      <div class="empty-state">
        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
        <h3>No past quotes yet</h3>
        <p>Submit your first project request above and we'll get back to you within 24 hours.</p>
      </div>
    <?php else: ?>
      <?php foreach ($quotes as $q):
        $sc = $statusConfig[$q['status']] ?? $statusConfig['new'];
        $date = date('M j, Y', strtotime($q['created_at']));
      ?>
        <div class="quote-card">
          <div class="qc-top">
            <div>
              <div class="qc-service"><?= h($q['service']) ?></div>
              <div class="qc-meta">
                <span>📅 <?= h($date) ?></span>
                <?php if ($q['timeline']): ?><span>⏱ <?= h($q['timeline']) ?></span><?php endif; ?>
                <?php if ($q['company']): ?><span>🏢 <?= h($q['company']) ?></span><?php endif; ?>
                <span>#<?= str_pad((string)$q['id'], 4, '0', STR_PAD_LEFT) ?></span>
              </div>
            </div>
            <span class="status-badge" style="color:<?= $sc['color'] ?>;background:<?= $sc['bg'] ?>"><?= $sc['label'] ?></span>
          </div>

          <div class="qc-details">
            <?= h(mb_substr($q['project_details'], 0, 200)) ?><?= mb_strlen($q['project_details']) > 200 ? '...' : '' ?>
          </div>

          <?php if ($q['admin_notes']): ?>
            <div class="qc-admin-note">
              <strong>📋 Note from Solidus 3D team</strong>
              <?= h($q['admin_notes']) ?>
            </div>
          <?php endif; ?>

          <?php if ($q['quoted_price']): ?>
            <div class="qc-price">💰 Quoted: ₹<?= number_format((float)$q['quoted_price'], 2) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
