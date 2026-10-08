<?php
 $base = $base ?? '..';
 $sbUser  = current_user();
 $sbAdmin = ($sbUser['role'] ?? '') === 'admin';
 $cur = basename($_SERVER['PHP_SELF']);
function snav(string $file, string $cur): string { return $file === $cur ? ' active' : ''; }
 $pendingClaims = $sbAdmin ? (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='pending'") : 0;
 $seal = file_exists(__DIR__ . '/../assets/images/seal.png');
?>
<aside class="app-sidebar" id="appSidebar">
  <div class="sidebar-brand">
    <?php if ($seal): ?><img src="<?= $base ?>/assets/images/seal.png" class="seal-img" alt="PCU Seal">
    <?php else: ?><span class="brand-logo">PCU</span><?php endif; ?>
    <div class="brand-text">
      <div class="brand-name">PC-YOU! Connect</div>
      <div class="brand-sub">Campus Portal</div>
    </div>
    <button id="sbCollapse" type="button" aria-label="Collapse sidebar" title="Collapse sidebar"><i class="bi bi-chevron-left"></i></button>
  </div>

  <div class="sidebar-label">Overview</div>
  <nav class="sidebar-nav">
    <a class="sidebar-link<?= snav('dashboard.php', $cur) ?>" href="<?= $base ?>/<?= $sbAdmin ? 'admin' : 'user' ?>/dashboard.php"><i class="bi bi-grid-1x2"></i> Dashboard</a>
  </nav>

  <div class="sidebar-label">Campus Operations</div>
  <nav class="sidebar-nav">
    <a class="sidebar-link<?= snav('facilities.php', $cur) ?>" href="facilities.php"><i class="bi bi-building"></i> Facilities</a>
    <?php if ($sbAdmin): ?>
      <a class="sidebar-link<?= snav('calendar.php', $cur) ?>" href="calendar.php"><i class="bi bi-calendar3"></i> Schedule</a>
      <a class="sidebar-link<?= snav('reservations.php', $cur) ?>" href="reservations.php"><i class="bi bi-calendar-check"></i> Reservations</a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-label">Lost &amp; Found</div>
  <nav class="sidebar-nav">
    <a class="sidebar-link<?= snav('lost-found.php', $cur) ?>" href="lost-found.php"><i class="bi bi-search"></i> Lost &amp; Found</a>
    <?php if ($sbAdmin): ?>
      <a class="sidebar-link<?= snav('claims.php', $cur) ?>" href="claims.php">
        <i class="bi bi-hand-index-thumb"></i> Claim Requests
        <?php if ($pendingClaims): ?><span class="ms-auto notif-dot" style="position:static"><?= $pendingClaims > 9 ? '9+' : $pendingClaims ?></span><?php endif; ?>
      </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-label">Communication</div>
  <nav class="sidebar-nav">
    <a class="sidebar-link<?= snav('announcements.php', $cur) ?>" href="announcements.php"><i class="bi bi-megaphone"></i> Announcements</a>
  </nav>

  <?php if ($sbAdmin): ?>
  <div class="sidebar-label">Management</div>
  <nav class="sidebar-nav">
    <a class="sidebar-link<?= snav('users.php', $cur) ?>" href="users.php"><i class="bi bi-people"></i> Users</a>
    <a class="sidebar-link<?= snav('reports.php', $cur) ?>" href="reports.php"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a>
  </nav>
  <?php endif; ?>

  <div class="sidebar-label">Account</div>
  <nav class="sidebar-nav">
    <a class="sidebar-link<?= snav('profile.php', $cur) ?>" href="profile.php"><i class="bi bi-person"></i> Profile</a>
    <a class="sidebar-link<?= snav('settings.php', $cur) ?>" href="settings.php"><i class="bi bi-gear"></i> Settings</a>
  </nav>

  <div class="sidebar-footer">
    <a class="sidebar-link" href="<?= $base ?>/logout.php"><i class="bi bi-box-arrow-left"></i> Sign Out</a>
  </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>