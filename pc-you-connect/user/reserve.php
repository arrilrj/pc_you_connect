<?php
require_once '../includes/auth.php';
 $u = require_login();
 $page_title = 'Reserve a Facility';
 $facilities = db()->query("SELECT * FROM facilities WHERE status='available' ORDER BY name")->fetchAll();
 $sel = (int)($_GET['facility_id'] ?? 0);

require_once '../includes/sidebar.php';
require_once '../includes/header.php';
?>
<div class="page-head">
  <div><h1 class="page-title">Reserve a Facility</h1><p class="page-sub">Check the calendar, pick a free date, and submit for approval.</p></div>
  <a class="btn btn-outline-navy" href="reservations.php"><i class="bi bi-clock-history"></i>My Reservations</a>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card p-4">
      <form method="post" action="../actions/reservation.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create">

        <label class="form-label">Facility <span class="text-danger">*</span></label>
        <select class="form-select mb-3" name="facility_id" id="facSelect" required>
          <option value="">Select facility…</option>
          <?php foreach ($facilities as $f): ?>
            <option value="<?= $f['id'] ?>" data-loc="<?= e($f['location']) ?>" <?= $sel === (int)$f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label">Purpose / Title <span class="text-danger">*</span></label>
        <input type="text" class="form-control mb-3" name="purpose" placeholder="e.g., Student Council General Assembly" required>

        <label class="form-label">Pick a date on the calendar <span class="text-danger">*</span></label>
        <div class="cal-card mb-3" style="padding:14px">
          <div id="reserveCal"></div>
        </div>
        <input type="date" class="form-control mb-3" name="res_date" id="resDate" min="<?= date('Y-m-d') ?>" required aria-label="Or type a date">

        <div class="row g-3 mb-3">
          <div class="col-md-6"><label class="form-label">Start Time <span class="text-danger">*</span></label>
            <input type="time" class="form-control" name="start_time" id="startTime" required></div>
          <div class="col-md-6"><label class="form-label">End Time <span class="text-danger">*</span></label>
            <input type="time" class="form-control" name="end_time" id="endTime" required></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Booked slots on selected date</label>
          <div id="slotBox"><div class="muted-sm">Select a facility and date to check availability.</div></div>
        </div>

        <label class="form-label">Notes / Additional Details</label>
        <textarea class="form-control mb-1" name="notes" rows="3" placeholder="Expected attendees, equipment needed, adviser, etc. (optional)"></textarea>
        <div class="form-text mb-3">Displayed to the admin reviewer only.</div>

        <label class="form-label">Documentation / Attachments</label>
        <input type="file" class="form-control" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx">
        <div class="form-text mb-4"><i class="bi bi-info-circle me-1"></i>Optional — up to 3 files (images, PDF, DOC). 2MB each.</div>

        <div class="d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-navy px-4"><i class="bi bi-send"></i>Submit Reservation</button>
          <a class="btn btn-ghost px-4" href="facilities.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="d-flex flex-column gap-3 sticky-col">
      <div class="card p-4">
        <h6 class="card-title mb-3">Selected Facility</h6>
        <div class="d-flex align-items-center gap-3">
          <span class="stat-icon icon-navy" style="width:52px;height:52px;font-size:24px"><i class="bi bi-door-open"></i></span>
          <div>
            <div class="fw-bold" id="pvName">—</div>
            <div class="muted-sm" id="pvLoc">Select a facility to preview</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="sec-head"><h6 class="sec-title"><i class="bi bi-calendar3"></i>That Day's Bookings</h6></div>
        <div class="list-body" id="calDayBox">
          <div class="empty-sm"><i class="bi bi-calendar3"></i>Click a date to see its bookings.</div>
        </div>
      </div>

      <div class="card p-4">
        <h6 class="card-title mb-2">How it works</h6>
        <ol class="muted-sm ps-3 mb-0" style="line-height:2">
          <li>Pick a free (green) date on the calendar.</li>
          <li>Set times outside the booked slots shown.</li>
          <li>Attach permits or letters if required.</li>
          <li>Admin approves — you get a notification.</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<script>
const facSel = document.getElementById('facSelect');
const dateIn = document.getElementById('resDate');

function updatePreview() {
  const opt = facSel.options[facSel.selectedIndex];
  if (!opt || !opt.value) {
    pvName.textContent = '—';
    pvLoc.textContent = 'Select a facility to preview';
    return;
  }
  pvName.textContent = opt.textContent;
  pvLoc.textContent = opt.dataset.loc || '';
}

async function loadSlots() {
  const box = document.getElementById('slotBox');
  if (!facSel.value || !dateIn.value) {
    box.innerHTML = '<div class="muted-sm">Select a facility and date to check availability.</div>';
    return;
  }
  box.innerHTML = '<div class="muted-sm"><span class="spinner-border spinner-border-sm me-1"></span>Checking availability…</div>';
  try {
    const res = await fetch('../actions/reservation.php?action=slots&facility_id=' + facSel.value + '&date=' + dateIn.value);
    const data = await res.json();
    if (!data.slots.length) {
      box.innerHTML = '<span class="slot-free"><i class="bi bi-check-circle-fill"></i>All time slots are free on this date</span>';
    } else {
      box.innerHTML = data.slots.map(s =>
        '<span class="slot-chip ' + (s.status === 'approved' ? 'approved' : 'pending') + '"><i class="bi bi-clock-fill"></i>' +
        s.start + ' – ' + s.end + ' · ' + s.status + '</span>').join('') +
        '<div class="muted-sm mt-1">Pick a time outside the slots above to avoid conflicts.</div>';
    }
  } catch (err) {
    box.innerHTML = '<div class="muted-sm">Could not load availability.</div>';
  }
}

facSel.addEventListener('change', () => { updatePreview(); loadSlots(); });
dateIn.addEventListener('change', () => {
  const d = new Date(dateIn.value + 'T00:00:00');
  if (!isNaN(d)) calWidget.selectDay(dateIn.value);
  loadSlots();
});
document.getElementById('startTime').addEventListener('change', () => {
  document.getElementById('endTime').min = document.getElementById('startTime').value;
});

const calWidget = initBookingCalendar('reserveCal', {
  facilitySelect: 'facSelect',
  dayBox: 'calDayBox',
  onSelect: (date) => { dateIn.value = date; loadSlots(); }
});
updatePreview();
</script>

<?php require_once '../includes/footer.php'; ?>