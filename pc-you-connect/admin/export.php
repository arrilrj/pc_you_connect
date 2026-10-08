<?php
require_once '../includes/admin_auth.php';
require_admin();

 $type = $_GET['type'] ?? '';
 $from = $_GET['from'] ?? date('Y-m-01');
 $to   = $_GET['to'] ?? date('Y-m-d');
if (!strtotime($from) || !strtotime($to)) redirect('reports.php');
 $mk = fn(string $col) => "$col >= ? AND $col < DATE_ADD(?, INTERVAL 1 DAY)";

 $defs = [
    'reservations' => ['reservation_report',
        'SELECT r.id, r.purpose, f.name AS facility, CONCAT(u.first_name," ",u.last_name) AS requester, r.res_date, r.start_time, r.end_time, r.status, r.created_at
         FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id JOIN users u ON u.id=r.user_id WHERE ' . $mk('r.created_at') . ' ORDER BY r.created_at DESC',
        ['ID','Purpose','Facility','Requester','Date','Start','End','Status','Created At']],
    'lostfound' => ['lost_found_report',
        'SELECT id, report_type, item_name, category, location_found, date_found, status, claimed_by, created_at
         FROM lost_found_items WHERE ' . $mk('created_at') . ' ORDER BY created_at DESC',
        ['ID','Type','Item','Category','Location Found','Date','Status','Claimed By','Reported At']],
    'claims' => ['claims_report',
        'SELECT c.id, i.item_name, CONCAT(u.first_name," ",u.last_name) AS claimant, c.claimant_contact, c.proof_details, c.status, c.admin_remarks, c.created_at
         FROM claim_requests c JOIN lost_found_items i ON i.id=c.item_id JOIN users u ON u.id=c.user_id WHERE ' . $mk('c.created_at') . ' ORDER BY c.created_at DESC',
        ['ID','Item','Claimant','Contact','Proof','Status','Admin Remarks','Submitted At']],
    'announcements' => ['announcements_report',
        'SELECT id, title, category, status, views, created_at
         FROM announcements WHERE ' . $mk('created_at') . ' ORDER BY created_at DESC',
        ['ID','Title','Category','Status','Views','Created At']],
];
if (!isset($defs[$type])) redirect('reports.php');
[$filename, $sql, $headers] = $defs[$type];

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}_{$from}_to_{$to}.csv\"");
 $out = fopen('php://output', 'w');
fputcsv($out, $headers);
 $st = db()->prepare($sql);
 $st->execute([$from, $to]);
while ($row = $st->fetch(PDO::FETCH_ASSOC)) fputcsv($out, $row);
fclose($out);
exit;