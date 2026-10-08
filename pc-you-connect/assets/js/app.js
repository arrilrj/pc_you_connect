document.addEventListener('DOMContentLoaded', () => {
  // ---- Sidebar offcanvas (mobile) ----
  const toggle = document.getElementById('sidebarToggle');
  const backdrop = document.getElementById('sidebarBackdrop');
  toggle && toggle.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
  backdrop && backdrop.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
  document.querySelectorAll('.app-sidebar a').forEach(a => a.addEventListener('click', () => {
    if (window.innerWidth < 992) document.body.classList.remove('sidebar-open');
  }));

  // ---- "Read more" expand + real view counter ----
  document.querySelectorAll('[data-readmore]').forEach(btn => btn.addEventListener('click', async () => {
    const target = document.querySelector(btn.getAttribute('data-readmore'));
    if (target) {
      const open = target.classList.toggle('expanded');
      btn.textContent = open ? 'Show less' : 'Read more';
    }
    if (btn.dataset.counted !== '1') {
      btn.dataset.counted = '1';
      const id = btn.getAttribute('data-id');
      try {
        await fetch(window.BASE_URL + '/actions/announcements.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'action=increment_views&id=' + encodeURIComponent(id)
        });
        const c = document.querySelector('[data-views="' + id + '"]');
        if (c) c.textContent = (parseInt(c.textContent || '0', 10) + 1);
      } catch (e) { /* offline-safe */ }
    }
  }));

  // ---- Delete confirmations ----
  document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
    if (!confirm(f.getAttribute('data-confirm') || 'Are you sure?')) e.preventDefault();
  }));

  // ---- Auto-dismiss flash alerts after 5s ----
  document.querySelectorAll('.alert-dismissible').forEach(a => {
    setTimeout(() => { try { bootstrap.Alert.getOrCreateInstance(a).close(); } catch (e) {} }, 5000);
  });
});

// ---- Password eye toggle (delegated — always works) ----
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-eye]');
  if (!btn) return;
  const input = document.querySelector(btn.getAttribute('data-eye'));
  if (!input) return;
  const show = input.type === 'password';
  input.type = show ? 'text' : 'password';
  const ic = btn.querySelector('i');
  if (ic) ic.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});