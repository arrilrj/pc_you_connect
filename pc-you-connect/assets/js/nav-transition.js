(function () {
  const bar = document.createElement('div');
  bar.className = 'pc-loadbar';
  document.addEventListener('DOMContentLoaded', () => document.body.appendChild(bar));

  let started = false;
  function start() {
    if (started) return;
    started = true;
    bar.classList.remove('done');
    bar.classList.add('active');
    requestAnimationFrame(() => { bar.style.width = '68%'; });
  }
  function finish() {
    if (!started) return;
    bar.style.width = '100%';
    bar.classList.add('done');
    setTimeout(() => {
      bar.classList.remove('active', 'done');
      bar.style.width = '0';
      started = false;
    }, 600);
  }
  window.addEventListener('load', finish);
  window.addEventListener('pageshow', () => {
    finish();
    document.querySelectorAll('.sidebar-link.nav-going').forEach(l => l.classList.remove('nav-going'));
  });

  function isNavigable(a) {
    if (!a || a.target === '_blank' || a.hasAttribute('download')) return false;
    const href = a.getAttribute('href') || '';
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.includes('logout')) return false;
    if (a.origin !== location.origin) return false;
    if (a.pathname === location.pathname && href.includes('#')) return false;
    return true;
  }

  document.addEventListener('click', ev => {
    const a = ev.target.closest('a');
    if (!ev.isTrusted || !isNavigable(a)) return;
    if (a.classList.contains('sidebar-link')) {
      document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('nav-going', 'active'));
      a.classList.add('nav-going');
    }
    start();
  }, true);

  document.addEventListener('submit', ev => {
    const f = ev.target;
    if (f.dataset.pcConfirmed === '1' || f.closest('.modal') || f.method === 'get') start();
  }, true);
})();