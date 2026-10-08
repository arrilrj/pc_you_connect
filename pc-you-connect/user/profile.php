<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'My Profile';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form'] ?? '') === 'profile') {
        $fn = post('first_name'); $ln = post('last_name'); $em = post('email');
        if ($fn === '' || $ln === '') flash_set('First and last name are required.', 'danger');
        elseif ($em !== '' && !filter_var($em, FILTER_VALIDATE_EMAIL)) flash_set('Invalid email address.', 'danger');
        else {
            $dup = db_scalar('SELECT COUNT(*) FROM users WHERE email=? AND id<>?', [$em ?: null, $u['id']]);
            if ($dup) flash_set('Email is already in use.', 'danger');
            else {
                db()->prepare('UPDATE users SET first_name=?, last_name=?, email=? WHERE id=?')->execute([$fn, $ln, $em ?: null, $u['id']]);
                $_SESSION['user']['name'] = $fn . ' ' . $ln;
                $_SESSION['user']['first_name'] = $fn;
                log_activity($u['id'], 'profile_update', 'Updated profile');
                flash_set('Profile updated.');
            }
        }
        redirect('profile.php');
    }
    if (($_POST['form'] ?? '') === 'password') {
        $cur = $_POST['current_password'] ?? ''; $new = $_POST['new_password'] ?? ''; $con = $_POST['confirm_password'] ?? '';
        $row = db()->prepare('SELECT password FROM users WHERE id=?'); $row->execute([$u['id']]);
        if (!password_verify($cur, $row->fetchColumn())) flash_set('Current password is incorrect.', 'danger');
        elseif (strlen($new) < 8) flash_set('New password must be at least 8 characters.', 'danger');
        elseif ($new !== $con) flash_set('New passwords do not match.', 'danger');
        else {
            db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
            log_activity($u['id'], 'password_change', 'Changed password');
            flash_set('Password changed successfully.');
        }
        redirect('profile.php');
    }
}

 $me = db()->prepare('SELECT * FROM users WHERE id=?'); $me->execute([$u['id']]); $me = $me->fetch();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">My Profile</h1><p class="page-sub">Manage your account information.</p></div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-4 text-center h-100">
      <span class="avatar lg mx-auto mb-3"><?= e(initials($me['first_name'], $me['last_name'])) ?></span>
      <h5 class="fw-bold mb-1"><?= e($me['first_name'] . ' ' . $me['last_name']) ?></h5>
      <div class="muted-sm mb-1"><?= ucfirst($me['role']) ?> · <?= e($me['user_id']) ?></div>
      <div class="muted-sm mb-3"><?= e($me['email'] ?: 'No email set') ?></div>
      <div class="muted-sm" style="font-size:12px">Member since <?= fmt_date($me['created_at']) ?></div>
    </div>
  </div>
  <div class="col-lg-8 d-flex flex-column gap-3">
    <div class="card p-4">
      <h6 class="card-title mb-3">Account Information</h6>
      <form method="post">
        <input type="hidden" name="form" value="profile">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">First Name</label>
            <input class="form-control" name="first_name" value="<?= e($me['first_name']) ?>" required></div>
          <div class="col-md-6"><label class="form-label">Last Name</label>
            <input class="form-control" name="last_name" value="<?= e($me['last_name']) ?>" required></div>
          <div class="col-12"><label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= e($me['email']) ?>"></div>
          <div class="col-12"><button class="btn btn-navy px-4">Save Changes</button></div>
        </div>
      </form>
    </div>
    <div class="card p-4">
      <h6 class="card-title mb-3">Change Password</h6>
      <form method="post">
        <input type="hidden" name="form" value="password">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Current Password</label>
            <input type="password" class="form-control" name="current_password" required></div>
          <div class="col-md-4"><label class="form-label">New Password</label>
            <input type="password" class="form-control" name="new_password" minlength="8" required></div>
          <div class="col-md-4"><label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="confirm_password" required></div>
          <div class="col-12"><button class="btn btn-navy px-4">Update Password</button></div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>