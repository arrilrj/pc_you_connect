<?php
require_once __DIR__ . '/../includes/admin_auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');
 $action = $_POST['action'] ?? '';
 $u = current_user();
 $isAdm = ($u['role'] ?? '') === 'admin';
 $back = $isAdm ? '../admin/lost-found.php' : '../user/lost-found.php';

/* ---------- Add item (found report or lost report) ---------- */
if ($action === 'add') {
    $user = require_login();
    $name = post('item_name'); $loc = post('location_found'); $date = post('date_found');
    $type = post('report_type') === 'lost' ? 'lost' : 'found';
    $isAdmin = ($user['role'] ?? '') === 'admin';
    if ($name === '' || $loc === '' || !$date) { flash_set('Item name, location, and date are required.', 'danger'); redirect($isAdmin ? $back : '../user/add-lost-found.php?type=' . $type); }

    $img = upload_image($_FILES['image'] ?? null);
    $status = $type === 'lost' ? 'searching' : ($isAdmin ? (post('status', 'unclaimed') ?: 'unclaimed') : 'unclaimed');
    db()->prepare('INSERT INTO lost_found_items (reporter_id,report_type,item_name,category,description,location_found,date_found,status,image) VALUES (?,?,?,?,?,?,?,?,?)')
       ->execute([$user['id'], $type, $name, post('category', 'other'), post('description') ?: null, $loc, $date, $status, $img]);
    notify_admins('lost_found',
        $type === 'lost' ? 'New Lost Report' : 'New Found Item',
        $user['name'] . ' reported ' . ($type === 'lost' ? 'LOSING' : 'FINDING') . ' "' . $name . '" (' . $loc . ').', '../admin/lost-found.php');
    log_activity((int)$user['id'], 'lf_add', ucfirst($type) . ': ' . $name);
    flash_set($type === 'lost' ? 'Lost report filed. We will notify you if a matching item is found.' : 'Item reported successfully.');
    redirect($isAdmin ? $back : '../user/lost-found.php');
}

/* ---------- Update (admin) ---------- */
if ($action === 'update') {
    require_admin();
    $id = (int)post('id');
    $st = db()->prepare('SELECT * FROM lost_found_items WHERE id=?'); $st->execute([$id]); $item = $st->fetch();
    if (!$item) { flash_set('Item not found.', 'danger'); redirect($back); }
    $img = upload_image($_FILES['image'] ?? null, $item['image']);
    $newStatus = post('status', 'unclaimed');
    db()->prepare('UPDATE lost_found_items SET item_name=?, category=?, description=?, location_found=?, date_found=?, status=?, claimed_by=?, image=? WHERE id=?')
       ->execute([post('item_name'), post('category', 'other'), post('description') ?: null, post('location_found'),
                  post('date_found'), $newStatus, post('claimed_by') ?: null, $img, $id]);
    if ($newStatus !== $item['status'] && $item['reporter_id']) {
        notify((int)$item['reporter_id'], 'lost_found', 'Item Update',
            '"' . $item['item_name'] . '" status changed to ' . strtoupper(str_replace('_', ' ', $newStatus)) . '.', $isAdm ? $back : '../user/lost-found.php');
    }
    log_activity((int)$u['id'], 'lf_update', '#' . $id . ' ' . post('item_name'));
    flash_set('Item updated successfully.');
    redirect($back);
}

/* ---------- Delete (admin) ---------- */
if ($action === 'delete') {
    require_admin();
    try {
        db()->prepare('DELETE FROM lost_found_items WHERE id=?')->execute([(int)post('id')]);
        log_activity((int)$u['id'], 'lf_delete', '#' . (int)post('id'));
        flash_set('Item deleted.');
    } catch (PDOException $ex) {
        flash_set('Could not delete this item.', 'danger');
    }
    redirect($back);
}

/* ---------- Delete own lost report (user) ---------- */
if ($action === 'delete_own') {
    $user = require_login();
    db()->prepare('DELETE FROM lost_found_items WHERE id=? AND reporter_id=? AND report_type=?')
       ->execute([(int)post('id'), $user['id'], 'lost']);
    flash_set('Lost report removed.');
    redirect('../user/lost-found.php?view=lost');
}

/* ---------- Student submits a claim request ---------- */
if ($action === 'claim') {
    $user = require_login();
    $itemId = (int)post('item_id');
    $proof = post('proof_details');
    $contact = post('claimant_contact');
    $back2 = '../user/lost-found.php?view=found';
    $st = db()->prepare("SELECT * FROM lost_found_items WHERE id=? AND report_type='found' AND status='unclaimed'");
    $st->execute([$itemId]); $item = $st->fetch();
    if (!$item) { flash_set('This item is no longer available for claims.', 'danger'); redirect($back2); }
    if ($proof === '') { flash_set('Please describe proof of ownership.', 'danger'); redirect($back2); }
    if (db_scalar('SELECT COUNT(*) FROM claim_requests WHERE item_id=? AND user_id=? AND status=?', [$itemId, $user['id'], 'pending'])) {
        flash_set('You already have a pending claim for this item.', 'warning'); redirect($back2);
    }
        $atts = upload_files($_FILES['attachments'] ?? null, 2);
    db()->prepare('INSERT INTO claim_requests (item_id,user_id,proof_details,claimant_contact,attachments) VALUES (?,?,?,?,?)')
       ->execute([$itemId, $user['id'], $proof, $contact ?: null, $atts ? json_encode($atts) : null]);
    notify_admins('lost_found', 'New Claim Request',
        $user['name'] . ' submitted a claim for "' . $item['item_name'] . '". Review it in Claim Requests.', '../admin/claims.php');
    log_activity((int)$user['id'], 'lf_claim', 'Claim for #' . $itemId);
    flash_set('Claim submitted! The admin office will verify your claim and notify you.');
    redirect('../user/lost-found.php?view=claims');
}

/* ---------- Admin approves / rejects a claim ---------- */
if ($action === 'claim_decide') {
    require_admin();
    $cid = (int)post('claim_id');
    $decision = post('decision') === 'approve' ? 'approved' : 'rejected';
    $remarks = post('remarks');
    $st = db()->prepare('SELECT c.*, i.item_name, i.id AS item_id, CONCAT(u.first_name," ",u.last_name) claimant
                         FROM claim_requests c JOIN lost_found_items i ON i.id=c.item_id JOIN users u ON u.id=c.user_id WHERE c.id=?');
    $st->execute([$cid]); $c = $st->fetch();
    if (!$c) { flash_set('Claim not found.', 'danger'); redirect('../admin/claims.php'); }
    if ($c['status'] !== 'pending') { flash_set('This claim was already decided.', 'warning'); redirect('../admin/claims.php'); }

    db()->prepare('UPDATE claim_requests SET status=?, admin_remarks=?, decided_by=?, decided_at=NOW() WHERE id=?')
       ->execute([$decision, $remarks !== '' ? $remarks : null, $u['id'], $cid]);

    if ($decision === 'approved') {
        db()->prepare('UPDATE lost_found_items SET status=?, claimed_by=? WHERE id=?')
           ->execute(['claimed', $c['claimant'], (int)$c['item_id']]);
        // auto-reject other pending claims for the same item
        db()->prepare("UPDATE claim_requests SET status=?, admin_remarks=?, decided_by=?, decided_at=NOW() WHERE item_id=? AND status=? AND id<>?")
           ->execute(['rejected', 'Item has already been claimed.', $u['id'], (int)$c['item_id'], 'pending', $cid]);
        notify((int)$c['user_id'], 'lost_found', 'Claim Approved',
            'Your claim for "' . $c['item_name'] . '" was approved. Please claim the item at the admin office.' .
            ($remarks !== '' ? ' Remarks: ' . $remarks : ''), '../user/lost-found.php?view=claims');
    } else {
        notify((int)$c['user_id'], 'lost_found', 'Claim Rejected',
            'Your claim for "' . $c['item_name'] . '" was not approved.' .
            ($remarks !== '' ? ' Remarks: ' . $remarks : ''), '../user/lost-found.php?view=claims');
    }
    log_activity((int)$u['id'], 'lf_claim_' . $decision, 'Claim #' . $cid);
    flash_set('Claim ' . $decision . '.');
    redirect('../admin/claims.php');
}

redirect('../index.php');