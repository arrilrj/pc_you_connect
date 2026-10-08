<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Notifications';
 $st = db()->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100');
 $st->execute([$u['id']]);
 $items = $st->fetchAll();
 $nicon = ['reservation'=>'bi-calendar-check','lost_found'=>'bi-search','announcement'=>'bi-megaphone','system'=>'bi-bell'];

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Notifications</h1><p class="page-sub">Reservation updates, lost &amp; found, and announcements.</p></div>
  <form method="post" action="../actions/notifications.php">
    <input type="hidden" name="action" value="read_all"><input type="hidden" name="back" value="notifications.php">
    <button class="btn btn-outline-navy"><i class="bi bi-check2-all me-1"></i>Mark all as read</button>
  </form>
</div>

<div class="card">
  <?php if (!$items): ?>
    <div class="p-4 text-center text-muted">You have no notifications.</div>
  <?php else: foreach ($items as $n): ?>
    <div class="d-flex gap-3 p-3 px-4 border-bottom <?= $n['is_read'] ? '' : 'bg-light' ?>" style="<?= $n['is_read'] ? '' : 'background:#F5F8FD' ?>">
      <span class="item-thumb"><i class="bi <?= $nicon[$n['type']] ?? 'bi-bell' ?>"></i></span>
      <div class="flex-grow-1">
        <div class="fw-semibold"><?= e($n['title']) ?> <?= $n['is_read'] ? '' : '<span class="badge badge-soft soft-primary">New</span>' ?></div>
        <div class="muted-sm"><?= e($n['message']) ?></div>
        <div class="muted-sm" style="font-size:11px"><?= time_ago($n['created_at']) ?></div>
      </div>
      <div class="d-flex flex-column align-items-end gap-2">
        <?php if (!$n['is_read']): ?>
        <form method="post" action="../actions/notifications.php">
          <input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?= $n['id'] ?>">
          <input type="hidden" name="back" value="notifications.php">
          <button class="btn-sm-icon" title="Mark as read"><i class="bi bi-check2"></i></button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>