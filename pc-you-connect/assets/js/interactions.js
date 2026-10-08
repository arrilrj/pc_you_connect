(function () {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  function toastWrap() {
    let w = $('.pc-toast-wrap');
    if (!w) { w = document.createElement('div'); w.className = 'pc-toast-wrap'; document.body.appendChild(w); }
    return w;
  }
  function toast(msg, type = 'success', title) {
    const t = document.createElement('div');
    t.className = 'pc-toast ' + type;
    const ic = { success: 'bi-check-circle-fill', danger: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' }[type] || 'bi-bell-fill';
    t.innerHTML = `<i class="bi ${ic}"></i><div class="flex-grow-1">${title ? `<div class="pc-toast-title">${title}</div>` : ''}<div>${msg}</div></div><button class="pc-toast-x" aria-label="Close"><i class="bi bi-x-lg"></i></button>`;
    toastWrap().appendChild(t);
    requestAnimationFrame(() => t.classList.add('show'));
    const kill = () => { t.classList.remove('show'); setTimeout(() => t.remove(), 250); };
    t.querySelector('.pc-toast-x').addEventListener('click', kill);
    setTimeout(kill, 4200);
  }
  window.pcToast = toast;

  function pcHardClose(m) {
    if (!m) return;
    try { (bootstrap.Modal.getInstance(m) || bootstrap.Modal.getOrCreateInstance(m)).hide(); } catch (e) {}
    setTimeout(() => {
      if (m.classList.contains('show')) {
        m.classList.remove('show');
        m.style.display = 'none';
        m.setAttribute('aria-hidden', 'true');
        m.removeAttribute('aria-modal');
        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
      }
    }, 150);
  }
  window.pcCloseModal = sel => pcHardClose(document.querySelector(sel));

  let cf = null;
  function confirmModal(o) {
    return new Promise(res => {
      if (typeof bootstrap === 'undefined') {
        res({ ok: confirm(o.title + '\n\n' + (o.message || '')), reason: '' });
        return;
      }
      if (!cf) {
        cf = document.createElement('div');
        cf.className = 'modal fade pc-confirm';
        cf.tabIndex = -1;
        cf.innerHTML = `<div class="modal-dialog modal-dialog-centered pc-confirm-dialog"><div class="modal-content">
          <div class="pc-confirm-body">
            <div class="pc-confirm-icon"></div>
            <h5 class="pc-confirm-title"></h5>
            <p class="pc-confirm-msg"></p>
            <div class="pc-confirm-details d-none"></div>
            <div class="pc-confirm-reason d-none"><label class="form-label">Reason <span class="text-danger">*</span></label>
              <textarea class="form-control" rows="2" placeholder="Enter reason..."></textarea>
              <div class="pc-reason-err" style="display:none">A reason is required.</div></div>
          </div>
          <div class="modal-footer"><button class="btn btn-ghost" data-act="cancel"></button><button class="btn" data-act="ok"></button></div>
        </div></div>`;
        document.body.appendChild(cf);
      }
      const el = cf;
      const ic = el.querySelector('.pc-confirm-icon');
      ic.className = 'pc-confirm-icon ' + (o.variant || 'primary');
      ic.innerHTML = `<i class="bi ${o.icon || 'bi-question-lg'}"></i>`;
      el.querySelector('.pc-confirm-title').textContent = o.title;
      el.querySelector('.pc-confirm-msg').textContent = o.message || '';
      const dt = el.querySelector('.pc-confirm-details');
      if (o.details && o.details.length) { dt.classList.remove('d-none'); dt.innerHTML = o.details.map(d => `<div class="pc-detail"><span>${d.label}</span><b>${d.value}</b></div>`).join(''); }
      else dt.classList.add('d-none');
      const rw = el.querySelector('.pc-confirm-reason');
      if (o.requireReason) { rw.classList.remove('d-none'); rw.querySelector('textarea').value = ''; rw.querySelector('.pc-reason-err').style.display = 'none'; }
      else rw.classList.add('d-none');
      const ok = el.querySelector('[data-act="ok"]'), ca = el.querySelector('[data-act="cancel"]');
      ok.className = 'btn ' + (o.variant === 'danger' ? 'btn-danger-pc' : o.variant === 'neutral' ? 'btn-ghost' : 'btn-navy');
      ok.textContent = o.confirmText || 'Confirm';
      ca.textContent = o.cancelText || 'Cancel';
      const m = bootstrap.Modal.getOrCreateInstance(el);
      let done = false;
      el.querySelectorAll('[data-act]').forEach(b => b.onclick = () => {
        if (b.dataset.act === 'ok' && o.requireReason) {
          const r = rw.querySelector('textarea');
          if (!r.value.trim()) { rw.querySelector('.pc-reason-err').style.display = 'block'; r.focus(); return; }
        }
        done = true; m.hide();
        res({ ok: b.dataset.act === 'ok', reason: rw.querySelector('textarea').value.trim() });
      });
      el.addEventListener('hidden.bs.modal', () => { if (!done) res({ ok: false, reason: '' }); }, { once: true });
      m.show();
      setTimeout(() => { (o.requireReason ? rw.querySelector('textarea') : ok).focus(); }, 350);
    });
  }
  window.pcConfirm = confirmModal;

  function busy(btn) {
    if (!btn || btn.dataset.pcBusy) return btn;
    btn.dataset.pcBusy = '1';
    btn.dataset.pcHtml = btn.innerHTML;
    btn.disabled = true;
    const label = btn.dataset.busyText || 'Saving...';
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${label}`;
    return btn;
  }

  function formHasContent(f) {
    if (!f) return false;
    const filled = [...f.querySelectorAll('input:not([type=hidden]):not([type=checkbox]):not([type=submit]), textarea, select')]
      .some(i => i.value.trim() !== '');
    const checked = [...f.querySelectorAll('input[type=checkbox]')].some(c => c.checked);
    return filled || checked;
  }

  document.addEventListener('submit', ev => {
    const f = ev.target;
    if (f.closest('.modal')) f.dataset.pcSubmitted = '1';
    if (f.dataset.pcBusy) { ev.preventDefault(); return; }
    if (f.dataset.confirm && !f.dataset.pcConfirmed) {
      ev.preventDefault(); ev.stopPropagation();
      const msg = f.dataset.confirm;
      const nm = (msg.match(/'([^']+)'/) || [])[1] || f.dataset.confirmName || '';
      const variant = f.dataset.confirmVariant || 'danger';
      confirmModal({
        title: f.dataset.confirmTitle || (nm ? `Delete "${nm}"?` : 'Are you sure?'),
        message: msg,
        confirmText: f.dataset.confirmOk || (variant === 'danger' ? 'Yes, Delete' : 'Confirm'),
        cancelText: 'Cancel',
        variant,
        icon: variant === 'danger' ? 'bi-trash3' : 'bi-exclamation-lg'
      }).then(({ ok }) => {
        if (ok) {
          f.dataset.pcConfirmed = '1';
          busy(f.querySelector('button[type="submit"]'));
          if (f.requestSubmit) f.requestSubmit(); else f.submit();
        }
      });
      return;
    }
    busy(ev.submitter || f.querySelector('button[type="submit"]:focus') || f.querySelector('button[type="submit"]'));
  }, true);

  document.addEventListener('click', ev => {
    const b = ev.target.closest('[data-require-reason]');
    if (b) {
      const t = document.querySelector(b.dataset.requireReason);
      if (t && !t.value.trim()) {
        ev.preventDefault(); ev.stopPropagation();
        t.classList.add('is-invalid'); t.focus();
        toast('A reason is required to reject this request.', 'warning', 'Missing reason');
      }
    }
    const a = ev.target.closest('a[href*="logout"]');
    if (a) {
      ev.preventDefault();
      ev.stopPropagation();
      confirmModal({
        title: 'Sign out?',
        message: 'Are you sure you want to sign out of your account?',
        confirmText: 'Sign Out', cancelText: 'Cancel',
        variant: 'danger', icon: 'bi-box-arrow-right'
      }).then(({ ok }) => { if (ok) { toast('Signing you out…', 'info'); window.location.href = a.href; } });
    }
  }, true);

  document.addEventListener('show.bs.modal', ev => {
    const f = ev.target.querySelector('form');
    if (f) { f.dataset.pcDirty = ''; f.dataset.pcSubmitted = ''; }
  });
  document.addEventListener('input', ev => {
    const m = ev.target.closest('.modal form');
    if (m) m.dataset.pcDirty = '1';
    if (ev.target.classList) ev.target.classList.remove('is-invalid');
  });
  document.addEventListener('hide.bs.modal', async ev => {
    const m = ev.target;
    if (!m || m.classList.contains('pc-confirm')) return;
    if (m.hasAttribute('data-pc-force-close')) return;
    const f = m.querySelector ? m.querySelector('form') : null;
    if (!f || f.dataset.pcSubmitted === '1' || f.dataset.pcDirty !== '1' || !formHasContent(f)) return;
    ev.preventDefault();
    const { ok } = await confirmModal({
      title: 'Discard unsaved changes?',
      message: "You have changes that haven't been saved. Are you sure you want to leave?",
      confirmText: 'Discard Changes', cancelText: 'Keep Editing',
      variant: 'danger', icon: 'bi-exclamation-triangle'
    });
    if (ok) { f.dataset.pcDirty = ''; f.reset(); pcHardClose(m); }
  });

  document.addEventListener('invalid', ev => {
    ev.target.classList.add('is-invalid');
    ev.target.addEventListener('input', () => ev.target.classList.remove('is-invalid'), { once: true });
  }, true);

  document.addEventListener('change', ev => {
    const inp = ev.target;
    if (inp.type !== 'file' || !inp.accept || !inp.accept.includes('image')) return;
    const old = inp.parentElement.querySelector('.pc-upload-preview');
    if (old) old.remove();
    const file = inp.files && inp.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const w = document.createElement('div');
    w.className = 'pc-upload-preview';
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.alt = 'Preview';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pc-upload-remove';
    btn.innerHTML = '<i class="bi bi-x"></i>Remove image';
    btn.addEventListener('click', () => { inp.value = ''; w.remove(); });
    w.append(img, btn);
    inp.after(w);
  });

  function initSidebar() {
    const b = document.getElementById('sbCollapse');
    if (!b) return;
    const apply = c => {
      document.body.classList.toggle('sb-collapsed', c);
      $$('#appSidebar .sidebar-link, #appSidebar .sidebar-footer .sidebar-link').forEach(a => {
        a.title = c ? a.textContent.trim() : '';
        if (c) a.setAttribute('aria-label', a.textContent.trim()); else a.removeAttribute('aria-label');
      });
      b.title = c ? 'Expand sidebar' : 'Collapse sidebar';
      localStorage.setItem('pc_sb', c ? '1' : '0');
    };
    b.addEventListener('click', () => apply(!document.body.classList.contains('sb-collapsed')));
    apply(localStorage.getItem('pc_sb') === '1');
  }

  document.addEventListener('DOMContentLoaded', () => {
    $$('.pc-flash').forEach(el => toast(el.textContent.trim(), el.dataset.type || 'success'));
    initSidebar();
  });
})();