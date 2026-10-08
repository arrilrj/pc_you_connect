<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Announcements';
 $q = get_str('q');
 $cat = in_array(get_str('cat'), ['facilities', 'lost_found'], true) ? get_str('cat') : '';

 $cnt = [
    'total'      => (int)db_scalar('SELECT COUNT(*) FROM announcements'),
    'facilities' => (int)db_scalar("SELECT COUNT(*) FROM announcements WHERE category='facilities'"),
    'lost_found' => (int)db_scalar("SELECT COUNT(*) FROM announcements WHERE category='lost_found'"),
    'high'       => (int)db_scalar("SELECT COUNT(*) FROM announcements WHERE priority='high' AND status='published'"),
];

 $sql = "SELECT a.*, COALESCE(NULLIF(a.author_label,''), CONCAT(u.first_name,' ',u.last_name), 'Admin Office') AS author
        FROM announcements a LEFT JOIN users u ON u.id=a.author_id WHERE 1=1";
 $p = [];
if ($q !== '') { $sql .= ' AND (a.title LIKE ? OR a.content LIKE ?)'; $p = ["%$q%", "%$q%"]; }
if ($cat !== '') { $sql .= ' AND a.category=?'; $p[] = $cat; }
 $sql .= ' ORDER BY a.created_at DESC LIMIT 200';
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
      <p class="page-sub">Post and manage announcements about facilities and lost &amp; found.</p>
    </div>
    <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addAnnModal"><i class="bi bi-plus-lg"></i>Add Announcement</button>
  </div>

  <div class="sum-row">
    <div class="sum-card"><span class="stat-icon icon-info"><i class="bi bi-megaphone"></i></span>
      <div><div class="sum-num"><?= $cnt['total'] ?></div><div class="sum-label">Total</div></div></div>
    <div class="sum-card"><span class="stat-icon icon-navy"><i class="bi bi-building"></i></span>
      <div><div class="sum-num"><?= $cnt['facilities'] ?></div><div class="sum-label">Facilities</div></div></div>
    <div class="sum-card <?= $cnt['lost_found'] ? 'hot' : '' ?>"><span class="stat-icon icon-warning"><i class="bi bi-search"></i></span>
      <div><div class="sum-num"><?= $cnt['lost_found'] ?></div><div class="sum-label">Lost &amp; Found</div></div></div>
    <div class="sum-card <?= $cnt['high'] ? 'hot' : '' ?>"><span class="stat-icon icon-purple"><i class="bi bi-exclamation-triangle"></i></span>
      <div><div class="sum-num"><?= $cnt['high'] ?></div><div class="sum-label">High Priority Live</div></div></div>
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
              <?= $a['status'] === 'archived' ? badge('archived') : '' ?>
            </div>
            <div class="ann-title mb-1"><?= e($a['title']) ?></div>
            <div class="ann-content" id="annBody<?= $a['id'] ?>"><?= nl2br(e($a['content'])) ?></div>
            <button class="read-more mt-1" data-readmore="#annBody<?= $a['id'] ?>" data-id="<?= $a['id'] ?>">Read more</button>
          </div>
          <div class="ann-side">
            <div class="d-flex gap-1">
              <button class="act-quiet" data-edit-ann data-id="<?= $a['id'] ?>" data-title="<?= e($a['title']) ?>"
                data-cat="<?= e($a['category']) ?>" data-content="<?= e($a['content']) ?>" data-status="<?= e($a['status']) ?>"
                data-event="<?= e((string)$a['event_date']) ?>" data-priority="<?= e($a['priority']) ?>"
                data-bs-toggle="modal" data-bs-target="#editAnnModal" title="Edit"><i class="bi bi-pencil"></i></button>
              <?php $archiving = $a['status'] === 'published'; ?>
              <form method="post" action="../actions/announcements.php" class="d-inline"
                data-confirm="<?= $archiving ? 'Archived announcements will no longer appear in the active announcement list.' : 'This announcement will appear again in the active announcement list.' ?>"
                data-confirm-title="<?= $archiving ? 'Archive announcement?' : 'Publish announcement?' ?>"
                data-confirm-ok="<?= $archiving ? 'Archive' : 'Publish' ?>"
                data-confirm-variant="neutral">
                <input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button class="act-quiet" title="<?= $archiving ? 'Archive' : 'Publish' ?>">
                  <i class="bi <?= $archiving ? 'bi-archive' : 'bi-upload' ?>"></i></button>
              </form>
              <form method="post" action="../actions/announcements.php" class="d-inline"
                data-confirm="Delete '<?= e($a['title']) ?>'? This announcement will be permanently removed from the system."
                data-confirm-ok="Delete Announcement">
                <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button class="act-quiet danger" title="Delete"><i class="bi bi-trash"></i></button>
              </form>
            </div>
            <span class="muted-sm d-flex align-items-center gap-1" style="font-size:12px">
              <i class="bi bi-eye"></i><span data-views="<?= $a['id'] ?>"><?= (int)$a['views'] ?></span>
            </span>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<div class="modal fade" id="addAnnModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Add Announcement</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/announcements.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <ul class="nav nav-tabs mb-3">
            <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#paneCompose">Compose</button></li>
            <li class="nav-item"><button class="nav-link" type="button" id="previewTabBtn" data-bs-toggle="tab" data-bs-target="#panePreview">Preview</button></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="paneCompose">
              <div class="alert alert-info py-2 small mb-3"><i class="bi bi-info-circle me-1"></i>Scope: announcements are limited to <strong>Facilities</strong> and <strong>Lost &amp; Found</strong> topics only.</div>
              <div class="mb-3"><label class="form-label">Announcement Title *</label><input class="form-control" name="title" id="anTitle" required></div>
              <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label">Category</label>
                  <select class="form-select" name="category" id="anCat">
                    <option value="facilities" selected>Facilities</option>
                    <option value="lost_found">Lost &amp; Found</option>
                  </select></div>
                <div class="col-md-4"><label class="form-label">Date / Schedule</label>
                  <input type="date" class="form-control" name="event_date" id="anDate"></div>
                <div class="col-md-4"><label class="form-label">Priority</label>
                  <select class="form-select" name="priority">
                    <option value="normal" selected>Normal</option><option value="high">High</option>
                  </select></div>
              </div>
              <div class="mb-3"><label class="form-label">Description *</label>
                <textarea class="form-control" name="content" id="anContent" rows="6" placeholder="e.g., facility closures, maintenance schedules, reservation guidelines, claiming deadlines, found item notices"></textarea></div>
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Publish Status</label>
                  <select class="form-select" name="status">
                    <option value="published">Publish now</option><option value="archived">Save as archived</option>
                  </select></div>
                <div class="col-md-6"><label class="form-label">Image (optional, max 2MB)</label>
                  <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,.gif"></div>
              </div>
            </div>
            <div class="tab-pane fade" id="panePreview">
              <div class="pv-box">
                <div class="pv-meta" id="pvMeta"></div>
                <div class="pv-title" id="pvTitle"></div>
                <div class="pv-body" id="pvBody"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Publish</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editAnnModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Edit Announcement</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/announcements.php" enctype="multipart/form-data" id="editAnnForm">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Announcement Title *</label><input class="form-control" name="title" required></div>
          <div class="row g-3 mb-3">
            <div class="col-md-4"><label class="form-label">Category</label>
              <select class="form-select" name="category">
                <option value="facilities">Facilities</option>
                <option value="lost_found">Lost &amp; Found</option>
              </select></div>
            <div class="col-md-4"><label class="form-label">Date / Schedule</label>
              <input type="date" class="form-control" name="event_date"></div>
            <div class="col-md-4"><label class="form-label">Priority</label>
              <select class="form-select" name="priority">
                <option value="normal">Normal</option><option value="high">High</option>
              </select></div>
          </div>
          <div class="mb-3"><label class="form-label">Description *</label><textarea class="form-control" name="content" rows="6" required></textarea></div>
          <div><label class="form-label">Publish Status</label>
            <select class="form-select" name="status">
              <option value="published">Published</option><option value="archived">Archived</option>
            </select></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const BC = {facilities:'soft-info',lost_found:'soft-warning'};
  const BL = {facilities:'FACILITIES',lost_found:'LOST & FOUND'};
  const syncPreview = () => {
    const t = document.getElementById('anTitle').value || 'Untitled announcement';
    const c = document.getElementById('anCat').value;
    const d = document.getElementById('anDate').value;
    const body = document.getElementById('anContent').value || 'Nothing to preview yet.';
    document.getElementById('pvMeta').innerHTML =
      '<span class="badge badge-soft ' + (BC[c] || 'soft-secondary') + '">' + (BL[c] || c) + '</span> just now · by Admin Office' +
      (d ? ' · <i class="bi bi-calendar-event"></i> ' + d : '');
    document.getElementById('pvTitle').textContent = t;
    document.getElementById('pvBody').innerHTML = body.split(/\r?\n/).map(esc => esc).map(x => x.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')).join('<br>');
  };
  ['anTitle','anCat','anDate','anContent'].forEach(id => document.getElementById(id).addEventListener('input', syncPreview));
  document.getElementById('previewTabBtn').addEventListener('click', syncPreview);

  document.querySelectorAll('[data-edit-ann]').forEach(b => b.addEventListener('click', () => {
    const f = document.getElementById('editAnnForm');
    f.id.value = b.dataset.id;
    f.title.value = b.dataset.title;
    f.category.value = b.dataset.cat;
    f.content.value = b.dataset.content;
    f.status.value = b.dataset.status;
    f.event_date.value = b.dataset.event || '';
    f.priority.value = b.dataset.priority || 'normal';
  }));
});
</script>

<?php require_once '../includes/footer.php'; ?>