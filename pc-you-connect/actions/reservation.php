<?php
require_once __DIR__ . '/../includes/admin_auth.php';
 $action = $_REQUEST['action'] ?? '';
 $u = current_user();
 $isAdm = ($u['role'] ?? '') === 'admin';

if ($action === 'slots') {
    $user = require_login();
    header('Content-Type: application/json');
    $fid = (int)($_GET['facility_id'] ?? 0);
    $date = $_GET['date'] ?? '';
    if (!$fid || !strtotime($date)) { echo json_encode(['slots' => []]); exit; }
    $st = db()->prepare("SELECT start_time, end_time, status FROM facility_reservations
                         WHERE facility_id=? AND res_date=? AND status IN ('pending','approved') ORDER BY start_time");
    $st->execute([$fid, $date]);
    $slots = array_map(fn($r) => ['start' => fmt_time12($r['start_time']), 'end' => fmt_time12($r['end_time']), 'status' => $r['status']], $st->fetchAll());
    echo json_encode(['slots' => $slots]);
    exit;
}

if ($action === 'calendar') {
    $user = require_login();
    header('Content-Type: application/json');
    $month = $_GET['month'] ?? date('Y-m');
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) $month = date('Y-m');
    $first = $month . '-01';
    $last = date('Y-m-t', strtotime($first));
    $fid = (int)($_GET['facility_id'] ?? 0);

    $sql = "SELECT res_date, status, COUNT(*) AS cnt FROM facility_reservations
            WHERE res_date BETWEEN ? AND ? AND status IN ('pending','approved')";
    $p = [$first, $last];
    if ($fid) { $sql .= ' AND facility_id=?'; $p[] = $fid; }
    $sql .= ' GROUP BY res_date, status';
    $st = db()->prepare($sql);
    $st->execute($p);

    $days = [];
    foreach ($st->fetchAll() as $r) {
        $d = $r['res_date'];
        if (!isset($days[$d])) $days[$d] = ['approved' => 0, 'pending' => 0];
        $days[$d][$r['status']] = (int)$r['cnt'];
    }
    echo json_encode(['month' => $month, 'days' => $days]);
    exit;
}

if ($action === 'day') {
    $user = require_login();
    header('Content-Type: application/json');
    $date = $_GET['date'] ?? '';
    if (!strtotime($date)) { echo json_encode(['items' => []]); exit; }
    $fid = (int)($_GET['facility_id'] ?? 0);
    $sql = "SELECT r.start_time, r.end_time, r.status, r.purpose, f.name AS facility";
    if ($isAdm) $sql .= ", CONCAT(u.first_name,' ',u.last_name) AS requester";
    $sql .= " FROM facility_reservations r
              JOIN facilities f ON f.id=r.facility_id JOIN users u ON u.id=r.user_id
              WHERE r.res_date=? AND r.status IN ('pending','approved')";
    $p = [$date];
    if ($fid) { $sql .= ' AND r.facility_id=?'; $p[] = $fid; }
    $sql .= ' ORDER BY r.start_time';
    $st = db()->prepare($sql);
    $st->execute($p);
    $items = [];
    foreach ($st->fetchAll() as $r) {
        $items[] = [
            'facility' => $r['facility'],
            'purpose' => $r['purpose'],
            'start' => fmt_time12($r['start_time']),
            'end' => fmt_time12($r['end_time']),
            'status' => $r['status'],
        ];
        if ($isAdm) $items[count($items) - 1]['requester'] = $r['requester'];
    }
    echo json_encode(['date' => $date, 'items' => $items]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('../index.php');

if ($action === 'create') {
    $user = require_login();
    $facilityId = (int)post('facility_id');
    $purpose = post('purpose');
    $date = post('res_date');
    $start = post('start_time');
    $end = post('end_time');
    $notes = post('notes');
    $back = '../user/reserve.php' . ($facilityId ? '?facility_id=' . $facilityId : '');

    $f = db()->prepare('SELECT * FROM facilities WHERE id=?');
    $f->execute([$facilityId]);
    $facility = $f->fetch();
    if (!$facility || $facility['status'] !== 'available') {
        flash_set('Selected facility is not available for reservation.', 'danger');
        redirect('../user/facilities.php');
    }
    if ($purpose === '' || !$date || !$start || !$end) { flash_set('Please complete all required fields.', 'danger'); redirect($back); }
    if (strtotime("$date $end") <= strtotime("$date $start")) { flash_set('End time must be after start time.', 'danger'); redirect($back); }
    if (strtotime($date) < strtotime(date('Y-m-d'))) { flash_set('Reservation date cannot be in the past.', 'danger'); redirect($back); }
    if (reservation_conflict($facilityId, $date, $start, $end)) {
        flash_set('That time slot conflicts with an existing reservation. Please choose another schedule.', 'danger');
        redirect($back);
    }

    $atts = upload_files($_FILES['attachments'] ?? null);
    db()->prepare('INSERT INTO facility_reservations (user_id,facility_id,purpose,res_date,start_time,end_time,notes,attachments) VALUES (?,?,?,?,?,?,?,?)')
       ->execute([$user['id'], $facilityId, $purpose, $date, $start, $end, $notes !== '' ? $notes : null, $atts ? json_encode($atts) : null]);
    $rid = (int)db()->lastInsertId();
    db()->prepare('INSERT INTO reservation_status_history (reservation_id,status,remarks,changed_by) VALUES (?,?,?,?)')
       ->execute([$rid, 'pending', 'Reservation submitted' . ($atts ? ' with ' . count($atts) . ' attachment(s)' : ''), $user['id']]);
    notify_admins('reservation', 'New Reservation Request',
        $user['name'] . ' requested ' . $facility['name'] . ' on ' . fmt_date($date) . ' (' . fmt_time12($start) . '–' . fmt_time12($end) . ').', '../admin/reservations.php');
    log_activity((int)$user['id'], 'reservation_create', 'Requested ' . $facility['name']);
    flash_set('Reservation submitted! You will be notified once it is reviewed.');
    redirect('../user/reservations.php');
}

if ($action === 'decide') {
    require_admin();
    $rid = (int)post('reservation_id');
    $decision = post('decision') === 'approve' ? 'approved' : 'rejected';
    $remarks = post('remarks');
    $st = db()->prepare('SELECT r.*, f.name AS facility FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id WHERE r.id=?');
    $st->execute([$rid]);
    $r = $st->fetch();
    if (!$r) { flash_set('Reservation not found.', 'danger'); redirect('../admin/reservations.php'); }
    if ($decision === 'approved' && reservation_conflict((int)$r['facility_id'], $r['res_date'], $r['start_time'], $r['end_time'], $rid)) {
        flash_set('Cannot approve: the time slot now conflicts with another reservation.', 'danger');
        redirect('../admin/reservations.php');
    }
    db()->prepare('UPDATE facility_reservations SET status=?, remarks=?, decided_by=?, decided_at=NOW() WHERE id=?')
       ->execute([$decision, $remarks !== '' ? $remarks : null, $u['id'], $rid]);
    db()->prepare('INSERT INTO reservation_status_history (reservation_id,status,remarks,changed_by) VALUES (?,?,?,?)')
       ->execute([$rid, $decision, $remarks !== '' ? $remarks : 'Reservation ' . $decision, $u['id']]);
    notify((int)$r['user_id'], 'reservation', 'Reservation ' . ucfirst($decision),
        'Your reservation for ' . $r['facility'] . ' on ' . fmt_date($r['res_date']) . ' was ' . $decision .
        ($remarks !== '' ? '. Remarks: ' . $remarks : '.') . '.', '../user/reservations.php');
    log_activity((int)$u['id'], 'reservation_' . $decision, 'Reservation #' . $rid);
    flash_set('Reservation ' . $decision . '.');
    redirect('../admin/reservations.php');
}

if ($action === 'complete') {
    require_admin();
    $rid = (int)post('reservation_id');
    $st = db()->prepare('SELECT r.*, f.name AS facility FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id WHERE r.id=? AND r.status=?');
    $st->execute([$rid, 'approved']);
    $r = $st->fetch();
    if ($r) {
        db()->prepare('UPDATE facility_reservations SET status=? WHERE id=?')->execute(['completed', $rid]);
        db()->prepare('INSERT INTO reservation_status_history (reservation_id,status,remarks,changed_by) VALUES (?,?,?,?)')
           ->execute([$rid, 'completed', 'Marked as completed by admin', $u['id']]);
        notify((int)$r['user_id'], 'reservation', 'Reservation Completed',
            'Your reservation for ' . $r['facility'] . ' on ' . fmt_date($r['res_date']) . ' is marked completed.', '../user/reservations.php');
        log_activity((int)$u['id'], 'reservation_complete', '#' . $rid);
        flash_set('Reservation marked as completed.');
    }
    redirect('../admin/reservations.php');
}

if ($action === 'cancel') {
    $user = require_login();
    $rid = (int)post('reservation_id');
    $st = db()->prepare('SELECT r.*, f.name AS facility FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id WHERE r.id=?' . ($isAdm ? '' : ' AND user_id=?'));
    $st->execute($isAdm ? [$rid] : [$rid, $user['id']]);
    $r = $st->fetch();
    if ($r && in_array($r['status'], ['pending', 'approved'])) {
        db()->prepare('UPDATE facility_reservations SET status=? WHERE id=?')->execute(['cancelled', $rid]);
        db()->prepare('INSERT INTO reservation_status_history (reservation_id,status,remarks,changed_by) VALUES (?,?,?,?)')
           ->execute([$rid, 'cancelled', 'Cancelled by ' . ($isAdm ? 'admin' : 'user'), $user['id']]);
        if (!$isAdm) notify_admins('reservation', 'Reservation Cancelled', $user['name'] . ' cancelled a reservation for ' . $r['facility'] . ' on ' . fmt_date($r['res_date']) . '.');
        log_activity((int)$user['id'], 'reservation_cancel', '#' . $rid);
        flash_set('Reservation cancelled.');
    }
    redirect($isAdm ? '../admin/reservations.php' : '../user/reservations.php');
}

redirect('../index.php');