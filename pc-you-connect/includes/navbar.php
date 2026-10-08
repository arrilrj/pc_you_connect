<?php
 $navUser  = current_user();
 $navAdmin = ($navUser['role'] ?? '') === 'admin';
 $notifLink = 'notifications.php';
 $unread = unread_count((int)$navUser['id']);
 $st = db()->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5');
 $st->execute([(int)$navUser['id']]);
 $notifs = $st->fetchAll();
 $nicon = ['reservation'=>'bi-calendar-check','lost_found'=>'bi-search','announcement'=>'bi-megaphone','system'=>'bi-bell'];
 $parts = preg_split('/\s+/', $navUser['name']);
 $ini = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
?>
<header class="app-navbar">
  <button class="btn btn-icon d-lg-none" id="sidebarToggle" type="button" aria-label="Menu"><i class="bi bi-list fs-5"></i></button>

  <form class="nav-search" role="search">
    <i class="bi bi-search"></i>
    <input type="search" name="q" class="form-control" placeholder="Search anything..." value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search">
  </form>

  <div class="nav-right ms-auto d-flex align-items-center gap-1">
    <div class="dropdown">
      <button class="btn btn-icon" data-bs-toggle="dropdown" type="button" aria-label="Quick links" title="Quick links"><i class="bi bi-gear fs-5"></i></button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><h6 class="dropdown-header">Quick Links</h6></li>
        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-sliders me-2"></i>Settings</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="<?= $base ?>/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign Out</a></li>
      </ul>
    </div>

    <div class="dropdown">
      <button class="btn btn-icon position-relative" data-bs-toggle="dropdown" type="button" aria-label="Notifications" title="Notifications">
        <i class="bi bi-bell fs-5"></i>
        <?php if ($unread > 0): ?><span class="notif-dot"><?= $unread > 9 ? '9+' : $unread ?></span><?php endif; ?>
      </button>
      <div class="dropdown-menu dropdown-menu-end notif-menu shadow-sm">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
          <strong class="small">Notifications</strong>
          <form method="post" action="<?= $base ?>/actions/notifications.php" class="m-0">
            <input type="hidden" name="action" value="read_all">
            <button class="btn btn-link btn-sm p-0 text-decoration-none small">Mark all as read</button>
          </form>
        </div>
        <?php if (!$notifs): ?>
          <div class="empty-sm" style="padding:22px"><i class="bi bi-bell"></i>No notifications yet.</div>
        <?php else: foreach ($notifs as $n): ?>
          <a class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>" href="<?= $notifLink ?>">
            <span class="item-thumb sm"><i class="bi <?= $nicon[$n['type']] ?? 'bi-bell' ?>"></i></span>
            <span class="flex-grow-1 overflow-hidden">
              <span class="d-block fw-semibold text-truncate"><?= e($n['title']) ?></span>
              <span class="d-block text-muted small text-truncate"><?= e($n['message']) ?></span>
              <span class="d-block text-muted" style="font-size:11px"><?= time_ago($n['created_at']) ?></span>
            </span>
          </a>
        <?php endforeach; endif; ?>
        <a href="<?= $notifLink ?>" class="d-block text-center small py-2 link-more fw-semibold">View all notifications</a>
      </div>
    </div>

    <div class="dropdown">
      <button class="nav-user ms-1 btn p-0 border-0 bg-transparent d-flex align-items-center gap-2" data-bs-toggle="dropdown" type="button" aria-label="Account menu">
        <span class="avatar"><?= e($ini) ?></span>
        <div class="d-none d-md-block lh-sm text-start">
          <div class="nav-user-name"><?= e($navUser['name']) ?></div>
          <div class="nav-user-role"><?= $navAdmin ? 'Administrator' : ucfirst($navUser['role']) ?> · <?= e($navUser['user_id']) ?></div>
        </div>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li class="px-3 py-2 border-bottom">
          <div class="fw-bold" style="font-size:13.5px"><?= e($navUser['name']) ?></div>
          <div class="muted-sm"><?= $navAdmin ? 'Administrator' : ucfirst($navUser['role']) ?> · <?= e($navUser['user_id']) ?></div>
        </li>
        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
        <li><a class="dropdown-item" href="settings.php#activity"><i class="bi bi-clock-history me-2"></i>Activity Log</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="<?= $base ?>/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign Out</a></li>
      </ul>
    </div>
  </div>
</header>