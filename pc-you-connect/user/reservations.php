<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'My Reservations';
 $st = db()->prepare('SELECT r.*, f.name AS facility FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id WHERE r.user_id=? ORDER BY r.res_date DESC, r.start_time DESC LIMIT 300');
 $st->execute([$u['id']]);
 $all = $st->fetchAll();

 $displayed = [];
foreach ($all as $r) {
    $d = $r['status'];
    if ($r['status'] === 'approved') $d = strtotime($r['res_date'] . ' ' . $r['end_time']) < time() ? 'completed' : 'upcoming';
    $r['display'] = $d;
    $displayed[] = $r;
}
 $tab = get_str('tab');
 $validTabs = ['pending', 'upcoming', 'completed', 'rejected', 'cancelled'];
if (!in_array($tab, $validTabs, true)) $tab = '';
 $rows = $tab === '' ? $displayed : array_values(array_filter($displayed, fn($r) => $r['display'] === $tab));

 $h = db()->prepare("SELECT h.*, CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) actor
    FROM reservation_status_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.reservation_id=? ORDER BY h.created_at ASC");

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">My Reservations</h1><p class="page-sub">Track the status of your facility reservations.</p></div>
  <a class="btn btn-navy" href="reserve.php"><i class="bi bi-plus-lg me-1"></i>New Reservation</a>
</div>

<div class="card mb-4"><div class="card-clean-head">
  <div class="filter-tabs">
    <?php $mk = fn($t, $l) => '<a class="filter-tab' . ($tab === $t ? ' active' : '') . '" href="reservations.php' . ($t ? '?tab=' . $t : '') . '">' . $l . '</a>';
    echo $mk('', 'All') . $mk('pending', 'Pending') . $mk('upcoming', 'Upcoming') . $mk('completed', 'Completed') . $mk('rejected', 'Rejected') . $mk('cancelled', 'Cancelled'); ?>
  </div>
  <span class="muted-sm"><?= count($rows) ?> reservation<?= $rows && count($rows) > 1 ? 's' : '' ?></span>
</div></div>

<div class="card">
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>FACILITY</th><th>PURPOSE</th><th>DATE &amp; TIME</th><th>STATUS</th><th class="text-end">ACTIONS</th></tr></thead>
      <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No reservations here yet. <a class="link-more" href="facilities.php">Browse facilities →</a></td></tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><div class="item-cell"><span class="item-thumb"><i class="bi bi-door-open"></i></span><span class="fw-semibold"><?= e($r['facility']) ?></span></div></td>
          <td><?= e($r['purpose']) ?><?= $r['attachments'] ? ' <i class="bi bi-paperclip muted-sm" title="Has attachments"></i>' : '' ?></td>
          <td class="text-nowrap"><?= fmt_date($r['res_date']) ?><br><span class="muted-sm"><?= fmt_time12($r['start_time']) ?> – <?= fmt_time12($r['end_time']) ?></span></td>
          <td><?= badge($r['display']) ?><?= $r['remarks'] ? '<div class="muted-sm" style="max-width:180px">' . e(mb_substr($r['remarks'], 0, 60)) . '</div>' : '' ?></td>
          <td class="text-nowrap text-end">
            <button class="btn-sm-icon" data-bs-toggle="modal" data-bs-target="#resDet<?= $r['id'] ?>" title="View details"><i class="bi bi-eye"></i></button>
            <?php if (in_array($r['status'], ['pending', 'approved']) && strtotime($r['res_date'] . ' ' . $r['end_time']) >= time()): ?>
            <form method="post" action="../actions/reservation.php" class="d-inline" data-confirm="Cancel this reservation?">
              <input type="hidden" name="action" value="cancel"><input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
              <button class="btn-sm-icon danger" title="Cancel"><i class="bi bi-x-lg"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($displayed as $r): $h->execute([$r['id']]); $hist = $h->fetchAll(); ?>
<div class="modal fade" id="resDet<?= $r['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title"><?= e($r['facility']) ?> <?= badge($r['display']) ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row-sep"><div class="muted-sm">Purpose</div><div class="fw-semibold"><?= e($r['purpose']) ?></div></div>
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
              <div class="muted-sm"><?= e($hr['remarks']) ?></div></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require_once '../includes/footer.php'; ?>