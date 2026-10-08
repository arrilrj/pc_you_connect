<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'My Profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
    $cur = $_POST['current_password'] ?? ''; $new = $_POST['new_password'] ?? ''; $con = $_POST['confirm_password'] ?? '';
    if (!password_verify($cur, db_scalar('SELECT password FROM users WHERE id=?', [$u['id']]))) flash_set('Current password is incorrect.', 'danger');
    elseif (strlen($new) < 8) flash_set('New password must be at least 8 characters.', 'danger');
    elseif ($new !== $con) flash_set('New passwords do not match.', 'danger');
    else {
        db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
        log_activity($u['id'], 'password_change', 'Changed password');
        flash_set('Password changed successfully.');
    }
    redirect('profile.php');
}
 $me = db()->prepare('SELECT * FROM users WHERE id=?'); $me->execute([$u['id']]); $me = $me->fetch();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">My Profile</h1><p class="page-sub">Administrator account.</p></div>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-4 text-center h-100">
      <span class="avatar lg mx-auto mb-3"><?= e(initials($me['first_name'], $me['last_name'])) ?></span>
      <h5 class="fw-bold mb-1"><?= e($me['first_name'] . ' ' . $me['last_name']) ?></h5>
      <div class="muted-sm">Administrator · <?= e($me['user_id']) ?></div>
      <div class="muted-sm mb-3"><?= e($me['email'] ?: '') ?></div>
      <div class="muted-sm" style="font-size:12px">Member since <?= fmt_date($me['created_at']) ?></div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card p-4">
      <h6 class="card-title mb-3">Change Password</h6>
      <form method="post">
        <input type="hidden" name="form" value="password">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Current Password</label><input type="password" class="form-control" name="current_password" required></div>
          <div class="col-md-4"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" minlength="8" required></div>
          <div class="col-md-4"><label class="form-label">Confirm New Password</label><input type="password" class="form-control" name="confirm_password" required></div>
          <div class="col-12"><button class="btn btn-navy px-4">Update Password</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>