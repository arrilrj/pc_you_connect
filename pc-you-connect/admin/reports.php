<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Reports';

 $from = get_str('from', date('Y-m-01'));
 $to   = get_str('to', date('Y-m-d'));
if (!strtotime($from) || !strtotime($to)) { $from = date('Y-m-01'); $to = date('Y-m-d'); }
 $mk = fn(string $col) => "$col >= ? AND $col < DATE_ADD(?, INTERVAL 1 DAY)";
 $rp = [$from, $to];

 $resTotal = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE " . $mk('created_at'), $rp);
 $resAppr  = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='approved' AND " . $mk('created_at'), $rp);
 $resComp  = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='completed' AND " . $mk('created_at'), $rp);
 $lfFound  = (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='found' AND " . $mk('created_at'), $rp);
 $lfLost   = (int)db_scalar("SELECT COUNT(*) FROM lost_found_items WHERE report_type='lost' AND " . $mk('created_at'), $rp);
 $clTotal  = (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE " . $mk('created_at'), $rp);
 $clAppr   = (int)db_scalar("SELECT COUNT(*) FROM claim_requests WHERE status='approved' AND " . $mk('created_at'), $rp);
 $annTotal = (int)db_scalar("SELECT COUNT(*) FROM announcements WHERE " . $mk('created_at'), $rp);
 $annViews = (int)db_scalar("SELECT COALESCE(SUM(views),0) FROM announcements WHERE " . $mk('created_at'), $rp);

 $lfTotal = $lfFound + $lfLost;
 $grand = $resTotal + $lfTotal + $clTotal + $annTotal;
 $seg = $grand ? fn($n) => $n ? round($n / $grand * 100, 1) : 0 : fn($n) => 0;

 $res = db()->prepare("SELECT r.*, f.name facility, CONCAT(u.first_name,' ',u.last_name) requester
    FROM facility_reservations r JOIN facilities f ON f.id=r.facility_id JOIN users u ON u.id=r.user_id
    WHERE " . $mk('r.created_at') . " ORDER BY r.created_at DESC LIMIT 500");
 $res->execute($rp);
 $resRows = $res->fetchAll();

 $lf = db()->prepare("SELECT * FROM lost_found_items WHERE " . $mk('created_at') . " ORDER BY created_at DESC LIMIT 500");
 $lf->execute($rp);
 $lfRows = $lf->fetchAll();

 $cl = db()->prepare("SELECT c.*, i.item_name, CONCAT(u.first_name,' ',u.last_name) claimant
    FROM claim_requests c JOIN lost_found_items i ON i.id=c.item_id JOIN users u ON u.id=c.user_id
    WHERE " . $mk('c.created_at') . " ORDER BY c.created_at DESC LIMIT 500");
 $cl->execute($rp);
 $clRows = $cl->fetchAll();

 $ann = db()->prepare("SELECT * FROM announcements WHERE " . $mk('created_at') . " ORDER BY created_at DESC LIMIT 500");
 $ann->execute($rp);
 $annRows = $ann->fetchAll();

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <div class="page-head">
    <div>
      <h1 class="page-title">Reports</h1>
      <p class="page-sub">Review service transactions and campus activity · <?= fmt_date($from) ?> – <?= fmt_date($to) ?>.</p>
    </div>
  </div>

  <div class="page-toolbar no-print">
    <form method="get" class="d-flex flex-wrap align-items-center gap-2">
      <span class="toolbar-hint fw-bold" style="color:var(--navy-dark)">Date Range</span>
      <div class="date-seg">
        <label>FROM</label>
        <input type="date" name="from" value="<?= e($from) ?>" aria-label="From date">
      </div>
      <div class="date-seg">
        <label>TO</label>
        <input type="date" name="to" value="<?= e($to) ?>" aria-label="To date">
      </div>
      <button class="btn btn-outline-navy"><i class="bi bi-funnel"></i>Apply</button>
    </form>

    <div class="d-flex align-items-center gap-2 ms-auto">
      <div class="dropdown">
        <button class="btn btn-navy dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-download"></i>Export</button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><h6 class="dropdown-header">Download CSV</h6></li>
          <li><a class="dropdown-item" href="export.php?type=reservations&from=<?= e($from) ?>&to=<?= e($to) ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Reservations</a></li>
          <li><a class="dropdown-item" href="export.php?type=lostfound&from=<?= e($from) ?>&to=<?= e($to) ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Lost &amp; Found</a></li>
          <li><a class="dropdown-item" href="export.php?type=claims&from=<?= e($from) ?>&to=<?= e($to) ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Claims</a></li>
          <li><a class="dropdown-item" href="export.php?type=announcements&from=<?= e($from) ?>&to=<?= e($to) ?>"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Announcements</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><button class="dropdown-item" onclick="window.print()"><i class="bi bi-printer me-2"></i>Print Report</button></li>
        </ul>
      </div>
    </div>
  </div>

  <div class="sum-row">
    <div class="sum-card"><span class="stat-icon icon-navy"><i class="bi bi-calendar-check"></i></span>
      <div><div class="sum-num"><?= $resTotal ?></div><div class="sum-label">Reservations</div>
        <div class="muted-sm" style="font-size:11px"><?= $resAppr ?> approved · <?= $resComp ?> completed</div></div></div>
    <div class="sum-card"><span class="stat-icon icon-warning"><i class="bi bi-box-seam"></i></span>
      <div><div class="sum-num"><?= $lfFound ?></div><div class="sum-label">Found Items</div>
        <div class="muted-sm" style="font-size:11px"><?= $lfLost ?> lost reports filed</div></div></div>
    <div class="sum-card"><span class="stat-icon icon-purple"><i class="bi bi-hand-index-thumb"></i></span>
      <div><div class="sum-num"><?= $clTotal ?></div><div class="sum-label">Claim Requests</div>
        <div class="muted-sm" style="font-size:11px"><?= $clAppr ?> approved</div></div></div>
    <div class="sum-card"><span class="stat-icon icon-info"><i class="bi bi-megaphone"></i></span>
      <div><div class="sum-num"><?= $annTotal ?></div><div class="sum-label">Announcements</div>
        <div class="muted-sm" style="font-size:11px"><?= number_format($annViews) ?> total views</div></div></div>
  </div>

  <div class="card activity-card mb-4">
    <h6 class="sec-title mb-3"><i class="bi bi-activity"></i>Campus Activity</h6>
    <?php if (!$grand): ?>
      <div class="empty-state" style="padding:20px"><i class="bi bi-inbox"></i>
        <div class="es-title">No activity in this date range</div>
        <div class="es-sub">Try widening the date range and applying again.</div></div>
    <?php else: ?>
      <div class="act-bar">
        <span style="width:<?= $seg($resTotal) ?>%;background:var(--navy)" title="Reservations"></span>
        <span style="width:<?= $seg($lfTotal) ?>%;background:var(--warning)" title="Lost & Found"></span>
        <span style="width:<?= $seg($clTotal) ?>%;background:var(--purple)" title="Claims"></span>
        <span style="width:<?= $seg($annTotal) ?>%;background:#2B5FA3" title="Announcements"></span>
      </div>
      <div class="act-legend">
        <span><span class="dot" style="background:var(--navy)"></span>Reservations <b><?= $resTotal ?></b></span>
        <span><span class="dot" style="background:var(--warning)"></span>Lost &amp; Found <b><?= $lfTotal ?></b></span>
        <span><span class="dot" style="background:var(--purple)"></span>Claims <b><?= $clTotal ?></b></span>
        <span><span class="dot" style="background:#2B5FA3"></span>Announcements <b><?= $annTotal ?></b></span>
      </div>
    <?php endif; ?>
  </div>

  <div class="manage-card">
    <div class="report-sec">
      <button class="sec-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#secRes" aria-expanded="true">
        <i class="bi bi-calendar-check"></i>Reservation Transactions
        <span class="badge badge-soft soft-primary"><?= count($resRows) ?></span>
        <i class="bi bi-chevron-down chev"></i>
      </button>
      <div class="collapse show" id="secRes">
        <div class="table-wrap"><table class="table">
          <thead><tr><th>DATE FILED</th><th>REQUESTER</th><th>FACILITY</th><th>SCHEDULE</th><th>STATUS</th></tr></thead>
          <tbody><?php if (!$resRows) echo '<tr><td colspan="5" class="text-center text-muted py-3">No records in this date range.</td></tr>';
            foreach ($resRows as $r): ?>
            <tr><td><?= fmt_date($r['created_at']) ?></td><td><?= e($r['requester']) ?></td><td><?= e($r['facility']) ?></td>
              <td><?= fmt_date($r['res_date']) ?> · <?= fmt_time12($r['start_time']) ?>–<?= fmt_time12($r['end_time']) ?></td>
              <td><?= badge($r['status']) ?></td></tr>
          <?php endforeach; ?></tbody>
        </table></div>
      </div>
    </div>

    <div class="report-sec">
      <button class="sec-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#secLF" aria-expanded="true">
        <i class="bi bi-search"></i>Lost &amp; Found Transactions
        <span class="badge badge-soft soft-warning"><?= count($lfRows) ?></span>
        <i class="bi bi-chevron-down chev"></i>
      </button>
      <div class="collapse show" id="secLF">
        <div class="table-wrap"><table class="table">
          <thead><tr><th>REPORTED</th><th>TYPE</th><th>ITEM</th><th>CATEGORY</th><th>LOCATION</th><th>STATUS</th></tr></thead>
          <tbody><?php if (!$lfRows) echo '<tr><td colspan="6" class="text-center text-muted py-3">No records in this date range.</td></tr>';
            foreach ($lfRows as $i): ?>
            <tr><td><?= fmt_date($i['created_at']) ?></td>
              <td><?= badge($i['report_type']) ?></td>
              <td class="fw-semibold"><?= e($i['item_name']) ?></td><td><?= badge($i['category']) ?></td>
              <td><?= e($i['location_found']) ?></td><td><?= badge($i['status']) ?></td></tr>
          <?php endforeach; ?></tbody>
        </table></div>
      </div>
    </div>

    <div class="report-sec">
      <button class="sec-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#secClaims" aria-expanded="false">
        <i class="bi bi-hand-index-thumb"></i>Claim Requests
        <span class="badge badge-soft soft-purple"><?= count($clRows) ?></span>
        <i class="bi bi-chevron-down chev"></i>
      </button>
      <div class="collapse" id="secClaims">
        <div class="table-wrap"><table class="table">
          <thead><tr><th>SUBMITTED</th><th>ITEM</th><th>CLAIMANT</th><th>PROOF</th><th>DECISION</th></tr></thead>
          <tbody><?php if (!$clRows) echo '<tr><td colspan="5" class="text-center text-muted py-3">No records in this date range.</td></tr>';
            foreach ($clRows as $c): ?>
            <tr><td><?= fmt_date($c['created_at']) ?></td><td class="fw-semibold"><?= e($c['item_name']) ?></td>
              <td><?= e($c['claimant']) ?></td>
              <td><?= e(mb_substr($c['proof_details'], 0, 60)) ?></td>
              <td><?= badge($c['status']) ?></td></tr>
          <?php endforeach; ?></tbody>
        </table></div>
      </div>
    </div>

    <div class="report-sec">
      <button class="sec-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#secAnn" aria-expanded="false">
        <i class="bi bi-megaphone"></i>Announcements Posted
        <span class="badge badge-soft soft-info"><?= count($annRows) ?></span>
        <i class="bi bi-chevron-down chev"></i>
      </button>
      <div class="collapse" id="secAnn">
        <div class="table-wrap"><table class="table">
          <thead><tr><th>DATE POSTED</th><th>TITLE</th><th>CATEGORY</th><th>VIEWS</th><th>STATUS</th></tr></thead>
          <tbody><?php if (!$annRows) echo '<tr><td colspan="5" class="text-center text-muted py-3">No records in this date range.</td></tr>';
            foreach ($annRows as $a): ?>
            <tr><td><?= fmt_date($a['created_at']) ?></td><td class="fw-semibold"><?= e($a['title']) ?></td><td><?= badge($a['category']) ?></td>
              <td><?= (int)$a['views'] ?></td><td><?= badge($a['status']) ?></td></tr>
          <?php endforeach; ?></tbody>
        </table></div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>