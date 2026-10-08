<?php
require_once '../includes/auth.php';
 $u = require_login();
 $hour = (int)date('G');
 $greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
 $page_title = 'Dashboard';

// ---- Accurate live statistics ----
 $statFac        = (int)db_scalar("SELECT COUNT(*) FROM facilities WHERE status='available'");
 $statRes        = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE user_id=? AND status IN ('pending','approved') AND CONCAT(res_date,' ',end_time) >= NOW()", [$u['id']]);
 $statUncl       = (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found' AND status='unclaimed'");
 $statMyPending  = (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE user_id=? AND status='pending'", [$u['id']]);
 $statMyApproved = (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE user_id=? AND status='approved'", [$u['id']]);

 $anns = db()->query("SELECT a.*, COALESCE(NULLIF(a.author_label,''), CONCAT(u.first_name,' ',u.last_name), 'Admin Office') AS author
    FROM announcements a LEFT JOIN users u ON u.id=a.author_id WHERE a.status='published' ORDER BY a.created_at DESC LIMIT 3")->fetchAll();
 $bk = db()->prepare("SELECT r.*, f.name AS facility FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id
    WHERE r.user_id=? AND r.status IN ('pending','approved') AND CONCAT(r.res_date,' ',r.end_time) >= NOW() ORDER BY r.res_date, r.start_time LIMIT 4");
 $bk->execute([$u['id']]); $bookings = $bk->fetchAll();
 $lf = db()->prepare("SELECT * FROM lost_found_items WHERE report_type='found' AND status='unclaimed' ORDER BY created_at DESC LIMIT 4");
 $lf->execute(); $lfItems = $lf->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>

<h3 class="fw-bold mb-1" style="color:var(--navy-dark)"><?= $greet ?>, <?= e($u['first_name']) ?>!</h3>
<p class="page-sub mb-4">Here's what's happening on campus today.</p>

<!-- Statistic cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
      [$statFac, 'Active Facilities',    'bi-building',          'icon-navy',    'available to book right now'],
      [$statRes, 'My Active Bookings',   'bi-calendar-check',    'icon-success', 'pending & approved'],
      [$statUncl, 'Items Awaiting Claim','bi-search',            'icon-warning', 'found on campus — is yours here?'],
      [$statMyPending + $statMyApproved, 'My Claims',            'bi-hand-index-thumb', 'icon-purple',
          $statMyPending . ' pending · ' . $statMyApproved . ' approved'],
  ];
  foreach ($cards as [$num, $label, $icon, $tint, $sub]): ?>
  <div class="col-6 col-lg-3">
    <div class="card stat-card">
      <div class="stat-top"><span class="stat-icon <?= $tint ?>"><i class="bi <?= $icon ?>"></i></span></div>
      <div class="stat-num"><?= $num ?></div>
      <div class="stat-label"><?= e($label) ?></div>
      <div class="muted-sm" style="font-size:11.5px"><?= e($sub) ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <!-- Recent Announcements -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-clean-head">
        <h6 class="card-title">Recent Announcements</h6>
        <a class="link-more" href="announcements.php">View All →</a>
      </div>
      <div class="list-body">
        <?php if (!$anns): ?>
          <p class="text-muted small py-3 mb-0">No announcements yet.</p>
        <?php else: foreach ($anns as $a): ?>
          <div class="d-flex gap-3 row-sep">
            <span class="stat-icon <?= ann_tint($a['category']) ?>"><i class="bi <?= ann_icon($a['category']) ?>"></i></span>
            <div class="flex-grow-1 overflow-hidden">
              <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <?= badge($a['category']) ?>
                <span class="muted-sm"><?= time_ago($a['created_at']) ?> • by <?= e($a['author']) ?></span>
              </div>
              <div class="fw-semibold"><?= e($a['title']) ?></div>
              <div class="muted-sm"><?= e(mb_substr(strip_tags($a['content']), 0, 110)) ?>…</div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4 d-flex flex-column gap-3">
    <!-- My Active Bookings -->
    <div class="card">
      <div class="card-clean-head">
        <h6 class="card-title">My Active Bookings</h6>
        <a class="link-more" href="reservations.php">View All →</a>
      </div>
      <div class="list-body">
        <?php if (!$bookings): ?>
          <p class="text-muted small py-3 mb-0">No upcoming bookings. <a class="link-more" href="facilities.php">Reserve a facility →</a></p>
        <?php else: foreach ($bookings as $b): ?>
          <div class="d-flex align-items-center gap-3 row-sep">
            <span class="item-thumb"><i class="bi bi-door-open"></i></span>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= e($b['facility']) ?></div>
              <div class="muted-sm"><?= fmt_date($b['res_date']) ?> • <?= fmt_time12($b['start_time']) ?> – <?= fmt_time12($b['end_time']) ?></div>
            </div>
            <?= badge($b['status'] === 'approved' ? 'upcoming' : 'pending') ?>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Items awaiting claim -->
    <div class="card">
      <div class="card-clean-head">
        <h6 class="card-title">Found Items — Awaiting Claim</h6>
        <a class="link-more" href="lost-found.php?view=found">View All →</a>
      </div>
      <div class="list-body">
        <?php if (!$lfItems): ?>
          <p class="text-muted small py-3 mb-0">No unclaimed items right now.</p>
        <?php else: foreach ($lfItems as $i): ?>
          <div class="d-flex align-items-start gap-3 row-sep">
            <span class="item-thumb <?= lf_tint($i['category']) ?>"><i class="bi <?= lf_icon($i['category'], $i['item_name']) ?>"></i></span>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= e($i['item_name']) ?></div>
              <div class="muted-sm"><?= e($i['location_found']) ?></div>
              <div class="muted-sm">• <?= time_ago($i['created_at']) ?></div>
            </div>
            <?= badge($i['status']) ?>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>