<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'prefs') {
    save_pref($u['id'], [
        'notify_reservation'  => isset($_POST['notify_reservation']),
        'notify_lost_found'   => isset($_POST['notify_lost_found']),
        'notify_announcement' => isset($_POST['notify_announcement']),
    ]);
    log_activity((int)$u['id'], 'settings_update', 'Updated notification preferences');
    flash_set('Settings saved.');
    redirect('settings.php');
}

 $on = [
    'notify_reservation'  => user_pref((int)$u['id'], 'notify_reservation'),
    'notify_lost_found'   => user_pref((int)$u['id'], 'notify_lost_found'),
    'notify_announcement' => user_pref((int)$u['id'], 'notify_announcement'),
];

 $lastLogin = db_scalar("SELECT created_at FROM activity_logs WHERE user_id=? AND action='login' ORDER BY created_at DESC LIMIT 1", [(int)$u['id']]);
 $me = db()->prepare('SELECT * FROM users WHERE id=?'); $me->execute([(int)$u['id']]); $me = $me->fetch();

 $acts = db()->prepare('SELECT action, description, created_at FROM activity_logs WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
 $acts->execute([(int)$u['id']]); $acts = $acts->fetchAll();

if (!function_exists('act_icon')) {
    function act_icon(string $a): array {
        if ($a === 'login')                      return ['bi-box-arrow-in-right', 'icon-success'];
        if ($a === 'logout')                     return ['bi-box-arrow-left',     'icon-navy'];
        if (str_starts_with($a, 'reservation'))  return ['bi-calendar-check',     'icon-info'];
        if (str_starts_with($a, 'lf_'))          return ['bi-search',             'icon-warning'];
        if (str_starts_with($a, 'ann_'))         return ['bi-megaphone',          'icon-purple'];
        return ['bi-check2', 'icon-navy'];
    }
}
function act_label(string $a): string {
    $map = ['login' => 'Signed in', 'logout' => 'Signed out', 'settings_update' => 'Settings updated',
            'reservation_create' => 'Reservation submitted', 'reservation_cancel' => 'Reservation cancelled',
            'lf_add' => 'Item reported', 'lf_claim' => 'Claim submitted', 'profile_update' => 'Profile updated',
            'password_change' => 'Password changed'];
    return $map[$a] ?? ucwords(str_replace('_', ' ', $a));
}

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Settings</h1><p class="page-sub">Notifications and account preferences.</p></div>
</div>

<div class="row g-4">
  <!-- Notifications -->
  <div class="col-lg-7">
    <div class="card p-4">
      <h6 class="card-title mb-1">Notification Preferences</h6>
      <p class="muted-sm mb-2">Choose what you get notified about.</p>
      <form method="post">
        <input type="hidden" name="form" value="prefs">

        <div class="set-row">
          <span class="stat-icon icon-info"><i class="bi bi-calendar-check"></i></span>
          <div class="flex-grow-1">
            <div class="set-title">Reservation updates</div>
            <div class="set-desc">Approvals, rejections, completion.</div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="notify_reservation" name="notify_reservation" <?= $on['notify_reservation'] ? 'checked' : '' ?>>
          </div>
        </div>

        <div class="set-row">
          <span class="stat-icon icon-warning"><i class="bi bi-search"></i></span>
          <div class="flex-grow-1">
            <div class="set-title">Lost &amp; Found updates</div>
            <div class="set-desc">Claim results and item status.</div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="notify_lost_found" name="notify_lost_found" <?= $on['notify_lost_found'] ? 'checked' : '' ?>>
          </div>
        </div>

        <div class="set-row">
          <span class="stat-icon icon-purple"><i class="bi bi-megaphone"></i></span>
          <div class="flex-grow-1">
            <div class="set-title">New announcements</div>
            <div class="set-desc">Campus-wide posts and notices.</div>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="notify_announcement" name="notify_announcement" <?= $on['notify_announcement'] ? 'checked' : '' ?>>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-navy px-4">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Account + activity -->
  <div class="col-lg-5 d-flex flex-column gap-3">
    <div class="card p-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <span class="avatar lg"><?= e(initials($me['first_name'], $me['last_name'])) ?></span>
        <div>
          <div class="fw-bold"><?= e($me['first_name'] . ' ' . $me['last_name']) ?></div>
          <span class="badge badge-soft soft-primary"><?= ucfirst($me['role']) ?></span>
        </div>
      </div>
      <div class="info-row"><span class="k">Student No.</span><span class="v"><?= e($me['user_id']) ?></span></div>
      <div class="info-row"><span class="k">Email</span><span class="v"><?= e($me['email'] ?: 'Not set') ?></span></div>
      <div class="info-row"><span class="k">Member since</span><span class="v"><?= fmt_date($me['created_at']) ?></span></div>
      <div class="info-row"><span class="k">Last sign-in</span><span class="v"><?= $lastLogin ? time_ago($lastLogin) : '—' ?></span></div>
      <a class="btn btn-outline-navy w-100 mt-3" href="profile.php"><i class="bi bi-shield-lock me-1"></i>Change Password</a>
    </div>

    <div class="card p-4">
      <h6 class="card-title mb-2">Recent Activity</h6>
      <?php if (!$acts): ?>
        <div class="muted-sm">No activity yet.</div>
      <?php else: foreach ($acts as $a): [$ai, $at] = act_icon($a['action']); ?>
        <div class="act-row">
          <span class="stat-icon <?= $at ?>"><i class="bi <?= $ai ?>"></i></span>
          <div class="flex-grow-1">
            <div class="act-text"><?= e(act_label($a['action'])) ?></div>
            <div class="act-sub"><?= e(mb_substr((string)$a['description'], 0, 60)) ?> · <?= time_ago($a['created_at']) ?></div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>