<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Claim Requests';
 $status = in_array(get_str('status'), ['pending', 'approved', 'rejected'], true) ? get_str('status') : '';

 $sql = 'SELECT c.*, i.item_name, i.image, i.category, CONCAT(u.first_name," ",u.last_name) claimant, u.user_id claimant_no
        FROM claim_requests c JOIN lost_found_items i ON i.id=c.item_id JOIN users u ON u.id=c.user_id';
 $p = [];
if ($status !== '') { $sql .= ' WHERE c.status=?'; $p[] = $status; }
 $sql .= ' ORDER BY FIELD(c.status,"pending","approved","rejected"), c.created_at DESC LIMIT 200';
 $st = db()->prepare($sql);
 $st->execute($p);
 $claims = $st->fetchAll();
 $counts = [
    ''         => (int)db_scalar('SELECT COUNT(*) FROM claim_requests'),
    'pending'  => (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='pending'"),
    'approved' => (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='approved'"),
    'rejected' => (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='rejected'"),
];

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <div class="page-head">
    <div><h1 class="page-title">Claim Requests</h1><p class="page-sub">Verify ownership claims for found items before release.</p></div>
    <a class="btn btn-ghost" href="lost-found.php"><i class="bi bi-arrow-left"></i>Lost &amp; Found</a>
  </div>

  <div class="card mb-4"><div class="card-clean-head">
    <div class="filter-tabs">
      <?php $mk = fn($s, $l) => '<a class="filter-tab' . ($status === $s ? ' active' : '') . '" href="claims.php' . ($s ? '?status=' . $s : '') . '">' . $l . ' (' . $counts[$s] . ')</a>';
      echo $mk('', 'All') . $mk('pending', 'Pending') . $mk('approved', 'Approved') . $mk('rejected', 'Rejected'); ?>
    </div>
  </div></div>

  <div class="card">
    <?php if (!$claims): ?>
      <div class="empty-state"><i class="bi bi-hand-index-thumb"></i>
        <div class="es-title">No claim requests<?= $status ? ' in this tab' : '' ?></div>
        <div class="es-sub">Claim submissions from students will appear here for verification.</div>
      </div>
    <?php else: foreach ($claims as $c): ?>
      <div class="d-flex gap-3 p-3 px-4 border-bottom align-items-start flex-wrap">
        <?php if ($c['image']): ?><img class="item-img" style="width:46px;height:46px" src="../assets/uploads/<?= e($c['image']) ?>" alt="">
        <?php else: ?><span class="item-thumb"><i class="bi <?= lf_icon($c['category'], $c['item_name']) ?>"></i></span><?php endif; ?>
        <div class="flex-grow-1" style="min-width:220px">
          <div class="fw-bold"><?= e($c['item_name']) ?></div>
          <div class="muted-sm"><strong>Claimant:</strong> <?= e($c['claimant']) ?> · <?= e($c['claimant_no']) ?>
            <?= $c['claimant_contact'] ? ' · ' . e($c['claimant_contact']) : '' ?></div>
          <div class="muted-sm"><strong>Proof:</strong> <?= e(mb_substr($c['proof_details'], 0, 120)) ?></div>
          <?= res_attachments_html($c['attachments'] ?? null) ?>
          <div class="muted-sm" style="font-size:11px">Submitted <?= time_ago($c['created_at']) ?>
            <?= $c['admin_remarks'] ? ' · <strong>Admin:</strong> ' . e($c['admin_remarks']) : '' ?></div>
        </div>
        <?= badge($c['status']) ?>
        <?php if ($c['status'] === 'pending'): ?>
        <div class="d-flex gap-2">
          <button class="btn btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#decideModal"
            data-id="<?= $c['id'] ?>" data-name="<?= e($c['item_name']) ?> — <?= e($c['claimant']) ?>">Review</button>
        </div>
        <?php endif; ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<div class="modal fade" id="decideModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Verify Claim</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/lost-found.php">
        <input type="hidden" name="action" value="claim_decide">
        <input type="hidden" name="claim_id" value="" id="decideId">
        <div class="modal-body">
          <p class="muted-sm" id="decideName"></p>
          <label class="form-label">Remarks (sent to the claimant)</label>
          <textarea class="form-control" name="remarks" rows="3" placeholder="e.g., Proof verified. Pick up at Student Affairs Office with your ID."></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="decision" value="reject" class="btn btn-ghost" style="border-color:var(--danger);color:var(--danger)"
            data-require-reason="#decideModal textarea[name=remarks]">Reject</button>
          <button type="submit" name="decision" value="approve" class="btn btn-navy">Approve Claim</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-bs-target="#decideModal"][data-id]').forEach(b => b.addEventListener('click', () => {
    decideId.value = b.dataset.id;
    decideName.textContent = b.dataset.name;
  }));
});
</script>

<?php require_once '../includes/footer.php'; ?>