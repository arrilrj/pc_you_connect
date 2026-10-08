<?php
require_once '../includes/auth.php';
 $u = require_login();
 $id = (int)($_GET['id'] ?? 0);
 $st = db()->prepare('SELECT * FROM facilities WHERE id=?'); $st->execute([$id]);
 $facility = $st->fetch();
if (!$facility) { flash_set('Facility not found.', 'danger'); redirect('facilities.php'); }
 $page_title = $facility['name'];

 $up = db()->prepare("SELECT r.res_date, r.start_time, r.end_time FROM facility_reservations r WHERE r.facility_id=? AND r.status='approved' AND CONCAT(r.res_date,' ',r.end_time) >= NOW() ORDER BY r.res_date, r.start_time LIMIT 10");
 $up->execute([$id]); $upcoming = $up->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title"><?= e($facility['name']) ?></h1><p class="page-sub">Facility details and availability.</p></div>
  <div class="d-flex gap-2">
    <a class="btn btn-ghost" href="facilities.php">Back</a>
    <?php if ($facility['status'] === 'available'): ?>
      <a class="btn btn-navy" href="reserve.php?facility_id=<?= $id ?>"><i class="bi bi-calendar-plus me-1"></i>Reserve</a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card p-3 p-md-4 h-100">
      <?php if ($facility['image']): ?>
        <img class="detail-hero mb-3" src="../assets/uploads/<?= e($facility['image']) ?>" alt="<?= e($facility['name']) ?>">
      <?php endif; ?>
      <div class="d-flex align-items-center gap-3 mb-3">
        <span class="stat-icon icon-navy" style="width:52px;height:52px;font-size:24px"><i class="bi <?= facility_icon($facility) ?>"></i></span>
        <div><?= badge($facility['status']) ?></div>
      </div>
      <p class="mb-3"><?= e($facility['description'] ?: 'No description provided.') ?></p>
      <div class="muted-sm"><i class="bi bi-geo-alt me-1"></i><strong class="text-body">Location:</strong> <?= e($facility['location']) ?></div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-clean-head"><h6 class="card-title">Upcoming Reservations</h6></div>
      <div class="list-body">
        <?php if (!$upcoming): ?>
          <p class="text-muted small py-3 mb-0">No upcoming reservations. All slots are open.</p>
        <?php else: foreach ($upcoming as $r): ?>
          <div class="row-sep">
            <div class="fw-semibold"><?= fmt_date($r['res_date']) ?></div>
            <div class="muted-sm"><?= fmt_time12($r['start_time']) ?> – <?= fmt_time12($r['end_time']) ?></div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>