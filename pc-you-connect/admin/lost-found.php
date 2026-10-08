<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Lost & Found';
 $q = get_str('q');
 $tab = get_str('tab');
 $status = in_array($tab, ['unclaimed', 'claimed', 'turned_in'], true) ? $tab : '';
 $typeFilter = $tab === 'lost' ? 'lost' : '';
 $cat = in_array(get_str('cat'), ['electronics', 'id_card', 'accessories', 'bags', 'keys', 'clothing', 'documents', 'books', 'other'], true) ? get_str('cat') : '';
 $perpage = 10;
 $page = max(1, (int)get_str('page', 1));

 $where = ' WHERE 1=1';
 $p = [];
if ($q !== '') { $where .= ' AND (i.item_name LIKE ? OR i.location_found LIKE ? OR i.description LIKE ?)'; $p = ["%$q%", "%$q%", "%$q%"]; }
if ($status !== '') { $where .= ' AND i.status=? AND i.report_type=?'; $p[] = $status; $p[] = 'found'; }
if ($typeFilter !== '') { $where .= ' AND i.report_type=?'; $p[] = $typeFilter; }
if ($cat !== '') { $where .= ' AND i.category=?'; $p[] = $cat; }

 $total = (int)db_scalar('SELECT COUNT(*) FROM lost_found_items i' . $where, $p);
 $pages = max(1, (int)ceil($total / $perpage));
if ($page > $pages) $page = $pages;
 $offset = ($page - 1) * $perpage;

 $cnt = [
    'total'     => (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found'"),
    'unclaimed' => (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found' AND status='unclaimed'"),
    'claimed'   => (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found' AND status='claimed'"),
    'turned_in' => (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found' AND status='turned_in'"),
    'lost'      => (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='lost'"),
];
 $pendingClaims = (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='pending'");

 $sql = 'SELECT i.* FROM lost_found_items i' . $where . " ORDER BY i.created_at DESC LIMIT $perpage OFFSET $offset";
 $st = db()->prepare($sql);
 $st->execute($p);
 $items = $st->fetchAll();

 $keep = array_filter(['q' => $q ?: null, 'tab' => $tab ?: null, 'cat' => $cat ?: null]);
 $mkTab = fn($t, $l) => '<a class="text-tab' . ($tab === $t ? ' active' : '') . '" href="lost-found.php?' . e(http_build_query(array_merge($keep, ['tab' => $t ?: null]))) . '">' . $l . '</a>';
 $pgUrl = fn($n) => 'lost-found.php?' . http_build_query(array_merge($keep, ['page' => $n > 1 ? $n : null]));

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <div class="page-head">
    <div>
      <h1 class="page-title">Lost &amp; Found</h1>
      <p class="page-sub">Manage and track lost and found items on campus.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <?php if ($pendingClaims): ?>
        <a class="btn btn-ghost position-relative" href="claims.php">
          <i class="bi bi-hand-index-thumb"></i>Claim Requests
          <span class="notif-dot" style="top:-4px;right:-4px"><?= $pendingClaims > 9 ? '9+' : $pendingClaims ?></span>
        </a>
      <?php endif; ?>
      <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="bi bi-plus-lg"></i>Add Item</button>
    </div>
  </div>

  <div class="sum-row">
    <div class="sum-card"><span class="stat-icon icon-info"><i class="bi bi-archive"></i></span>
      <div><div class="sum-num"><?= $cnt['total'] ?></div><div class="sum-label">Total Items</div></div></div>
    <div class="sum-card <?= $cnt['unclaimed'] ? 'hot' : '' ?>"><span class="stat-icon icon-warning"><i class="bi bi-hourglass-split"></i></span>
      <div><div class="sum-num"><?= $cnt['unclaimed'] ?></div><div class="sum-label">Unclaimed</div></div></div>
    <div class="sum-card"><span class="stat-icon icon-success"><i class="bi bi-check-circle"></i></span>
      <div><div class="sum-num"><?= $cnt['claimed'] ?></div><div class="sum-label">Claimed</div></div></div>
    <div class="sum-card"><span class="stat-icon icon-purple"><i class="bi bi-building"></i></span>
      <div><div class="sum-num"><?= $cnt['turned_in'] ?></div><div class="sum-label">Turned In</div></div></div>
  </div>

  <?php if ($q || $cat || $tab): ?>
  <div class="filter-chips">
    <span class="muted-sm">Active filters:</span>
    <?php if ($tab): ?><span class="f-chip"><b>Tab:</b> <?= e(ucwords(str_replace('_',' ',$tab))) ?><a href="lost-found.php?<?= e(http_build_query(array_filter(['q'=>$q ?: null,'cat'=>$cat ?: null]))) ?>"><i class="bi bi-x"></i></a></span><?php endif; ?>
    <?php if ($cat): ?><span class="f-chip"><b>Category:</b> <?= e(ucfirst($cat)) ?><a href="lost-found.php?<?= e(http_build_query(array_filter(['q'=>$q ?: null,'tab'=>$tab ?: null]))) ?>"><i class="bi bi-x"></i></a></span><?php endif; ?>
    <?php if ($q): ?><span class="f-chip"><b>Search:</b> <?= e($q) ?><a href="lost-found.php?<?= e(http_build_query(array_filter(['tab'=>$tab ?: null,'cat'=>$cat ?: null]))) ?>"><i class="bi bi-x"></i></a></span><?php endif; ?>
    <a class="f-chip-clear" href="lost-found.php">Clear all</a>
  </div>
  <?php endif; ?>

  <div class="manage-card">
    <div class="manage-head">
      <h6 class="manage-title">Lost &amp; Found Items</h6>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <div class="dropdown">
          <button class="btn btn-ghost btn-sm dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-funnel"></i>Filter<?= $cat ? ' <span class="badge badge-soft soft-primary ms-1">1</span>' : '' ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">Category</h6></li>
            <li><a class="dropdown-item" href="lost-found.php?<?= e(http_build_query(array_filter(['q' => $q ?: null, 'tab' => $tab ?: null]))) ?>">All categories</a></li>
            <?php foreach (['electronics' => 'Electronics', 'id_card' => 'ID / Cards', 'accessories' => 'Accessories', 'bags' => 'Bags', 'keys' => 'Keys', 'clothing' => 'Clothing', 'documents' => 'Documents', 'books' => 'Books', 'other' => 'Other'] as $k => $v): ?>
              <li><a class="dropdown-item <?= $cat === $k ? 'active' : '' ?>" href="lost-found.php?<?= e(http_build_query(array_merge($keep, ['cat' => $k]))) ?>"><?= $v ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <form class="nav-search" style="max-width:220px"><i class="bi bi-search"></i>
          <input type="search" name="q" class="form-control" placeholder="Search items..." value="<?= e($q) ?>">
          <input type="hidden" name="tab" value="<?= e($tab) ?>">
          <input type="hidden" name="cat" value="<?= e($cat) ?>">
        </form>
      </div>
    </div>

    <div class="manage-tabs">
      <div class="text-tabs">
        <?= $mkTab('', 'All Items') . $mkTab('unclaimed', 'Unclaimed') . $mkTab('claimed', 'Claimed') . $mkTab('turned_in', 'Turned In') . $mkTab('lost', 'Lost Reports (' . $cnt['lost'] . ')') ?>
      </div>
    </div>

    <?php if (!$items): ?>
      <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <div class="es-title">No items found</div>
        <div class="es-sub">Try adjusting your filters or search terms.</div>
        <a class="btn btn-outline-navy btn-sm" href="lost-found.php"><i class="bi bi-arrow-counterclockwise"></i>Reset Filters</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr>
            <th>#</th><th>ITEM</th><th>CATEGORY</th><th>LOCATION FOUND</th><th>DATE REPORTED</th><th>STATUS</th><th class="text-end">ACTIONS</th>
          </tr></thead>
          <tbody>
          <?php $n = $offset; foreach ($items as $i): $n++; ?>
            <tr>
              <td class="text-muted"><?= $n ?></td>
              <td><div class="item-cell">
                <?php if ($i['image']): ?><img class="item-img" src="../assets/uploads/<?= e($i['image']) ?>" alt="">
                <?php else: ?><span class="item-thumb <?= lf_tint($i['category']) ?>"><i class="bi <?= lf_icon($i['category'], $i['item_name']) ?>"></i></span><?php endif; ?>
                <span><span class="fw-semibold d-block"><?= e($i['item_name']) ?></span>
                <?php if ($i['report_type'] === 'lost'): ?><span class="mini-badge">LOST REPORT</span><?php endif; ?></span>
              </div></td>
              <td><?= badge($i['category']) ?></td>
              <td><?= e($i['location_found']) ?></td>
              <td class="text-nowrap"><?= time_ago($i['created_at']) ?></td>
              <td><?= badge($i['status']) ?></td>
              <td class="text-nowrap text-end">
                <button class="act-quiet" data-view-item data-name="<?= e($i['item_name']) ?>" data-cat="<?= e($i['category']) ?>"
                  data-desc="<?= e((string)$i['description']) ?>" data-loc="<?= e($i['location_found']) ?>"
                  data-date="<?= e($i['date_found']) ?>" data-status="<?= e($i['status']) ?>" data-type="<?= e($i['report_type']) ?>"
                  data-claimed="<?= e((string)$i['claimed_by']) ?>" data-bs-toggle="modal" data-bs-target="#viewItemModal" title="View">
                  <i class="bi bi-eye"></i></button>
                <button class="act-quiet" data-edit-item data-id="<?= $i['id'] ?>" data-name="<?= e($i['item_name']) ?>"
                  data-cat="<?= e($i['category']) ?>" data-desc="<?= e((string)$i['description']) ?>"
                  data-loc="<?= e($i['location_found']) ?>" data-date="<?= e($i['date_found']) ?>"
                  data-status="<?= e($i['status']) ?>" data-claimed="<?= e((string)$i['claimed_by']) ?>"
                  data-bs-toggle="modal" data-bs-target="#editItemModal" title="Edit"><i class="bi bi-pencil"></i></button>
                <form method="post" action="../actions/lost-found.php" class="d-inline"
                  data-confirm="Delete '<?= e($i['item_name']) ?>'? This action cannot be undone."
                  data-confirm-ok="Delete Item">
                  <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $i['id'] ?>">
                  <button class="act-quiet danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card-foot">
        <span class="muted-sm">Showing <?= $total ? ($offset + 1) . '–' . min($offset + $perpage, $total) : 0 ?> of <?= $total ?> items</span>
        <div class="pg">
          <a class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= $pgUrl(max(1, $page - 1)) ?>">Prev</a>
          <?php for ($n = 1; $n <= $pages; $n++): ?>
            <a class="pg-btn <?= $n === $page ? 'active' : '' ?>" href="<?= $pgUrl($n) ?>"><?= $n ?></a>
          <?php endfor; ?>
          <a class="pg-btn <?= $page >= $pages ? 'disabled' : '' ?>" href="<?= $pgUrl(min($pages, $page + 1)) ?>">Next</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Add Item</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/lost-found.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Report Type</label>
              <select class="form-select" name="report_type"><option value="found">Found item</option><option value="lost">Lost report</option></select></div>
            <div class="col-md-6"><label class="form-label">Status <span class="muted-sm fw-normal">(found items only)</span></label>
              <select class="form-select" name="status"><option value="unclaimed">Unclaimed</option><option value="claimed">Claimed</option><option value="turned_in">Turned In</option></select></div>
            <div class="col-md-6"><label class="form-label">Item Name *</label><input class="form-control" name="item_name" required></div>
            <div class="col-md-6"><label class="form-label">Category</label>
              <select class="form-select" name="category">
                <?php foreach (['electronics' => 'Electronics', 'id_card' => 'ID / Cards', 'accessories' => 'Accessories', 'bags' => 'Bags', 'keys' => 'Keys', 'clothing' => 'Clothing', 'documents' => 'Documents', 'books' => 'Books', 'other' => 'Other'] as $k => $v): ?>
                  <option value="<?= $k ?>"><?= $v ?></option>
                <?php endforeach; ?>
              </select></div>
            <div class="col-md-6"><label class="form-label">Location *</label><input class="form-control" name="location_found" required></div>
            <div class="col-md-3"><label class="form-label">Date *</label><input type="date" class="form-control" name="date_found" value="<?= date('Y-m-d') ?>" required></div>
            <div class="col-md-3"><label class="form-label">Photo</label><input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,.gif"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Add Item</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editItemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Edit Item</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/lost-found.php" enctype="multipart/form-data" id="editItemForm">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Item Name *</label><input class="form-control" name="item_name" required></div>
            <div class="col-md-6"><label class="form-label">Category</label>
              <select class="form-select" name="category">
                <?php foreach (['electronics' => 'Electronics', 'id_card' => 'ID / Cards', 'accessories' => 'Accessories', 'bags' => 'Bags', 'keys' => 'Keys', 'clothing' => 'Clothing', 'documents' => 'Documents', 'books' => 'Books', 'other' => 'Other'] as $k => $v): ?>
                  <option value="<?= $k ?>"><?= $v ?></option>
                <?php endforeach; ?>
              </select></div>
            <div class="col-md-4"><label class="form-label">Location *</label><input class="form-control" name="location_found" required></div>
            <div class="col-md-4"><label class="form-label">Date *</label><input type="date" class="form-control" name="date_found" required></div>
            <div class="col-md-4"><label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="unclaimed">Unclaimed</option><option value="claimed">Claimed</option>
                <option value="turned_in">Turned In</option><option value="searching">Searching (lost)</option>
                <option value="reunited">Reunited (lost)</option>
              </select></div>
            <div class="col-md-6"><label class="form-label">Claimed By</label><input class="form-control" name="claimed_by"></div>
            <div class="col-md-6"><label class="form-label">Replace Photo</label><input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
              <div class="muted-sm" style="font-size:11px">Leave blank to keep the current photo.</div></div>
            <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="viewItemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title" id="viName">Item</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2" id="viBadges"></div>
        <p class="mb-2" id="viDesc"></p>
        <div class="muted-sm"><i class="bi bi-geo-alt me-1"></i><span id="viLoc"></span></div>
        <div class="muted-sm"><i class="bi bi-calendar me-1"></i><span id="viDate"></span></div>
        <div class="muted-sm"><i class="bi bi-person me-1"></i>Claimed by: <span id="viClaimed"></span></div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const LABEL = {electronics:'Electronics',id_card:'ID / Cards',accessories:'Accessories',bags:'Bags',keys:'Keys',clothing:'Clothing',documents:'Documents',books:'Books',other:'Other'};
  const STATUS = {unclaimed:['UNCLAIMED','soft-warning'],claimed:['CLAIMED','soft-success'],turned_in:['TURNED IN','soft-purple'],searching:['SEARCHING','soft-warning'],reunited:['REUNITED','soft-primary']};
  document.querySelectorAll('[data-edit-item]').forEach(b => b.addEventListener('click', () => {
    const f = document.getElementById('editItemForm');
    f.id.value = b.dataset.id;
    f.item_name.value = b.dataset.name;
    f.category.value = b.dataset.cat;
    f.description.value = b.dataset.desc || '';
    f.location_found.value = b.dataset.loc || '';
    f.date_found.value = b.dataset.date || '';
    f.status.value = b.dataset.status;
    f.claimed_by.value = b.dataset.claimed || '';
  }));
  document.querySelectorAll('[data-view-item]').forEach(b => b.addEventListener('click', () => {
    viName.textContent = b.dataset.name;
    viDesc.textContent = b.dataset.desc || 'No description provided.';
    viLoc.textContent = b.dataset.loc || '—';
    viDate.textContent = (b.dataset.type === 'lost' ? 'Lost on ' : 'Found on ') + (b.dataset.date || '—');
    viClaimed.textContent = b.dataset.claimed || '—';
    const st = STATUS[b.dataset.status] || ['—','soft-secondary'];
    viBadges.innerHTML =
      '<span class="badge badge-soft soft-secondary">' + (LABEL[b.dataset.cat] || 'Other') + '</span> ' +
      (b.dataset.type === 'lost' ? '<span class="badge badge-soft soft-danger">LOST REPORT</span> ' : '') +
      '<span class="badge badge-soft ' + st[1] + '">' + st[0] + '</span>';
  }));
});
</script>

<?php require_once '../includes/footer.php'; ?>