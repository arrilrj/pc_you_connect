(function () {
  function hourGreeting() {
    const h = new Date().getHours();
    return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }

  function initGreeting() {
    const h = document.querySelector('h3');
    if (!h) return;
    const m = h.textContent.match(/^(Good (morning|afternoon|evening)),\s*(.+)$/);
    if (!m) return;
    h.classList.add('live-greet');
    let current = '';
    const apply = () => {
      const next = hourGreeting() + ', ' + m[3];
      if (next === current) return;
      if (!current) { current = next; h.textContent = next; return; }
      current = next;
      h.classList.add('swap');
      setTimeout(() => { h.textContent = next; h.classList.remove('swap'); }, 200);
    };
    apply();
    setInterval(apply, 30000);
  }

  function initCursor() {
    const fine = window.matchMedia('(pointer: fine)').matches;
    if (!fine) return;
    document.documentElement.classList.add('pc-custom-cursor');

    const arrow = document.createElement('div');
    arrow.className = 'pc-arrow';
    arrow.innerHTML = '<svg viewBox="0 0 24 24" width="26" height="26"><path d="M5 3l14 8.5-6.2 1.4L16 20.5 13.2 21.8 10 14.6 5 18z"/></svg>';
    document.body.appendChild(arrow);

    let mx = -100, my = -100, px = -100, py = -100, velX = 0, lastX = -100, tilt = 0;

    document.addEventListener('mousemove', e => {
      mx = e.clientX; my = e.clientY;
      arrow.style.opacity = '1';
    }, { passive: true });

    const HOT = 'a, button, .btn, .sidebar-link, .filter-tab, .text-tab, .pg-btn, .cal-day, .act-quiet, label, .f-chip, summary';
    document.addEventListener('mouseover', e => {
      arrow.classList.toggle('hot', !!e.target.closest(HOT));
      arrow.classList.toggle('on-text', !!e.target.closest('input:not([type=checkbox]):not([type=radio]):not([type=submit]):not([type=button]), textarea'));
    }, { passive: true });

    document.addEventListener('mousedown', () => arrow.classList.add('press'));
    document.addEventListener('mouseup', () => arrow.classList.remove('press'));
    document.addEventListener('mouseleave', () => { arrow.style.opacity = '0'; });
    document.addEventListener('mouseenter', () => { arrow.style.opacity = '1'; });

    (function loop() {
      px += (mx - px) * 0.5;
      py += (my - py) * 0.5;
      velX = px - lastX;
      lastX = px;
      tilt += ((Math.max(-14, Math.min(14, velX * 1.6))) - tilt) * 0.2;
      arrow.style.transform = `translate(${px}px, ${py}px) rotate(${tilt}deg)`;
      requestAnimationFrame(loop);
    })();
  }

  document.addEventListener('DOMContentLoaded', () => {
    initGreeting();
    initCursor();
  });
})();