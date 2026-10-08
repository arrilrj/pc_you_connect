<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Facilities';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'create') {
        if (post('name') === '') {
            flash_set('Facility name is required.', 'danger');
        } else {
            $img = upload_image($_FILES['image'] ?? null);
            db()->prepare('INSERT INTO facilities (name,description,location,status,image) VALUES (?,?,?,?,?)')
               ->execute([post('name'), post('description') ?: null, post('location'), post('status', 'available'), $img]);
            log_activity((int)$u['id'], 'facility_create', post('name'));
            flash_set('Facility added.');
        }
    } elseif ($act === 'update') {
        $id  = (int)post('id');
        $cur = db_scalar('SELECT image FROM facilities WHERE id=?', [$id]);
        $img = upload_image($_FILES['image'] ?? null, $cur ?: null);
        db()->prepare('UPDATE facilities SET name=?, description=?, location=?, status=?, image=? WHERE id=?')
           ->execute([post('name'), post('description') ?: null, post('location'), post('status', 'available'), $img, $id]);
        log_activity((int)$u['id'], 'facility_update', post('name'));
        flash_set('Facility updated.');
    } elseif ($act === 'delete') {
        $id = (int)post('id');
        if (db_scalar('SELECT COUNT(*) FROM facility_reservations WHERE facility_id=?', [$id])) {
            flash_set('Cannot delete: this facility has existing reservations.', 'danger');
        } else {
            db()->prepare('DELETE FROM facilities WHERE id=?')->execute([$id]);
            log_activity((int)$u['id'], 'facility_delete', '#' . $id);
            flash_set('Facility deleted.');
        }
    }
    redirect('facilities.php');
}

 $q = get_str('q');
 $sql = 'SELECT * FROM facilities WHERE 1=1';
 $p = [];
if ($q !== '') { $sql .= ' AND (name LIKE ? OR location LIKE ?)'; $p = ["%$q%", "%$q%"]; }
 $sql .= ' ORDER BY name';
 $st = db()->prepare($sql);
 $st->execute($p);
 $facilities = $st->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Facilities</h1><p class="page-sub">Manage bookable campus facilities.</p></div>
  <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#facModal" id="addFacBtn"><i class="bi bi-plus-lg me-1"></i>Add Facility</button>
</div>

<div class="card mb-4"><div class="card-clean-head">
  <span class="muted-sm"><?= count($facilities) ?> facilities</span>
  <form class="nav-search" style="max-width:240px"><i class="bi bi-search"></i>
    <input type="search" name="q" class="form-control" placeholder="Search facilities..." value="<?= e($q) ?>"></form>
</div></div>

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
          <button class="btn btn-outline-navy btn-sm flex-fill" data-edit-fac
            data-id="<?= $f['id'] ?>" data-name="<?= e($f['name']) ?>"
            data-desc="<?= e((string)$f['description']) ?>" data-loc="<?= e((string)$f['location']) ?>"
            data-status="<?= e($f['status']) ?>"
            data-bs-toggle="modal" data-bs-target="#facModal"><i class="bi bi-pencil me-1"></i>Edit</button>
          <form method="post" class="flex-fill" data-confirm="Delete '<?= e($f['name']) ?>'?">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $f['id'] ?>">
            <button class="btn btn-ghost btn-sm w-100" style="border-color:var(--danger);color:var(--danger)"><i class="bi bi-trash me-1"></i>Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="facModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title" id="facModalTitle">Add Facility</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" id="facForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create" id="facAction">
        <input type="hidden" name="id" value="" id="facId">
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Name *</label><input class="form-control" name="name" required></div>
          <div class="row g-3 mb-3">
            <div class="col-md-7"><label class="form-label">Location</label><input class="form-control" name="location"></div>
            <div class="col-md-5"><label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="available">Available</option><option value="maintenance">Maintenance</option><option value="unavailable">Unavailable</option>
              </select></div>
          </div>
          <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3"></textarea></div>
          <div><label class="form-label">Photo</label><input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
            <div class="muted-sm" style="font-size:11px">Optional, max 2MB. On edit, leave blank to keep the current photo.</div></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-edit-fac]').forEach(b => b.addEventListener('click', () => {
    facModalTitle.textContent = 'Edit Facility';
    facAction.value = 'update';
    facId.value = b.dataset.id;
    facForm.name.value = b.dataset.name;
    facForm.description.value = b.dataset.desc || '';
    facForm.location.value = b.dataset.loc || '';
    facForm.status.value = b.dataset.status;
    facForm.image.value = '';
  }));
  document.getElementById('addFacBtn').addEventListener('click', () => {
    facModalTitle.textContent = 'Add Facility';
    facAction.value = 'create';
    facForm.reset();
  });
});
</script>

<?php require_once '../includes/footer.php'; ?>