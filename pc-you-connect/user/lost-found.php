<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Lost & Found';
 $view = in_array(get_str('view'), ['found', 'lost', 'claims'], true) ? get_str('view') : 'found';
 $q = get_str('q');

require_once '../includes/sidebar.php';
require_once '../includes/header.php';

if ($view === 'found'):
    $sql = "SELECT * FROM lost_found_items WHERE report_type='found'"; $p = [];
    if ($q !== '') { $sql .= ' AND (item_name LIKE ? OR location_found LIKE ?)'; $p = ["%$q%", "%$q%"]; }
    $sql .= ' ORDER BY created_at DESC LIMIT 200';
    $st = db()->prepare($sql); $st->execute($p); $items = $st->fetchAll();
?>
<div class="page-head">
  <div><h1 class="page-title">Lost &amp; Found</h1><p class="page-sub">Items found on campus. Spot yours? Submit a claim with proof.</p></div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-navy" href="add-lost-found.php?type=lost"><i class="bi bi-search-heart me-1"></i>I Lost an Item</a>
    <a class="btn btn-navy" href="add-lost-found.php?type=found"><i class="bi bi-hand-thumbs-up me-1"></i>I Found an Item</a>
  </div>
</div>

<div class="card mb-4"><div class="card-clean-head">
  <div class="filter-tabs">
    <a class="filter-tab active" href="lost-found.php?view=found">Found Items</a>
    <a class="filter-tab" href="lost-found.php?view=lost">My Lost Reports</a>
    <a class="filter-tab" href="lost-found.php?view=claims">My Claims</a>
  </div>
  <form class="nav-search" style="max-width:240px"><i class="bi bi-search"></i>
    <input type="hidden" name="view" value="found">
    <input type="search" name="q" class="form-control" placeholder="Search items..." value="<?= e($q) ?>">
  </form>
</div></div>

<?php if (!$items): ?>
  <div class="card p-4 text-center text-muted">No found items right now.</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($items as $i): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 overflow-hidden">
      <?php if ($i['image']): ?>
        <img src="../assets/uploads/<?= e($i['image']) ?>" class="w-100" style="height:140px;object-fit:cover" alt="">
      <?php else: ?>
        <div class="facility-cover" style="height:100px;font-size:34px"><i class="bi <?= lf_icon($i['category'], $i['item_name']) ?>"></i></div>
      <?php endif; ?>
      <div class="p-3 d-flex flex-column flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
          <span class="fw-bold"><?= e($i['item_name']) ?></span><?= badge($i['status']) ?>
        </div>
        <?= badge($i['category']) ?>
        <div class="muted-sm mt-2 mb-1"><i class="bi bi-geo-alt me-1"></i><?= e($i['location_found']) ?></div>
        <div class="muted-sm mb-3"><i class="bi bi-clock me-1"></i>Found <?= time_ago($i['created_at']) ?></div>
        <?php if ($i['status'] === 'unclaimed'): ?>
          <div class="mt-auto">
            <button class="btn btn-navy btn-sm w-100" data-bs-toggle="modal" data-bs-target="#claimModal"
              data-id="<?= $i['id'] ?>" data-name="<?= e($i['item_name']) ?>"><i class="bi bi-hand-index-thumb me-1"></i>This is mine — Claim</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Claim modal -->
<div class="modal fade" id="claimModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title">Claim: <span id="clName"></span></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" action="../actions/lost-found.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="claim">
        <input type="hidden" name="item_id" id="clId">
        <div class="modal-body">
          <label class="form-label">Proof of ownership <span class="text-danger">*</span></label>
          <textarea class="form-control mb-3" name="proof_details" rows="4" required
            placeholder="Describe distinguishing details only the owner would know — stickers, scratches, contents, engravings…"></textarea>
          <label class="form-label">Contact number / Messenger</label>
          <input type="text" class="form-control mb-3" name="claimant_contact" placeholder="So the office can reach you (optional)">
          <label class="form-label">Supporting Photos / Documents <span class="muted-sm fw-normal">(optional)</span></label>
          <input type="file" class="form-control" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx">
          <div class="form-text">Up to 2 files, 2MB each — e.g., a photo of you with the item, a receipt, a close-up of a marking.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Submit Claim</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('#claimModal [data-id]').forEach(b => b.addEventListener('click', () => {
    clId.value = b.dataset.id;
    clName.textContent = b.dataset.name;
  }));
});
</script>

<?php elseif ($view === 'lost'):
    $st = db()->prepare("SELECT * FROM lost_found_items WHERE report_type='lost' AND reporter_id=? ORDER BY created_at DESC");
    $st->execute([$u['id']]); $items = $st->fetchAll();
?>
<div class="page-head">
  <div><h1 class="page-title">My Lost Reports</h1><p class="page-sub">Items you reported losing. We notify you when a match is found.</p></div>
  <a class="btn btn-navy" href="add-lost-found.php?type=lost"><i class="bi bi-plus-lg me-1"></i>File a Lost Report</a>
</div>
<div class="card mb-4"><div class="card-clean-head"><div class="filter-tabs">
  <a class="filter-tab" href="lost-found.php?view=found">Found Items</a>
  <a class="filter-tab active" href="lost-found.php?view=lost">My Lost Reports</a>
  <a class="filter-tab" href="lost-found.php?view=claims">My Claims</a>
</div></div></div>

<?php if (!$items): ?>
  <div class="card p-4 text-center text-muted">You have no lost reports. <a class="link-more" href="add-lost-found.php?type=lost">File one →</a></div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($items as $i): ?>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100 p-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="item-thumb <?= lf_tint($i['category']) ?>"><i class="bi <?= lf_icon($i['category'], $i['item_name']) ?>"></i></span>
        <div class="flex-grow-1"><span class="fw-bold d-block"><?= e($i['item_name']) ?></span><?= badge($i['category']) ?></div>
        <?= badge($i['status']) ?>
      </div>
      <div class="muted-sm mb-1"><i class="bi bi-geo-alt me-1"></i>Last seen: <?= e($i['location_found']) ?></div>
      <div class="muted-sm mb-3"><i class="bi bi-calendar me-1"></i>Lost on <?= fmt_date($i['date_found']) ?></div>
      <?php if ($i['description']): ?><div class="muted-sm mb-3"><?= e(mb_substr($i['description'], 0, 90)) ?></div><?php endif; ?>
      <form method="post" action="../actions/lost-found.php" class="mt-auto" data-confirm="Remove this lost report?">
        <input type="hidden" name="action" value="delete_own"><input type="hidden" name="id" value="<?= $i['id'] ?>">
        <button class="btn btn-ghost btn-sm w-100"><i class="bi bi-trash me-1"></i>Remove Report</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php else:
    $st = db()->prepare('SELECT c.*, i.item_name, i.status AS item_status FROM claim_requests c JOIN lost_found_items i ON i.id=c.item_id WHERE c.user_id=? ORDER BY c.created_at DESC');
    $st->execute([$u['id']]); $claims = $st->fetchAll();
?>
<div class="page-head">
  <div><h1 class="page-title">My Claims</h1><p class="page-sub">Track your claim requests and the admin's decision.</p></div>
</div>
<div class="card mb-4"><div class="card-clean-head"><div class="filter-tabs">
  <a class="filter-tab" href="lost-found.php?view=found">Found Items</a>
  <a class="filter-tab" href="lost-found.php?view=lost">My Lost Reports</a>
  <a class="filter-tab active" href="lost-found.php?view=claims">My Claims</a>
</div></div></div>

<div class="card">
  <?php if (!$claims): ?>
    <div class="p-4 text-center text-muted">No claim requests yet.</div>
  <?php else: foreach ($claims as $c): ?>
    <div class="d-flex gap-3 p-3 px-4 border-bottom align-items-start flex-wrap">
      <span class="item-thumb"><i class="bi bi-hand-index-thumb"></i></span>
      <div class="flex-grow-1">
        <div class="fw-bold"><?= e($c['item_name']) ?></div>
        <div class="muted-sm">Proof: <?= e(mb_substr($c['proof_details'], 0, 100)) ?></div>
        <div class="muted-sm" style="font-size:11px">Submitted <?= time_ago($c['created_at']) ?></div>
        <?php if ($c['admin_remarks']): ?><div class="muted-sm mt-1"><i class="bi bi-chat-left-text me-1"></i><strong>Admin:</strong> <?= e($c['admin_remarks']) ?></div><?php endif; ?>
      </div>
      <?= badge($c['status']) ?>
      <?php if ($c['status'] === 'approved'): ?>
        <div class="w-100 muted-sm"><i class="bi bi-info-circle me-1"></i>Approved — please pick up the item at the Student Affairs Office with your ID.</div>
      <?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>