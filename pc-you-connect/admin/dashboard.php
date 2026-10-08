<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Admin Dashboard';
 $hour = (int)date('G');
 $greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

 $statFacAvail = (int)db_scalar("SELECT COUNT(*) FROM facilities WHERE status='available'");
 $statFacTotal = (int)db_scalar('SELECT COUNT(*) FROM facilities');
 $pendRes      = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='pending'");
 $pendClm      = (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='pending'");
 $lostOpen     = (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='lost' AND status='searching'");
 $statUncl     = (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found' AND status='unclaimed'");
 $attention    = $pendRes + $pendClm + $lostOpen;

 $resToday = db()->query("SELECT r.*, f.name AS facility, CONCAT(u.first_name,' ',u.last_name) requester
    FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id JOIN users u ON u.id=r.user_id
    WHERE r.status='approved' AND r.res_date=CURDATE() ORDER BY r.start_time LIMIT 4")->fetchAll();
 $todayCount = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='approved' AND res_date=CURDATE()");
 $resUpcoming = db()->query("SELECT r.*, f.name AS facility, CONCAT(u.first_name,' ',u.last_name) requester
    FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id JOIN users u ON u.id=r.user_id
    WHERE r.status='approved' AND r.res_date>CURDATE() ORDER BY r.res_date, r.start_time LIMIT 4")->fetchAll();
 $upcomingCount = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='approved' AND res_date>CURDATE()");
 $facInUse = (int)db_scalar("SELECT COUNT(DISTINCT facility_id) FROM facility_reservations WHERE status='approved' AND res_date=CURDATE()");
 $util = $statFacTotal ? (int)round($facInUse / $statFacTotal * 100) : 0;

 $lostRecent = db()->query("SELECT * FROM lost_found_items WHERE report_type='lost' ORDER BY created_at DESC LIMIT 3")->fetchAll();
 $unclItems = db()->query("SELECT * FROM lost_found_items WHERE report_type='found' AND status='unclaimed' ORDER BY created_at DESC LIMIT 3")->fetchAll();
 $claimsRecent = db()->query("SELECT c.*, i.item_name, CONCAT(u.first_name,' ',u.last_name) claimant
    FROM claim_requests c JOIN lost_found_items i ON i.id=c.item_id JOIN users u ON u.id=c.user_id
    WHERE c.status='pending' ORDER BY c.created_at ASC LIMIT 3")->fetchAll();
 $anns = db()->query("SELECT a.*, COALESCE(NULLIF(a.author_label,''), CONCAT(u.first_name,' ',u.last_name), 'Admin Office') AS author
    FROM announcements a LEFT JOIN users u ON u.id=a.author_id WHERE a.status='published'
    ORDER BY a.created_at DESC LIMIT 4")->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <h3 class="fw-bold mb-1" style="color:var(--navy-dark)"><?= $greet ?>, Admin!</h3>
  <p class="page-sub mb-4">Here's what's happening on campus today.</p>

  <div class="attention-panel <?= $attention ? 'urgent' : '' ?>">
    <?php if ($attention): ?>
      <div class="att-head">
        <span class="att-title"><?= $attention ?> item<?= $attention > 1 ? 's' : '' ?> need<?= $attention > 1 ? '' : 's' ?> your attention</span>
        <span class="badge badge-soft soft-warning"><?= $attention ?> PENDING</span>
      </div>
      <div class="att-sub">Resolve these administrative tasks to keep campus services running smoothly.</div>
      <div class="att-items">
        <?php if ($pendRes): ?>
        <div class="att-item">
          <span class="att-count"><?= $pendRes ?></span>
          <div class="att-text">Reservation request<?= $pendRes > 1 ? 's' : '' ?> awaiting approval
            <small>Review schedules and approve or reject with remarks.</small></div>
          <a class="btn btn-navy btn-sm" href="reservations.php?status=pending"><i class="bi bi-arrow-right"></i>Review</a>
        </div>
        <?php endif; ?>
        <?php if ($pendClm): ?>
        <div class="att-item">
          <span class="att-count"><?= $pendClm ?></span>
          <div class="att-text">Claim request<?= $pendClm > 1 ? 's' : '' ?> awaiting verification
            <small>Verify proof of ownership before releasing items.</small></div>
          <a class="btn btn-navy btn-sm" href="claims.php?status=pending"><i class="bi bi-arrow-right"></i>Review</a>
        </div>
        <?php endif; ?>
        <?php if ($lostOpen): ?>
        <div class="att-item">
          <span class="att-count"><?= $lostOpen ?></span>
          <div class="att-text">Lost report<?= $lostOpen > 1 ? 's' : '' ?> with no match yet
            <small>Cross-check found items against these reports.</small></div>
          <a class="btn btn-outline-navy btn-sm" href="lost-found.php?tab=lost"><i class="bi bi-search"></i>Check</a>
        </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="att-clear">
        <span class="ok-icon"><i class="bi bi-check-lg"></i></span>
        <div class="flex-grow-1">
          <div class="att-title">Everything is up to date</div>
          <div class="att-sub">No pending approvals, claims, or unmatched lost reports.</div>
        </div>
        <a class="btn btn-ghost btn-sm" href="facilities.php"><i class="bi bi-plus-lg"></i>Add Facility</a>
        <a class="btn btn-ghost btn-sm" href="announcements.php"><i class="bi bi-megaphone"></i>Create Announcement</a>
        <a class="btn btn-outline-navy btn-sm" href="reports.php"><i class="bi bi-file-earmark-bar-graph"></i>View Reports</a>
      </div>
    <?php endif; ?>
  </div>

  <div class="kpi-row">
    <div class="kpi-card">
      <span class="stat-icon icon-navy"><i class="bi bi-building"></i></span>
      <div><div class="kpi-num"><?= $statFacAvail ?></div><div class="kpi-label">Active Facilities</div></div>
    </div>
    <div class="kpi-card">
      <span class="stat-icon icon-warning"><i class="bi bi-calendar-check"></i></span>
      <div><div class="kpi-num"><?= $pendRes ?></div><div class="kpi-label">Pending Reservations</div></div>
    </div>
    <div class="kpi-card">
      <span class="stat-icon icon-info"><i class="bi bi-search"></i></span>
      <div><div class="kpi-num"><?= $statUncl ?></div><div class="kpi-label">Unclaimed Items</div></div>
    </div>
    <div class="kpi-card">
      <span class="stat-icon icon-purple"><i class="bi bi-hand-index-thumb"></i></span>
      <div><div class="kpi-num"><?= $pendClm ?></div><div class="kpi-label">Pending Claims</div></div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="sec-head">
          <h6 class="sec-title"><i class="bi bi-calendar-week"></i>Facility &amp; Reservation Activity</h6>
          <a class="btn btn-ghost btn-sm" href="reservations.php">View All</a>
        </div>
        <div class="mini-stats">
          <span class="mini-stat"><b><?= $todayCount ?></b> today</span>
          <span class="mini-stat"><b><?= $upcomingCount ?></b> upcoming</span>
          <span class="mini-stat"><b><?= $util ?>%</b> facilities in use today</span>
        </div>
        <div class="ops-label">Today</div>
        <div class="list-body pt-0">
          <?php if (!$resToday): ?>
            <div class="empty-sm"><i class="bi bi-calendar-x"></i>No facility activities scheduled today.</div>
          <?php else: foreach ($resToday as $r): ?>
            <div class="d-flex align-items-center gap-3 row-sep">
              <span class="item-thumb"><i class="bi bi-door-open"></i></span>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($r['facility']) ?></div>
                <div class="muted-sm"><?= e($r['requester']) ?> · <?= fmt_time12($r['start_time']) ?> – <?= fmt_time12($r['end_time']) ?></div>
              </div>
              <?= badge('approved') ?>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <div class="ops-label">Upcoming</div>
        <div class="list-body pt-0">
          <?php if (!$resUpcoming): ?>
            <div class="empty-sm"><i class="bi bi-calendar-plus"></i>No upcoming reservations yet.</div>
          <?php else: foreach ($resUpcoming as $r): ?>
            <div class="d-flex align-items-center gap-3 row-sep">
              <span class="item-thumb"><i class="bi bi-clock-history"></i></span>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($r['facility']) ?></div>
                <div class="muted-sm"><?= e($r['requester']) ?> · <?= fmt_date($r['res_date']) ?> · <?= fmt_time12($r['start_time']) ?></div>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="sec-head">
          <h6 class="sec-title"><i class="bi bi-search"></i>Lost &amp; Found Activity</h6>
          <a class="btn btn-ghost btn-sm" href="claims.php">Review Claims</a>
        </div>
        <div class="mini-stats">
          <span class="mini-stat"><b><?= $statUncl ?></b> unclaimed</span>
          <span class="mini-stat"><b><?= $lostOpen ?></b> lost reports open</span>
          <span class="mini-stat"><b><?= $pendClm ?></b> claims to verify</span>
        </div>
        <div class="ops-label">Recent Lost Reports</div>
        <div class="list-body pt-0">
          <?php if (!$lostRecent): ?>
            <div class="empty-sm"><i class="bi bi-emoji-smile"></i>No open lost reports — good news.</div>
          <?php else: foreach ($lostRecent as $i): ?>
            <div class="d-flex align-items-center gap-3 row-sep">
              <span class="item-thumb <?= lf_tint($i['category']) ?>"><i class="bi <?= lf_icon($i['category'], $i['item_name']) ?>"></i></span>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($i['item_name']) ?></div>
                <div class="muted-sm"><?= e($i['location_found']) ?> · <?= time_ago($i['created_at']) ?></div>
              </div>
              <?= badge($i['status']) ?>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <div class="ops-label">Unclaimed Found Items</div>
        <div class="list-body pt-0">
          <?php if (!$unclItems): ?>
            <div class="empty-sm"><i class="bi bi-inbox"></i>Nothing waiting to be claimed.</div>
          <?php else: foreach ($unclItems as $i): ?>
            <div class="d-flex align-items-center gap-3 row-sep">
              <span class="item-thumb <?= lf_tint($i['category']) ?>"><i class="bi <?= lf_icon($i['category'], $i['item_name']) ?>"></i></span>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($i['item_name']) ?></div>
                <div class="muted-sm"><?= e($i['location_found']) ?> · <?= time_ago($i['created_at']) ?></div>
              </div>
              <?= badge($i['status']) ?>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <div class="ops-label">Claims Awaiting Verification</div>
        <div class="list-body pt-0">
          <?php if (!$claimsRecent): ?>
            <div class="empty-sm"><i class="bi bi-check2-circle"></i>No claims waiting for review.</div>
          <?php else: foreach ($claimsRecent as $c): ?>
            <div class="d-flex align-items-center gap-3 row-sep">
              <span class="item-thumb"><i class="bi bi-hand-index-thumb"></i></span>
              <div class="flex-grow-1">
                <div class="fw-semibold"><?= e($c['item_name']) ?></div>
                <div class="muted-sm"><?= e($c['claimant']) ?> · <?= time_ago($c['created_at']) ?></div>
              </div>
              <a class="btn btn-outline-navy btn-sm" href="claims.php?status=pending">Verify</a>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="sec-head">
      <h6 class="sec-title"><i class="bi bi-megaphone"></i>Recent Announcements</h6>
      <div class="d-flex gap-2">
        <a class="btn btn-ghost btn-sm" href="announcements.php">Manage</a>
        <a class="btn btn-navy btn-sm" href="announcements.php"><i class="bi bi-plus-lg"></i>Create</a>
      </div>
    </div>
    <div class="list-body">
      <?php if (!$anns): ?>
        <div class="empty-sm"><i class="bi bi-megaphone"></i>No announcements published yet.</div>
      <?php else: foreach ($anns as $a): ?>
        <div class="d-flex gap-3 row-sep align-items-start">
          <span class="stat-icon <?= ann_tint($a['category']) ?>" style="width:36px;height:36px;font-size:15px"><i class="bi <?= ann_icon($a['category']) ?>"></i></span>
          <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
              <?= badge($a['category']) ?>
              <span class="muted-sm"><?= time_ago($a['created_at']) ?> · by <?= e($a['author']) ?></span>
            </div>
            <div class="fw-semibold"><?= e($a['title']) ?></div>
            <div class="muted-sm" style="display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden"><?= e(mb_substr(strip_tags($a['content']), 0, 120)) ?></div>
          </div>
          <a class="btn btn-ghost btn-sm d-none d-md-inline-flex" href="announcements.php">View</a>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>