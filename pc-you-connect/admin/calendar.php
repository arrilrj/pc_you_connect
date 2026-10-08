<?php
require_once '../includes/admin_auth.php';
 $u = require_admin();
 $page_title = 'Schedule';

 $facilities = db()->query('SELECT id, name FROM facilities ORDER BY name')->fetchAll();
 $todayCount = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='approved' AND res_date=CURDATE()");
 $upcomingCount = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='approved' AND res_date>CURDATE()");
 $pendCount = (int)db_scalar("SELECT COUNT(*) FROM facility_reservations WHERE status='pending'");

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="dash-wrap">
  <div class="page-head">
    <div>
      <h1 class="page-title">Schedule</h1>
      <p class="page-sub">See which dates are booked or free across campus facilities.</p>
    </div>
    <a class="btn btn-ghost" href="reservations.php"><i class="bi bi-list-task"></i>Reservation List</a>
  </div>

  <div class="mini-stats mb-3" style="padding:0">
    <span class="mini-stat"><b><?= $todayCount ?></b> booked today</span>
    <span class="mini-stat"><b><?= $upcomingCount ?></b> upcoming</span>
    <span class="mini-stat"><b><?= $pendCount ?></b> pending approval</span>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="cal-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <h6 class="sec-title mb-0"><i class="bi bi-calendar3"></i>Booking Calendar</h6>
          <select class="form-select" id="facFilter" style="max-width:260px">
            <option value="0">All facilities</option>
            <?php foreach ($facilities as $f): ?>
              <option value="<?= (int)$f['id'] ?>"><?= e($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="adminCal"></div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card h-100">
        <div class="sec-head">
          <h6 class="sec-title"><i class="bi bi-clock-history"></i>Day Details</h6>
          <a class="btn btn-ghost btn-sm" href="reservations.php?status=pending">Review Pending</a>
        </div>
        <div class="list-body" id="dayBox">
          <div class="empty-sm"><i class="bi bi-calendar3"></i>Pick a date on the calendar to see its bookings.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  initBookingCalendar('adminCal', { facilitySelect: 'facFilter', dayBox: 'dayBox' });
});
</script>

<?php require_once '../includes/footer.php'; ?>