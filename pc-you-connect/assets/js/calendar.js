function initBookingCalendar(rootId, opts = {}) {
  const root = document.getElementById(rootId);
  if (!root) return null;
  const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const WD = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  let current = new Date();
  current.setDate(1);
  let facilityId = opts.facilityId || 0;
  let monthData = {};
  let selectedKey = '';

  const keyOf = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

  root.innerHTML = `
    <div class="cal-head">
      <button type="button" class="cal-nav" data-dir="-1" aria-label="Previous month"><i class="bi bi-chevron-left"></i></button>
      <span class="cal-title"></span>
      <button type="button" class="cal-nav" data-dir="1" aria-label="Next month"><i class="bi bi-chevron-right"></i></button>
    </div>
    <div class="cal-grid cal-weekdays">${WD.map(w => `<span>${w}</span>`).join('')}</div>
    <div class="cal-grid cal-days"></div>
    <div class="cal-legend">
      <span><span class="cal-dot busy"></span>Booked</span>
      <span><span class="cal-dot pending"></span>Pending</span>
      <span><span class="cal-dot free"></span>Available</span>
    </div>`;

  const titleEl = root.querySelector('.cal-title');
  const daysEl = root.querySelector('.cal-days');

  async function load() {
    const ym = `${current.getFullYear()}-${String(current.getMonth()+1).padStart(2,'0')}`;
    titleEl.textContent = `${MONTHS[current.getMonth()]} ${current.getFullYear()}`;
    daysEl.innerHTML = '<div class="cal-loading"><span class="spinner-border spinner-border-sm"></span></div>';
    try {
      const res = await fetch(`${window.BASE_URL}/actions/reservation.php?action=calendar&month=${ym}&facility_id=${facilityId}`);
      monthData = (await res.json()).days || {};
    } catch (err) { monthData = {}; }
    render();
  }

  function render() {
    daysEl.innerHTML = '';
    const start = new Date(current.getFullYear(), current.getMonth(), 1).getDay();
    const total = new Date(current.getFullYear(), current.getMonth() + 1, 0).getDate();
    const today = new Date(); today.setHours(0,0,0,0);
    for (let i = 0; i < start; i++) daysEl.appendChild(document.createElement('span'));
    for (let d = 1; d <= total; d++) {
      const dt = new Date(current.getFullYear(), current.getMonth(), d);
      const key = keyOf(dt);
      const info = monthData[key] || {};
      const el = document.createElement('button');
      el.type = 'button';
      el.dataset.key = key;
      let cls = 'cal-day free';
      if (info.approved) cls = 'cal-day busy';
      else if (info.pending) cls = 'cal-day pending';
      if (dt < today) cls += ' past';
      if (dt.getTime() === today.getTime()) cls += ' today';
      if (key === selectedKey) cls += ' selected';
      el.className = cls;
      const cnt = (info.approved || 0) + (info.pending || 0);
      el.innerHTML = `<span class="d">${d}</span>` + (cnt ? `<span class="cal-count">${cnt}</span>` : '');
      el.title = info.approved ? `${info.approved} booked` : (info.pending ? `${info.pending} pending` : 'Available');
      el.addEventListener('click', () => {
        selectDay(key);
        if (opts.onSelect) opts.onSelect(key);
      });
      daysEl.appendChild(el);
    }
  }

  async function loadDay(date) {
    const box = document.getElementById(opts.dayBox);
    if (!box) return;
    box.innerHTML = '<div class="muted-sm"><span class="spinner-border spinner-border-sm me-1"></span>Loading…</div>';
    try {
      const res = await fetch(`${window.BASE_URL}/actions/reservation.php?action=day&date=${date}&facility_id=${facilityId}`);
      const data = await res.json();
      const items = data.items || [];
      const label = new Date(date + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
      if (!items.length) {
        box.innerHTML = `<div class="fw-bold mb-2" style="font-size:13.5px">${label}</div>
          <div class="empty-sm" style="padding:14px"><i class="bi bi-check-circle"></i>No bookings — fully available.</div>`;
        return;
      }
      box.innerHTML = `<div class="fw-bold mb-2" style="font-size:13.5px">${label} · ${items.length} booking${items.length > 1 ? 's' : ''}</div>` +
        items.map(i => `
          <div class="d-flex align-items-center gap-2 row-sep">
            <span class="item-thumb sm"><i class="bi bi-door-open"></i></span>
            <div class="flex-grow-1">
              <div class="fw-semibold" style="font-size:13px">${i.facility}</div>
              <div class="muted-sm">${i.start} – ${i.end} · ${i.purpose}</div>
              ${i.requester ? `<div class="muted-sm" style="font-size:11px"><i class="bi bi-person me-1"></i>${i.requester}</div>` : ''}
            </div>
            <span class="badge badge-soft ${i.status === 'approved' ? 'soft-success' : 'soft-warning'}">${i.status.toUpperCase()}</span>
          </div>`).join('');
    } catch (err) {
      box.innerHTML = '<div class="muted-sm">Could not load bookings.</div>';
    }
  }

  function selectDay(key) {
    selectedKey = key;
    root.querySelectorAll('.cal-day').forEach(el => el.classList.toggle('selected', el.dataset.key === key));
    loadDay(key);
  }

  root.querySelectorAll('.cal-nav').forEach(b => b.addEventListener('click', () => {
    current.setMonth(current.getMonth() + parseInt(b.dataset.dir, 10));
    load();
  }));

  if (opts.facilitySelect) {
    const sel = document.getElementById(opts.facilitySelect);
    if (sel) sel.addEventListener('change', () => {
      facilityId = parseInt(sel.value, 10) || 0;
      load();
    });
  }

  load().then(() => selectDay(keyOf(new Date())));

  return { reload: load, selectDay };
}