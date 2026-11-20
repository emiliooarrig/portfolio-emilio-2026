/**
 * Portafolio Emilio Guzmán — interacciones.
 * Sin dependencias. Todo es mejora progresiva: sin JS el sitio funciona.
 */
(() => {
  'use strict';

  const root = document.documentElement;
  root.classList.remove('no-js');
  root.classList.add('js');

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const supportsScrollTimeline = !!(window.CSS && CSS.supports && CSS.supports('animation-timeline: view()'));

  /* ─── Menú móvil ─────────────────────────────────────────── */
  const menuBtn = document.querySelector('[data-menu-toggle]');
  const mobileNav = document.getElementById('mnav');

  if (menuBtn && mobileNav) {
    const setMenu = (open) => {
      menuBtn.setAttribute('aria-expanded', String(open));
      menuBtn.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
      mobileNav.hidden = !open;
    };

    menuBtn.addEventListener('click', () => setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'));
    mobileNav.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !mobileNav.hidden) { setMenu(false); menuBtn.focus(); }
    });
    window.matchMedia('(min-width: 901px)').addEventListener('change', (e) => { if (e.matches) setMenu(false); });
  }

  /* ─── Filtro de proyectos ────────────────────────────────── */
  const tabs = Array.from(document.querySelectorAll('[data-filter]'));
  const cards = Array.from(document.querySelectorAll('[data-cats]'));
  const filterStatus = document.querySelector('[data-filter-status]');

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const filter = tab.dataset.filter;
      let visible = 0;

      tabs.forEach((t) => {
        const on = t === tab;
        t.classList.toggle('is-on', on);
        t.setAttribute('aria-pressed', String(on));
      });

      cards.forEach((card) => {
        const show = filter === 'all' || card.dataset.cats.split(' ').includes(filter);
        card.hidden = !show;
        card.classList.toggle('pc-feat', show && filter === 'all' && card.dataset.feat === '1');

        if (show) {
          visible += 1;
          if (!reduceMotion) {
            card.classList.remove('is-entering');
            void card.offsetWidth; // reinicia la animación de entrada
            card.classList.add('is-entering');
          }
        }
      });

      if (filterStatus) {
        filterStatus.textContent = `${visible} ${visible === 1 ? 'proyecto' : 'proyectos'} en ${tab.textContent.trim()}`;
      }
    });
  });

  /* ─── Modal de caso (<dialog> nativo) ────────────────────── */
  const dialog = document.getElementById('case-dialog');
  const dialogBody = dialog ? dialog.querySelector('[data-case-body]') : null;
  let lastTrigger = null;

  const closeDialog = (after) => {
    if (!dialog || !dialog.open) { if (after) after(); return; }

    const finish = () => {
      dialog.classList.remove('is-closing');
      dialog.close();
      if (after) after();
    };

    if (reduceMotion) { finish(); return; }

    dialog.classList.add('is-closing');
    dialog.querySelector('.m-p').addEventListener('animationend', finish, { once: true });
  };

  if (dialog && dialogBody && typeof dialog.showModal === 'function') {
    document.querySelectorAll('[data-case]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const tpl = document.getElementById(`case-${btn.dataset.case}`);
        if (!tpl) return;

        dialogBody.replaceChildren(tpl.content.cloneNode(true));
        lastTrigger = btn;
        dialog.showModal();
        document.body.classList.add('is-locked');
      });
    });

    // Toda la tarjeta abre el caso; el botón sigue siendo el control para teclado.
    document.querySelectorAll('.pc').forEach((card) => {
      const btn = card.querySelector('[data-case]');
      if (!btn) return;
      card.addEventListener('click', (e) => {
        if (e.target.closest('a, button') || String(window.getSelection()) !== '') return;
        btn.click();
      });
    });

    dialog.addEventListener('click', (e) => {
      const closer = e.target.closest('[data-case-close]');

      if (e.target === dialog) { e.preventDefault(); closeDialog(); return; } // clic en el fondo
      if (!closer) return;

      e.preventDefault();
      const href = closer.getAttribute('href');
      closeDialog(() => {
        if (href && href.startsWith('#')) {
          const target = document.querySelector(href);
          if (target) target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
          history.replaceState(null, '', href);
        }
      });
    });

    // Escape: anima el cierre en lugar de cerrar de golpe.
    dialog.addEventListener('cancel', (e) => { e.preventDefault(); closeDialog(); });

    dialog.addEventListener('close', () => {
      document.body.classList.remove('is-locked');
      if (lastTrigger) lastTrigger.focus();
    });
  }

  /* ─── Formulario de contacto (fetch con estados) ─────────── */
  const form = document.querySelector('[data-contact-form]');
  const okPanel = document.querySelector('[data-contact-ok]');

  if (form && okPanel && window.fetch && window.FormData) {
    const submitBtn = form.querySelector('[type="submit"]');
    const status = form.querySelector('[data-form-status]');

    const setErrors = (errors = {}) => {
      form.querySelectorAll('[data-error-for]').forEach((el) => {
        const name = el.dataset.errorFor;
        const message = errors[name] || '';
        el.textContent = message;

        const field = form.elements[name];
        if (field && typeof field.setAttribute === 'function' && field.type !== 'radio') {
          if (message) field.setAttribute('aria-invalid', 'true');
          else field.removeAttribute('aria-invalid');
        }
      });
    };

    const setBusy = (busy) => {
      submitBtn.disabled = busy;
      submitBtn.classList.toggle('is-sending', busy);
      submitBtn.setAttribute('aria-busy', String(busy));
    };

    // Errores de validación: toast (los campos ya muestran su error).
    // El resto (sin red, límite de envíos, sesión vencida, servidor): modal.
    // Si alerts.js no cargó, el mensaje queda en el texto bajo el formulario.
    const notify = (kind, message) => {
      if (!window.Alerta) { status.textContent = message; return; }

      if (kind === 'invalid') {
        window.Alerta.toast({ icon: 'warning', title: message });
      } else {
        window.Alerta.fire({ icon: 'error', title: 'No se pudo enviar tu mensaje', text: message, confirmText: 'Entendido' });
      }
    };

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (submitBtn.disabled) return;

      setErrors();
      status.textContent = '';
      setBusy(true);

      try {
        const res = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'fetch' },
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok && data.ok) {
          form.reset();
          form.hidden = true;
          okPanel.hidden = false;
          const heading = okPanel.querySelector('h3');
          if (heading) heading.focus();
          return;
        }

        setErrors(data.errors || {});
        notify(res.status === 422 ? 'invalid' : 'error', data.message || 'No se pudo enviar. Intenta de nuevo.');

        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        if (firstInvalid) firstInvalid.focus();
      } catch (err) {
        notify('error', 'Sin conexión. Revisa tu red e intenta de nuevo.');
      } finally {
        setBusy(false);
      }
    });

    const resetBtn = document.querySelector('[data-contact-reset]');
    if (resetBtn) {
      resetBtn.addEventListener('click', (e) => {
        e.preventDefault();
        okPanel.hidden = true;
        form.hidden = false;
        status.textContent = '';
        setErrors();
        form.elements.nombre.focus();
      });
    }
  }

  /* ─── Paleta de Fovea ────────────────────────────────────── */
  // Entra cuando la sección ocupa (casi) toda la pantalla bajo el menú y
  // sale cuando ya ocupa menos de la mitad. La animación es solo CSS.
  const fovea = document.getElementById('fovea');

  if (fovea) {
    const nav = document.querySelector('.nav');
    const themeMeta = document.querySelector('meta[name="theme-color"]');
    const baseTheme = themeMeta ? themeMeta.content : '';
    let inFovea = false;
    let ticking = false;

    const check = () => {
      ticking = false;
      const rect = fovea.getBoundingClientRect();
      const top = nav ? nav.offsetHeight : 0;
      const bottom = window.innerHeight;
      const span = Math.min(rect.height, bottom - top);
      const visible = Math.min(rect.bottom, bottom) - Math.max(rect.top, top);
      const coverage = span > 0 ? visible / span : 0;
      const next = inFovea ? coverage > 0.5 : coverage >= 0.95;

      if (next === inFovea) return;
      inFovea = next;
      root.classList.toggle('is-fovea', inFovea);
      if (themeMeta) themeMeta.content = inFovea ? '#2B2266' : baseTheme; // = $fv-wash
    };

    const queue = () => {
      if (!ticking) { ticking = true; window.requestAnimationFrame(check); }
    };

    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', queue, { passive: true });
    check();
  }

  /* ─── "Cómo trabajo": scroll horizontal anclado ──────────── */
  // Al llegar a la sección, se fija en pantalla y el scroll vertical mueve los pasos
  // hacia la izquierda; cada paso se enciende al pasar. Al terminar, se suelta.
  // Solo en escritorio con altura suficiente; si no, queda la cuadrícula vertical.
  const procSec = document.querySelector('[data-proc]');
  const procTrack = procSec && procSec.querySelector('[data-proc-track]');

  if (procSec && procTrack && !reduceMotion) {
    const wide = window.matchMedia('(min-width: 901px) and (min-height: 560px)');
    const steps = Array.from(procSec.querySelectorAll('[data-proc-step]'));
    const wrapEl = procTrack.parentElement;
    const countEl = procSec.querySelector('[data-proc-count]');
    const nav = document.querySelector('.nav');
    const SPEED = 1.35; // px de scroll vertical por px de recorrido horizontal

    let on = false;
    let dist = 0;
    let navH = 0;
    let stickH = 0;
    let ticking = false;
    let lastCount = '';

    const render = () => {
      ticking = false;
      if (!on) return;

      const rect = procSec.getBoundingClientRect();
      const travel = rect.height - stickH;
      const p = travel > 0 ? Math.min(Math.max((navH - rect.top) / travel, 0), 1) : 0;

      procTrack.style.transform = `translate3d(${(-p * dist).toFixed(1)}px, 0, 0)`;
      procSec.style.setProperty('--p', p.toFixed(4));

      // El paso i se enciende entre t = i - 1 y t = i (t recorre 0 … n-1).
      const t = p * (steps.length - 1);
      let current = 0;
      steps.forEach((step, i) => {
        const s = Math.min(Math.max(t - i + 1, 0), 1);
        step.style.setProperty('--s', s.toFixed(3));
        step.classList.toggle('is-lit', s >= 0.98);
        if (s >= 0.5) current = i;
      });

      const label = String(current + 1).padStart(2, '0');
      if (countEl && label !== lastCount) { countEl.textContent = label; lastCount = label; }
      procSec.classList.toggle('is-end', p >= 0.999);
    };

    const queue = () => { if (!ticking) { ticking = true; window.requestAnimationFrame(render); } };

    const layout = () => {
      const enable = wide.matches;

      if (!enable) {
        if (on) {
          on = false;
          procSec.classList.remove('proc-h', 'is-end');
          procSec.style.height = '';
          procSec.style.removeProperty('--p');
          procTrack.style.transform = '';
          steps.forEach((step) => { step.style.removeProperty('--s'); step.classList.remove('is-lit'); });
        }
        return;
      }

      on = true;
      procSec.classList.add('proc-h');
      navH = nav ? nav.offsetHeight : 0;
      root.style.setProperty('--nav-h', `${navH}px`);

      // Recorrido: lo que la pista sobresale del área de contenido de .wrap.
      const cs = window.getComputedStyle(wrapEl);
      const inner = wrapEl.clientWidth - parseFloat(cs.paddingLeft) - parseFloat(cs.paddingRight);
      dist = Math.max(0, procTrack.scrollWidth - inner);
      stickH = window.innerHeight - navH;
      procSec.style.height = `${Math.round(stickH + dist * SPEED)}px`;
      render();
    };

    layout();
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', () => window.requestAnimationFrame(layout), { passive: true });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(layout);

    // La sección creció: si se entró con un #ancla más abajo, se vuelve a ella.
    if (location.hash.length > 1) {
      const target = document.getElementById(decodeURIComponent(location.hash.slice(1)));
      if (target && procSec.compareDocumentPosition(target) & Node.DOCUMENT_POSITION_FOLLOWING) {
        target.scrollIntoView({ behavior: 'auto' });
      }
    }
  }

  /* ─── "conoce: Fovea": pausa de scroll ───────────────────── */
  // Al llegar al contenido de Fovea se fija en pantalla y el scroll revela los
  // 6 servicios uno por uno (con la guía "sigue bajando" y un contador). Al
  // terminar se suelta y el scroll sigue normal. Solo en escritorio con altura
  // suficiente y sin "reducir movimiento"; si no, queda el diseño de siempre.
  const fovPin = document.querySelector('[data-fov-pin]');

  if (fovPin && !reduceMotion) {
    const wide = window.matchMedia('(min-width: 901px) and (min-height: 560px)');
    const items = Array.from(fovPin.querySelectorAll('[data-fov-svc]'));
    const countEl = fovPin.querySelector('[data-fov-count]');
    const nav = document.querySelector('.nav');
    const TRAVEL = 0.3; // alturas de pantalla de scroll por cada servicio (más alto = más lento)

    let on = false;
    let navH = 0;
    let stickH = 0;
    let ticking = false;
    let lastCount = '';

    const render = () => {
      ticking = false;
      if (!on) return;

      const rect = fovPin.getBoundingClientRect();
      const travel = rect.height - stickH;
      const p = travel > 0 ? Math.min(Math.max((navH - rect.top) / travel, 0), 1) : 0;
      fovPin.style.setProperty('--p', p.toFixed(4));

      // Los servicios aparecen entre p = 0.05 y p = 0.85; el último queda
      // completo antes de p = 1 y hay una pausa corta con todo visible.
      const q = Math.min(Math.max((p - 0.05) / 0.8, 0), 1);
      const t = q * (items.length + 0.5);
      let shown = 0;
      items.forEach((item, i) => {
        const s = Math.min(Math.max(t - i, 0), 1);
        item.style.setProperty('--s', s.toFixed(3));
        const lit = s >= 0.98;
        item.classList.toggle('is-lit', lit);
        if (lit) shown = i + 1;
      });

      const label = String(shown).padStart(2, '0');
      if (countEl && label !== lastCount) { countEl.textContent = label; lastCount = label; }
      fovPin.classList.toggle('is-done', shown === items.length);
    };

    const queue = () => { if (!ticking) { ticking = true; window.requestAnimationFrame(render); } };

    const grid = fovPin.querySelector('.fov-grid');
    const CUE_SPACE = 96; // px que reserva la guía "sigue bajando" bajo el contenido

    const disable = () => {
      if (!on) return;
      on = false;
      fovPin.classList.remove('fov-pin-on', 'is-done');
      fovPin.style.height = '';
      fovPin.style.removeProperty('--p');
      items.forEach((item) => { item.style.removeProperty('--s'); item.classList.remove('is-lit'); });
    };

    const layout = () => {
      if (!wide.matches) { disable(); return; }

      navH = nav ? nav.offsetHeight : 0;
      stickH = window.innerHeight - navH;

      // Si el contenido no cabe en la pantalla fijada, se recortaría: mejor no fijar.
      if (grid && grid.offsetHeight + CUE_SPACE > stickH) { disable(); return; }

      on = true;
      fovPin.classList.add('fov-pin-on');
      root.style.setProperty('--nav-h', `${navH}px`);
      fovPin.style.height = `${Math.round(stickH + window.innerHeight * TRAVEL * items.length)}px`;
      render();
    };

    layout();
    window.addEventListener('scroll', queue, { passive: true });
    window.addEventListener('resize', () => window.requestAnimationFrame(layout), { passive: true });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(layout);
  }

  /* ─── Cursor personalizado ───────────────────────────────── */
  // Solo con mouse y sin "reducir movimiento". El anillo sigue al puntero
  // con retraso, crece sobre enlaces y muestra una palabra sobre los CTA:
  // data-cursor="Abrir" o las reglas automáticas de labelFor().
  if (window.matchMedia('(hover: hover) and (pointer: fine)').matches && !reduceMotion) {
    const cur = document.createElement('div');
    cur.className = 'cur';
    cur.setAttribute('aria-hidden', 'true');
    cur.innerHTML = '<div class="cur-ring"><div class="cur-c"><span class="cur-l"></span></div></div><div class="cur-dot"><i></i></div>';
    document.body.append(cur);

    const ring = cur.querySelector('.cur-ring');
    const shape = cur.querySelector('.cur-c');
    const dot = cur.querySelector('.cur-dot');
    const label = cur.querySelector('.cur-l');

    const LINK = 'a, button, label, summary, [role="button"]';
    const TEXT = 'input:not([type="radio"]):not([type="checkbox"]), textarea, select';
    const LIGHT = '.dark, .cobalt, .foot';
    const FOVEA = '.fov, .fov-near';
    const SOLID = '.pc, .tabs, .form, .ok'; // superficies blancas dentro de zonas oscuras

    const labelFor = (el) => {
      const tagged = el.closest('[data-cursor]');
      if (tagged) return tagged.dataset.cursor;

      const link = el.closest('a, button');
      if (!link) return '';
      if (link.hasAttribute('download')) return 'Descargar';
      if (link.type === 'submit') return 'Enviar';

      const href = link.getAttribute('href') || '';
      if (href.startsWith('mailto:') || href.includes('wa.me/')) return 'Escribir';
      if (link.target === '_blank') return 'Visitar';
      return '';
    };

    let x = 0; let y = 0; // puntero
    let rx = 0; let ry = 0; // anillo
    let target = null;
    let shown = false;
    let dirty = false;
    let raf = 0;

    const setState = (el) => {
      target = el;
      if (!(el instanceof Element)) return;

      const text = !!el.closest(TEXT);
      const word = text ? '' : labelFor(el);
      const link = !text && !word && !!el.closest(LINK);
      const light = !el.closest(SOLID)
        && (!!el.closest(LIGHT) || (root.classList.contains('is-fovea') && !!el.closest(FOVEA)));

      cur.classList.toggle('is-text', text);
      cur.classList.toggle('is-cta', !!word);
      cur.classList.toggle('is-link', link);
      cur.classList.toggle('is-light', light);
      if (word && label.textContent !== word) label.textContent = word;
    };

    const tick = () => {
      raf = 0;
      // Al hacer scroll cambia lo que hay bajo el puntero aunque no se mueva.
      if (dirty) { dirty = false; setState(document.elementFromPoint(x, y)); }

      const dx = x - rx;
      const dy = y - ry;
      rx += dx * 0.2;
      ry += dy * 0.2;
      ring.style.transform = `translate3d(${rx}px, ${ry}px, 0)`;

      // Se estira en la dirección del movimiento (menos cuando muestra texto).
      const lag = Math.hypot(dx, dy);
      const s = cur.classList.contains('is-cta') ? 0 : Math.min(lag / 260, 0.28);
      shape.style.transform = s > 0.005 ? `rotate(${Math.atan2(dy, dx)}rad) scale(${1 + s}, ${1 - s * 0.6})` : '';

      if (lag > 0.1) raf = window.requestAnimationFrame(tick);
    };

    const queue = () => { if (!raf && shown) raf = window.requestAnimationFrame(tick); };

    window.addEventListener('pointermove', (e) => {
      // Pluma o pantalla táctil: vuelve el cursor nativo.
      if (e.pointerType !== 'mouse') { root.classList.remove('has-cursor'); shown = false; return; }

      x = e.clientX;
      y = e.clientY;
      dot.style.transform = `translate3d(${x}px, ${y}px, 0)`;

      if (!shown) {
        shown = true;
        rx = x; ry = y;
        root.classList.add('has-cursor');
      }
      if (e.target !== target) setState(e.target);
      queue();
    }, { passive: true });

    window.addEventListener('scroll', () => { dirty = true; queue(); }, { passive: true });

    document.documentElement.addEventListener('mouseleave', () => cur.classList.add('is-out'));
    document.documentElement.addEventListener('mouseenter', () => cur.classList.remove('is-out'));

    window.addEventListener('pointerdown', (e) => { if (e.pointerType === 'mouse') cur.classList.add('is-down'); });
    window.addEventListener('pointerup', () => {
      cur.classList.remove('is-down', 'is-click');
      void cur.offsetWidth; // reinicia la onda
      cur.classList.add('is-click');
    });

    // Los <dialog> (caso de proyecto, Alerta) viven en la capa superior: el cursor se
    // muda al último abierto y vuelve al body cuando se cierran.
    new MutationObserver(() => {
      const top = Array.from(document.querySelectorAll('dialog[open]')).pop();
      (top || document.body).append(cur);
      dirty = true;
      queue();
    }).observe(document.body, { subtree: true, attributes: true, attributeFilter: ['open'] });
  }

  /* ─── Respaldo de animaciones con scroll (Firefox / Safari) ─ */
  if (!supportsScrollTimeline && !reduceMotion && 'IntersectionObserver' in window) {
    root.classList.add('io');

    const revealer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-in');
          revealer.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.05 });

    document
      .querySelectorAll('.rv, .rv-s, .rv-l, .ml, .draw-x, .draw-y, .fw, .fov-word')
      .forEach((el) => revealer.observe(el));

    // Barra de progreso de lectura.
    const bar = document.querySelector('.prog');
    if (bar) {
      let ticking = false;
      const update = () => {
        const max = root.scrollHeight - root.clientHeight;
        bar.style.transform = `scaleX(${max > 0 ? Math.min(window.scrollY / max, 1) : 0})`;
        ticking = false;
      };
      window.addEventListener('scroll', () => {
        if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
      }, { passive: true });
      update();
    }
  }
})();
