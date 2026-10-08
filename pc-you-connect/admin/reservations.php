<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Reservations';
 $q = get_str('q');
 $status = in_array(get_str('status'), ['pending', 'approved', 'rejected', 'cancelled', 'completed'], true) ? get_str('status') : '';

 $sql = 'SELECT r.*, f.name AS facility, CONCAT(u.first_name," ",u.last_name) requester, u.user_id AS requester_no
        FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id JOIN users u ON u.id=r.user_id WHERE 1=1';
 $p = [];
if ($q !== '') { $sql .= ' AND (f.name LIKE ? OR r.purpose LIKE ? OR CONCAT(u.first_name," ",u.last_name) LIKE ?)'; $p = ["%$q%", "%$q%", "%$q%"]; }
if ($status !== '') { $sql .= ' AND r.status=?'; $p[] = $status; }
 $sql .= ' ORDER BY FIELD(r.status,"pending","approved","completed","rejected","cancelled"), r.res_date DESC LIMIT 300';
 $st = db()->prepare($sql);
 $st->execute($p);
 $rows = $st->fetchAll();
 $h = db()->prepare("SELECT h.*, CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) actor
    FROM reservation_status_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.reservation_id=? ORDER BY h.created_at ASC");

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <div class="page-head">
    <div><h1 class="page-title">Reservations</h1><p class="page-sub">Review, approve, and complete facility reservations.</p></div>
    <a class="btn btn-ghost" href="calendar.php"><i class="bi bi-calendar3"></i>Schedule</a>
  </div>

  <div class="card mb-4"><div class="card-clean-head">
    <div class="filter-tabs">
      <?php $mk = fn($s, $l) => '<a class="filter-tab' . ($status === $s ? ' active' : '') . '" href="reservations.php?' . e(http_build_query(array_filter(['q' => $q, 'status' => $s ?: null]))) . '">' . $l . '</a>';
      echo $mk('', 'All') . $mk('pending', 'Pending') . $mk('approved', 'Approved') . $mk('completed', 'Completed') . $mk('rejected', 'Rejected') . $mk('cancelled', 'Cancelled'); ?>
    </div>
    <form class="nav-search" style="max-width:260px"><i class="bi bi-search"></i>
      <input type="search" name="q" class="form-control" placeholder="Search reservations..." value="<?= e($q) ?>">
      <input type="hidden" name="status" value="<?= e($status) ?>"></form>
  </div></div>

  <div class="manage-card">
    <?php if (!$rows): ?>
      <div class="empty-state"><i class="bi bi-calendar-check"></i>
        <div class="es-title">No reservations found</div>
        <div class="es-sub">Reservation requests from students will appear here for review.</div>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>#</th><th>REQUESTER</th><th>FACILITY</th><th>PURPOSE</th><th>DATE &amp; TIME</th><th>STATUS</th><th class="text-end">ACTIONS</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="text-muted">#<?= (int)$r['id'] ?></td>
            <td><span class="fw-semibold d-block"><?= e($r['requester']) ?></span><span class="muted-sm"><?= e($r['requester_no']) ?></span></td>
            <td><?= e($r['facility']) ?></td>
            <td><?= e($r['purpose']) ?><?= $r['attachments'] ? ' <i class="bi bi-paperclip muted-sm" title="Has documentation"></i>' : '' ?></td>
            <td class="text-nowrap"><?= fmt_date($r['res_date']) ?><br><span class="muted-sm"><?= fmt_time12($r['start_time']) ?> – <?= fmt_time12($r['end_time']) ?></span></td>
            <td><?= badge($r['status']) ?></td>
            <td class="text-nowrap text-end">
              <button class="act-quiet" data-bs-toggle="modal" data-bs-target="#det<?= $r['id'] ?>" title="Details & history"><i class="bi bi-eye"></i></button>
              <?php if ($r['status'] === 'pending'): ?>
                <button class="act-quiet" style="color:var(--success)" data-bs-toggle="modal" data-bs-target="#decideModal"
                  data-id="<?= $r['id'] ?>" data-name="<?= e($r['facility']) ?> — <?= e($r['requester']) ?>"
                  data-date="<?= fmt_date($r['res_date']) ?> · <?= fmt_time12($r['start_time']) ?>–<?= fmt_time12($r['end_time']) ?>" title="Approve / Reject">
                  <i class="bi bi-clipboard-check"></i></button>
              <?php elseif ($r['status'] === 'approved'): ?>
                <form method="post" action="../actions/reservation.php" class="d-inline"
                  data-confirm="Mark reservation #<?= (int)$r['id'] ?> as completed?" data-confirm-title="Mark as completed?"
                  data-confirm-ok="Mark Completed" data-confirm-variant="primary">
                  <input type="hidden" name="action" value="complete"><input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                  <button class="act-quiet" style="color:var(--success)" title="Mark Completed"><i class="bi bi-check2-all"></i></button>
                </form>
                <form method="post" action="../actions/reservation.php" class="d-inline"
                  data-confirm="Cancel reservation #<?= (int)$r['id'] ?> for '<?= e($r['facility']) ?>'? The requester will be notified."
                  data-confirm-ok="Cancel Reservation">
                  <input type="hidden" name="action" value="cancel"><input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                  <button class="act-quiet danger" title="Cancel"><i class="bi bi-x-lg"></i></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="decideModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Review Reservation</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/reservation.php">
        <input type="hidden" name="action" value="decide">
        <input type="hidden" name="reservation_id" value="" id="decideId">
        <div class="modal-body">
          <div class="pc-confirm-details">
            <div class="pc-detail"><span>Reservation</span><b id="decideName"></b></div>
            <div class="pc-detail"><span>Schedule</span><b id="decideSched"></b></div>
          </div>
          <label class="form-label">Remarks (sent to the requester)</label>
          <textarea class="form-control" name="remarks" rows="3" placeholder="e.g., Approved for academic use."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="decision" value="reject" class="btn btn-ghost" style="border-color:var(--danger);color:var(--danger)"
            data-require-reason="#decideModal textarea[name=remarks]">Reject</button>
          <button type="submit" name="decision" value="approve" class="btn btn-navy">Approve Reservation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php foreach ($rows as $r): $h->execute([$r['id']]); $hist = $h->fetchAll(); ?>
<div class="modal fade" id="det<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Reservation #<?= (int)$r['id'] ?> <?= badge($r['status']) ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row-sep"><div class="muted-sm">Requester</div><div class="fw-semibold"><?= e($r['requester']) ?> · <?= e($r['requester_no']) ?></div></div>
        <div class="row-sep"><div class="muted-sm">Facility / Purpose</div><div><?= e($r['facility']) ?> — <?= e($r['purpose']) ?></div></div>
        <div class="row-sep"><div class="muted-sm">Schedule</div><div><?= fmt_date($r['res_date']) ?> · <?= fmt_time12($r['start_time']) ?> – <?= fmt_time12($r['end_time']) ?></div></div>
        <?php if ($r['notes']): ?><div class="row-sep"><div class="muted-sm">Notes</div><div><?= nl2br(e($r['notes'])) ?></div></div><?php endif; ?>
        <?php if ($r['attachments']): ?><div class="row-sep"><div class="muted-sm">Documentation</div><?= res_attachments_html($r['attachments']) ?></div><?php endif; ?>
        <?php if ($r['remarks']): ?><div class="row-sep"><div class="muted-sm">Admin Remarks</div><div><?= e($r['remarks']) ?></div></div><?php endif; ?>
        <div class="row-sep"><div class="muted-sm mb-2">Status History</div>
          <?php if (!$hist): ?><div class="muted-sm">No history records.</div>
          <?php else: foreach ($hist as $hr): ?>
            <div class="d-flex gap-2 mb-2">
              <i class="bi bi-record-fill text-primary mt-1" style="font-size:9px"></i>
              <div><div class="fw-semibold" style="font-size:13px"><?= e(ucfirst($hr['status'])) ?> <span class="muted-sm fw-normal">· <?= time_ago($hr['created_at']) ?></span></div>
              <div class="muted-sm"><?= e($hr['remarks']) ?><?= trim($hr['actor']) !== '' ? ' — ' . e($hr['actor']) : '' ?></div></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-target="#decideModal"][data-id]').forEach(b => b.addEventListener('click', () => {
    decideId.value = b.dataset.id;
    decideName.textContent = b.dataset.name;
    decideSched.textContent = b.dataset.date || '';
  }));
});
</script>

<?php require_once '../includes/footer.php'; ?>