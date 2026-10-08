<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Announcements';
 $q = get_str('q');
 $cat = in_array(get_str('cat'), ['facilities', 'lost_found'], true) ? get_str('cat') : '';

 $sql = "SELECT a.*, COALESCE(NULLIF(a.author_label,''), CONCAT(u.first_name,' ',u.last_name), 'Admin Office') AS author
        FROM announcements a LEFT JOIN users u ON u.id=a.author_id WHERE a.status='published'";
 $p = [];
if ($q !== '') { $sql .= ' AND (a.title LIKE ? OR a.content LIKE ?)'; $p = ["%$q%", "%$q%"]; }
if ($cat !== '') { $sql .= ' AND a.category=?'; $p[] = $cat; }
 $sql .= ' ORDER BY a.created_at DESC LIMIT 100';
 $st = db()->prepare($sql);
 $st->execute($p);
 $anns = $st->fetchAll();

 $mkTab = fn($c, $l) => '<a class="text-tab' . ($cat === $c ? ' active' : '') . '" href="announcements.php?' . e(http_build_query(array_filter(['q' => $q ?: null, 'cat' => $c ?: null]))) . '">' . $l . '</a>';

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <div class="page-head">
    <div>
      <h1 class="page-title">Announcements</h1>
      <p class="page-sub">Facility updates and lost &amp; found notices from the admin office.</p>
    </div>
  </div>

  <div class="manage-card">
    <div class="manage-head">
      <h6 class="manage-title">All Announcements</h6>
      <form class="nav-search" style="max-width:240px;margin-bottom:8px"><i class="bi bi-search"></i>
        <input type="search" name="q" class="form-control" placeholder="Search announcements..." value="<?= e($q) ?>">
        <input type="hidden" name="cat" value="<?= e($cat) ?>">
      </form>
    </div>
    <div class="manage-tabs">
      <div class="text-tabs">
        <?= $mkTab('', 'All') . $mkTab('facilities', 'Facilities') . $mkTab('lost_found', 'Lost &amp; Found') ?>
      </div>
    </div>

    <?php if (!$anns): ?>
      <div class="empty-state">
        <i class="bi bi-megaphone"></i>
        <div class="es-title">No announcements found</div>
        <div class="es-sub">Try adjusting your search or category filter.</div>
        <a class="btn btn-outline-navy btn-sm" href="announcements.php"><i class="bi bi-arrow-counterclockwise"></i>Reset Filters</a>
      </div>
    <?php else: foreach ($anns as $a): $imp = $a['priority'] === 'high'; ?>
      <div class="ann-item <?= $imp ? 'important' : '' ?>">
        <div class="d-flex gap-3 align-items-start">
          <span class="stat-icon <?= ann_tint($a['category']) ?>"><i class="bi <?= ann_icon($a['category']) ?>"></i></span>
          <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
              <?= badge($a['category']) ?>
              <?php if ($imp): ?><span class="badge badge-soft soft-danger">HIGH PRIORITY</span><?php endif; ?>
              <span class="muted-sm"><?= time_ago($a['created_at']) ?> · by <?= e($a['author']) ?></span>
              <?php if ($a['event_date']): ?><span class="muted-sm"><i class="bi bi-calendar-event me-1"></i><?= fmt_date($a['event_date']) ?></span><?php endif; ?>
            </div>
            <div class="ann-title mb-1"><?= e($a['title']) ?></div>
            <div class="ann-content" id="annBody<?= $a['id'] ?>"><?= nl2br(e($a['content'])) ?></div>
            <button class="read-more mt-1" data-readmore="#annBody<?= $a['id'] ?>" data-id="<?= $a['id'] ?>">Read more</button>
          </div>
          <span class="muted-sm d-none d-md-flex align-items-center gap-1 flex-none" style="font-size:12px">
            <i class="bi bi-eye"></i><span data-views="<?= $a['id'] ?>"><?= (int)$a['views'] ?></span>
          </span>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>