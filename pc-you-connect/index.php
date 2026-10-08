<?php
require_once __DIR__ . '/includes/auth.php';
if (is_logged_in()) redirect(is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php');
 $flash = flash_get();
 $seal  = file_exists(__DIR__ . '/assets/images/seal.png');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PC-YOU! Connect — Campus Portal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<header class="auth-header">
  <div class="d-flex align-items-center gap-3">
    <?php if ($seal): ?>
      <img src="assets/images/seal.png" class="seal-img" alt="PCU Seal">
    <?php else: ?>
      <span class="brand-logo">PCU</span>
    <?php endif; ?>
    <span class="brand-name fs-4">PC-YOU! Connect</span>
  </div>
  <button class="btn btn-navy btn-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#loginModal">Log in</button>
</header>

<div class="auth-hero">
  <h1 class="hero-title">Connect to Your Campus World</h1>
  <p class="hero-sub">Your integrated portal for campus facilities, lost-and-found, announcements, and service notifications – all in one place.</p>
  <ul class="hero-features">
    <li><span class="hero-icon"><i class="bi bi-calendar-week"></i></span>Book facilities instantly</li>
    <li><span class="hero-icon"><i class="bi bi-search"></i></span>Track lost &amp; found items in real-time</li>
    <li><span class="hero-icon"><i class="bi bi-bell"></i></span>Stay updated with announcements</li>
  </ul>
  <button class="btn btn-navy px-4 mt-4" data-bs-toggle="modal" data-bs-target="#loginModal">Get Started</button>
</div>

<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true" data-pc-force-close>
  <div class="modal-dialog modal-dialog-centered login-modal">
    <div class="modal-content">
      <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close"
        onclick="var m=this.closest('.modal');var f=m.querySelector('form');if(f){f.dataset.pcDirty='0';f.dataset.pcSubmitted='1';}bootstrap.Modal.getOrCreateInstance(m).hide();"></button>
      <div class="modal-body">
        <?php foreach ($flash as $f): ?>
          <div class="alert alert-<?= e($f['type']) ?> py-2 small"><?= e($f['msg']) ?></div>
        <?php endforeach; ?>

        <h4 class="modal-title-xl mb-4" id="loginTitle">Student Log in</h4>
        <form method="post" action="actions/login.php">
          <input type="hidden" name="role" id="loginRole" value="student">

          <div class="mb-3">
            <label class="form-label" id="loginIdLabel" for="identifier">Student Number</label>
            <div class="input-icon">
              <i class="bi bi-person"></i>
              <input type="text" class="form-control" id="identifier" name="identifier"
                     placeholder="Enter your Student Number" autocomplete="username" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <div class="input-icon with-eye">
              <i class="bi bi-lock"></i>
              <input type="password" class="form-control" id="password" name="password"
                     placeholder="Enter your password" autocomplete="current-password" required>
              <button type="button" class="eye-btn" data-eye="#password" aria-label="Show password"><i class="bi bi-eye"></i></button>
            </div>
          </div>

          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>

          <div class="d-grid gap-3">
            <button type="submit" class="btn btn-navy">Log in</button>
            <button type="button" class="btn btn-ghost" data-bs-dismiss="modal"
              onclick="var m=this.closest('.modal');var f=m.querySelector('form');if(f){f.dataset.pcDirty='0';f.dataset.pcSubmitted='1';}bootstrap.Modal.getOrCreateInstance(m).hide();">Back</button>
          </div>
        </form>

        <div class="text-center mt-4">
          <a href="#" id="loginModeLink" class="small link-more">Log in as Admin</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js?v=2"></script>
<script src="assets/js/interactions.js?v=2"></script>
<script>
const MODES = {
  student: { title: 'Student Log in', label: 'Student Number', ph: 'Enter your Student Number', link: 'Log in as Admin' },
  admin:   { title: 'Admin Log in',   label: 'User ID',        ph: 'Enter your User ID',        link: 'Log in as Student' }
};
let mode = 'student';
const $ = s => document.querySelector(s);
function applyMode() {
  const m = MODES[mode];
  $('#loginTitle').textContent = m.title;
  $('#loginIdLabel').textContent = m.label;
  $('#identifier').placeholder = m.ph;
  $('#loginRole').value = mode;
  $('#loginModeLink').textContent = m.link;
}
 $('#loginModeLink').addEventListener('click', e => {
  e.preventDefault();
  mode = mode === 'student' ? 'admin' : 'student';
  applyMode();
});
<?php if ($flash): ?>new bootstrap.Modal('#loginModal').show();<?php endif; ?>
</script>
</body>
</html>