<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    if ($act === 'create') {
        if (post('user_id') === '' || post('first_name') === '' || post('last_name') === '' || post('password') === '') {
            flash_set('All fields except email are required.', 'danger');
        } elseif (db_scalar('SELECT COUNT(*) FROM users WHERE user_id=?', [post('user_id')])) {
            flash_set('That User ID already exists.', 'danger');
        } else {
            db()->prepare('INSERT INTO users (role,user_id,first_name,last_name,email,password) VALUES (?,?,?,?,?,?)')
               ->execute([post('role', 'student'), post('user_id'), post('first_name'), post('last_name'),
                          post('email') ?: null, password_hash(post('password'), PASSWORD_DEFAULT)]);
            log_activity($u['id'], 'user_create', post('user_id'));
            flash_set('User created.');
        }
    } elseif ($act === 'update') {
        $id = (int)post('id');
        $sql = 'UPDATE users SET role=?, first_name=?, last_name=?, email=?, status=?';
        $p = [post('role', 'student'), post('first_name'), post('last_name'), post('email') ?: null, post('status', 'active')];
        if (post('password') !== '') { $sql .= ', password=?'; $p[] = password_hash(post('password'), PASSWORD_DEFAULT); }
        $sql .= ' WHERE id=?'; $p[] = $id;
        db()->prepare($sql)->execute($p);
        log_activity($u['id'], 'user_update', post('user_id'));
        flash_set('User updated.');
    } elseif ($act === 'toggle') {
        $id = (int)post('id');
        if ($id === $u['id']) flash_set('You cannot deactivate your own account.', 'danger');
        else {
            db()->prepare("UPDATE users SET status=IF(status='active','inactive','active') WHERE id=?")->execute([$id]);
            flash_set('User status changed.');
        }
    } elseif ($act === 'delete') {
        $id = (int)post('id');
        if ($id === $u['id']) flash_set('You cannot delete your own account.', 'danger');
        else {
            try {
                db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
                log_activity($u['id'], 'user_delete', '#' . $id);
                flash_set('User deleted.');
            } catch (PDOException $ex) {
                flash_set('Cannot delete: this user has related records. Deactivate instead.', 'danger');
            }
        }
    }
    redirect('users.php');
}

 $q = get_str('q');
 $sql = 'SELECT * FROM users WHERE 1=1'; $p = [];
if ($q !== '') { $sql .= ' AND (user_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)'; $p = ["%$q%", "%$q%", "%$q%", "%$q%"]; }
 $sql .= ' ORDER BY created_at DESC LIMIT 300';
 $st = db()->prepare($sql); $st->execute($p); $users = $st->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Users</h1><p class="page-sub">Manage student, faculty, staff, and admin accounts.</p></div>
  <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn"><i class="bi bi-plus-lg me-1"></i>Add User</button>
</div>

<div class="card mb-4"><div class="card-clean-head">
  <span class="muted-sm"><?= count($users) ?> users</span>
  <form class="nav-search" style="max-width:260px"><i class="bi bi-search"></i>
    <input type="search" name="q" class="form-control" placeholder="Search users..." value="<?= e($q) ?>"></form>
</div></div>

<div class="card"><div class="table-wrap">
  <table class="table">
    <thead><tr><th>#</th><th>USER</th><th>USER ID</th><th>ROLE</th><th>EMAIL</th><th>STATUS</th><th>ACTIONS</th></tr></thead>
    <tbody>
    <?php foreach ($users as $usr): ?>
      <tr>
        <td class="text-muted">#<?= (int)$usr['id'] ?></td>
        <td><div class="item-cell"><span class="item-thumb"><?= e(initials($usr['first_name'], $usr['last_name'])) ?></span>
          <span class="fw-semibold"><?= e($usr['first_name'] . ' ' . $usr['last_name']) ?></span></div></td>
        <td><?= e($usr['user_id']) ?></td>
        <td><span class="badge badge-soft <?= ['admin'=>'soft-primary','student'=>'soft-info','faculty'=>'soft-purple','staff'=>'soft-secondary'][$usr['role']] ?>"><?= ucfirst($usr['role']) ?></span></td>
        <td><?= e($usr['email'] ?: '—') ?></td>
        <td><?= badge($usr['status']) ?></td>
        <td class="text-nowrap">
          <button class="btn-sm-icon" data-edit-user data-id="<?= $usr['id'] ?>" data-uid="<?= e($usr['user_id']) ?>"
            data-fn="<?= e($usr['first_name']) ?>" data-ln="<?= e($usr['last_name']) ?>" data-em="<?= e((string)$usr['email']) ?>"
            data-role="<?= e($usr['role']) ?>" data-status="<?= e($usr['status']) ?>"
            data-bs-toggle="modal" data-bs-target="#userModal" title="Edit"><i class="bi bi-pencil"></i></button>
          <?php if ($usr['id'] !== $u['id']): ?>
          <form method="post" class="d-inline"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $usr['id'] ?>">
            <button class="btn-sm-icon" title="<?= $usr['status'] === 'active' ? 'Deactivate' : 'Activate' ?>"><i class="bi bi-power"></i></button></form>
          <form method="post" class="d-inline" data-confirm="Delete this user permanently?">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $usr['id'] ?>">
            <button class="btn-sm-icon danger" title="Delete"><i class="bi bi-trash"></i></button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>

<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h6 class="modal-title" id="userModalTitle">Add User</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
      <form method="post" id="userForm">
        <input type="hidden" name="action" value="create" id="userAction">
        <input type="hidden" name="id" value="" id="userId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">User ID / Student No. *</label><input class="form-control" name="user_id" required></div>
            <div class="col-md-6"><label class="form-label">Role</label>
              <select class="form-select" name="role">
                <option value="student">Student</option><option value="faculty">Faculty</option>
                <option value="staff">Staff</option><option value="admin">Admin</option>
              </select></div>
            <div class="col-md-6"><label class="form-label">First Name *</label><input class="form-control" name="first_name" required></div>
            <div class="col-md-6"><label class="form-label">Last Name *</label><input class="form-control" name="last_name" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
            <div class="col-md-6"><label class="form-label" id="pwLabel">Password *</label><input type="password" class="form-control" name="password" id="userPw" minlength="8">
              <div class="muted-sm" style="font-size:11px" id="pwHint" hidden>Leave blank to keep current password.</div></div>
            <div class="col-md-6"><label class="form-label">Status</label>
              <select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-navy" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-navy px-4">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-edit-user]').forEach(b => b.addEventListener('click', () => {
    userModalTitle.textContent = 'Edit User';
    userAction.value = 'update';
    userId.value = b.dataset.id;
    userForm.user_id.value = b.dataset.uid;
    userForm.first_name.value = b.dataset.fn;
    userForm.last_name.value = b.dataset.ln;
    userForm.email.value = b.dataset.em || '';
    userForm.role.value = b.dataset.role;
    userForm.status.value = b.dataset.status;
    userPw.removeAttribute('required');
    userPw.value = '';
    pwLabel.textContent = 'Password';
    pwHint.hidden = false;
  }));
  document.getElementById('addUserBtn').addEventListener('click', () => {
    userModalTitle.textContent = 'Add User';
    userAction.value = 'create';
    userForm.reset();
    userPw.setAttribute('required', '');
    pwLabel.textContent = 'Password *';
    pwHint.hidden = true;
  });
});
</script>

<?php require_once '../includes/footer.php'; ?>