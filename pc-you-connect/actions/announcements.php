<?php
require_once __DIR__ . '/../includes/admin_auth.php';
 $action = $_POST['action'] ?? '';

if ($action === 'increment_views') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) db()->prepare("UPDATE announcements SET views=views+1 WHERE id=? AND status='published'")->execute([$id]);
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');
require_admin();
 $u = current_user();
 $back = '../admin/announcements.php';

 $catList = ['facilities', 'lost_found'];
 $prioList = ['normal', 'high'];

if ($action === 'add') {
    $title = post('title');
    $content = post('content');
    $cat = in_array(post('category'), $catList, true) ? post('category') : 'facilities';
    $prio = in_array(post('priority'), $prioList, true) ? post('priority') : 'normal';
    $ev = post('event_date');
    $ev = $ev && strtotime($ev) ? date('Y-m-d', strtotime($ev)) : null;
    if ($title === '' || $content === '') { flash_set('Title and content are required.', 'danger'); redirect($back); }
    $img = upload_image($_FILES['image'] ?? null);
    $status = post('status') === 'archived' ? 'archived' : 'published';
    db()->prepare('INSERT INTO announcements (author_id,author_label,title,content,category,event_date,priority,status,image) VALUES (?,?,?,?,?,?,?,?,?)')
       ->execute([$u['id'], 'Admin Office', $title, $content, $cat, $ev, $prio, $status, $img]);
    if ($status === 'published') {
        foreach (db()->query("SELECT id FROM users WHERE role<>'admin' AND status='active'") as $row) {
            notify((int)$row['id'], 'announcement', 'New Announcement: ' . mb_substr($title, 0, 80), mb_substr(strip_tags($content), 0, 120) . '…', '../user/announcements.php');
        }
    }
    log_activity((int)$u['id'], 'ann_add', $title);
    flash_set('Announcement published.');
    redirect($back);
}

if ($action === 'update') {
    $id = (int)post('id');
    $st = db()->prepare('SELECT * FROM announcements WHERE id=?');
    $st->execute([$id]);
    $a = $st->fetch();
    if (!$a) { flash_set('Announcement not found.', 'danger'); redirect($back); }
    $cat = in_array(post('category'), $catList, true) ? post('category') : 'facilities';
    $prio = in_array(post('priority'), $prioList, true) ? post('priority') : 'normal';
    $ev = post('event_date');
    $ev = $ev && strtotime($ev) ? date('Y-m-d', strtotime($ev)) : null;
    $img = upload_image($_FILES['image'] ?? null, $a['image']);
    db()->prepare('UPDATE announcements SET title=?, content=?, category=?, event_date=?, priority=?, status=?, image=? WHERE id=?')
       ->execute([post('title'), post('content'), $cat, $ev, $prio, post('status', 'published'), $img, $id]);
    log_activity((int)$u['id'], 'ann_update', '#' . $id);
    flash_set('Announcement updated.');
    redirect($back);
}

if ($action === 'toggle_status') {
    $id = (int)post('id');
    db()->prepare("UPDATE announcements SET status = IF(status='published','archived','published') WHERE id=?")->execute([$id]);
    log_activity((int)$u['id'], 'ann_toggle', '#' . $id);
    flash_set('Announcement status changed.');
    redirect($back);
}

if ($action === 'delete') {
    db()->prepare('DELETE FROM announcements WHERE id=?')->execute([(int)post('id')]);
    log_activity((int)$u['id'], 'ann_delete', '#' . (int)post('id'));
    flash_set('Announcement deleted.');
    redirect($back);
}

redirect('../index.php');