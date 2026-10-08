<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Facilities';
 $q = get_str('q');
 $status = in_array(get_str('status'), ['available', 'maintenance', 'unavailable'], true) ? get_str('status') : '';

 $sql = 'SELECT * FROM facilities WHERE 1=1'; $p = [];
if ($q !== '')      { $sql .= ' AND (name LIKE ? OR location LIKE ? OR description LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; $p[] = "%$q%"; }
if ($status !== '') { $sql .= ' AND status=?'; $p[] = $status; }
 $sql .= ' ORDER BY name';
 $st = db()->prepare($sql); $st->execute($p); $facilities = $st->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Facilities</h1><p class="page-sub">Browse and reserve campus facilities.</p></div>
</div>

<div class="card mb-4">
  <div class="card-clean-head">
    <div class="filter-tabs">
      <a class="filter-tab <?= $status === '' ? 'active' : '' ?>" href="facilities.php?<?= e(http_build_query(array_filter(['q' => $q]))) ?>">All</a>
      <?php foreach (['available', 'maintenance', 'unavailable'] as $s): ?>
        <a class="filter-tab <?= $status === $s ? 'active' : '' ?>" href="facilities.php?<?= e(http_build_query(array_filter(['q' => $q, 'status' => $s]))) ?>"><?= ucfirst($s) ?></a>
      <?php endforeach; ?>
    </div>
    <form class="nav-search" style="max-width:240px"><i class="bi bi-search"></i>
      <input type="search" name="q" class="form-control" placeholder="Search facilities..." value="<?= e($q) ?>">
      <input type="hidden" name="status" value="<?= e($status) ?>">
    </form>
  </div>
</div>

<?php if (!$facilities): ?>
  <div class="card p-4 text-center text-muted">No facilities found.</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($facilities as $f): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 overflow-hidden">
      <div class="facility-cover">
        <?php if ($f['image']): ?>
          <img class="facility-photo" src="../assets/uploads/<?= e($f['image']) ?>" alt="<?= e($f['name']) ?>">
        <?php else: ?>
          <i class="bi <?= facility_icon($f) ?>" style="font-size:36px;color:var(--navy)"></i>
        <?php endif; ?>
        <span class="position-absolute top-0 end-0 m-2"><?= badge($f['status']) ?></span>
      </div>
      <div class="p-3 d-flex flex-column flex-grow-1">
        <div class="fw-bold mb-1"><?= e($f['name']) ?></div>
        <div class="muted-sm mb-1"><i class="bi bi-geo-alt me-1"></i><?= e($f['location']) ?></div>
        <div class="muted-sm mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e((string)$f['description']) ?></div>
        <div class="mt-auto d-flex gap-2">
          <a class="btn btn-outline-navy btn-sm flex-fill" href="facility-details.php?id=<?= (int)$f['id'] ?>">View Details</a>
          <?php if ($f['status'] === 'available'): ?>
            <a class="btn btn-navy btn-sm flex-fill" href="reserve.php?facility_id=<?= (int)$f['id'] ?>">Reserve</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>