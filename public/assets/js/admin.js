/**
 * Panel de administración — interacciones mínimas.
 * Sin JS todo funciona: los filtros se envían con su botón y los borrados sin confirmar.
 * Las confirmaciones (form[data-confirm]) y los avisos ([data-flash]) los maneja alerts.js.
 */
(() => {
  'use strict';

  document.documentElement.classList.replace('no-js', 'js');

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Mostrar / ocultar contraseña sin perder el foco ni la posición del cursor.
  document.querySelectorAll('[data-pw-toggle]').forEach((btn) => {
    const input = document.getElementById(btn.getAttribute('aria-controls'));
    if (!input) return;

    btn.addEventListener('click', () => {
      const show = input.type === 'password';
      const { selectionStart, selectionEnd } = input;
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(show));
      btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
      input.focus();
      try { input.setSelectionRange(selectionStart, selectionEnd); } catch (e) { /* tipos sin selección */ }
    });

    // Nunca se envía ni se queda visible al salir de la página.
    input.form?.addEventListener('submit', () => { input.type = 'password'; });
  });

  // Login: el botón pasa a "Entrando…" mientras responde el servidor.
  const loginForm = document.querySelector('[data-login-form]');
  if (loginForm) {
    loginForm.addEventListener('submit', () => {
      const btn = loginForm.querySelector('[data-login-submit]');
      const label = loginForm.querySelector('[data-login-label]');
      btn.classList.add('is-busy');
      btn.setAttribute('aria-busy', 'true');
      if (label) label.textContent = 'Entrando…';
    });
  }

  // Cerrar sesión: la cortina sube y cubre el panel; después se envía el formulario.
  document.querySelectorAll('form[data-logout]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (reduceMotion || form.dataset.leaving === '1') return;
      e.preventDefault();
      form.dataset.leaving = '1';

      const curtain = document.createElement('div');
      curtain.className = 'curtain curtain--in';
      curtain.setAttribute('role', 'status');
      curtain.innerHTML = '<div class="curtain-in"><span class="curtain-brand ff-d">emilio<b>.</b>guzmán</span>'
        + '<p class="curtain-t ff-d">Cerrando sesión</p><span class="curtain-dots" aria-hidden="true"><i></i><i></i><i></i></span></div>';
      document.body.append(curtain);

      let sent = false;
      const go = () => { if (!sent) { sent = true; form.submit(); } };
      const timer = setTimeout(go, 900); // respaldo por si no llega animationend
      curtain.addEventListener('animationend', (ev) => {
        if (ev.target !== curtain) return; // ignora las animaciones del texto
        clearTimeout(timer);
        setTimeout(go, 250); // un respiro con la pantalla cubierta
      });
    });
  });

  // Al volver con "Atrás" (bfcache) no deben quedar cortinas ni botones ocupados.
  window.addEventListener('pageshow', (e) => {
    if (!e.persisted) return;
    document.querySelectorAll('.curtain--in').forEach((c) => c.remove());
    document.querySelectorAll('form[data-logout]').forEach((f) => { delete f.dataset.leaving; });
    document.querySelectorAll('[data-login-submit].is-busy').forEach((b) => {
      b.classList.remove('is-busy');
      b.removeAttribute('aria-busy');
      const label = b.querySelector('[data-login-label]');
      if (label) label.textContent = 'Entrar';
    });
  });

  // Los selects de filtro aplican al cambiar.
  document.querySelectorAll('[data-autosubmit]').forEach((select) => {
    select.addEventListener('change', () => select.form.requestSubmit());
  });

  // Un checkbox con data-toggles="#id" oculta ese bloque mientras está marcado
  // (p. ej. "Trabajo aquí actualmente" oculta la fecha de fin).
  document.querySelectorAll('input[type="checkbox"][data-toggles]').forEach((box) => {
    const target = document.querySelector(box.dataset.toggles);
    if (!target) return;
    const sync = () => { target.hidden = box.checked; };
    box.addEventListener('change', sync);
    sync();
  });

  // ─── Ordenar listas arrastrando ──────────────────────────────
  // Contenedor: data-sort="url" (+ data-sort-axis="y" para filas, data-sort-params="category_id=3").
  // Elementos: hijos directos con data-sort-id y data-sort-name. Asa: [data-sort-handle].
  // Con el asa enfocada, las flechas (e Inicio/Fin) también mueven. Se guarda solo;
  // si el servidor rechaza el cambio, la lista vuelve al último orden guardado.
  const sortLists = document.querySelectorAll('[data-sort]');

  if (sortLists.length) {
    const help = document.createElement('p');
    help.id = 'sort-help';
    help.className = 'sr-only';
    help.textContent = 'Arrastra para cambiar el orden o usa las flechas del teclado. Se guarda automáticamente.';
    const live = document.createElement('div');
    live.className = 'sr-only';
    live.setAttribute('aria-live', 'polite');
    document.body.append(help, live);

    const say = (text) => { live.textContent = ''; requestAnimationFrame(() => { live.textContent = text; }); };
    const notify = (icon, title) => window.Alerta?.toast({ icon, title, timer: icon === 'error' ? 6000 : 1800 });

    const itemsOf = (list) => [...list.children].filter((n) => n.hasAttribute('data-sort-id'));
    const orderOf = (list) => itemsOf(list).map((n) => n.dataset.sortId);
    const inside = (r, x, y) => x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;

    // Mueve nodos animando a los demás desde su posición anterior (FLIP).
    const flip = (list, mutate, skip = null) => {
      const els = itemsOf(list);
      const before = reduceMotion ? null : new Map(els.map((n) => [n, n.getBoundingClientRect()]));
      mutate();
      if (!before) return;
      els.forEach((n) => {
        if (n === skip) return;
        const a = before.get(n);
        const b = n.getBoundingClientRect();
        const dx = a.left - b.left;
        const dy = a.top - b.top;
        if (dx || dy) {
          n.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }],
            { duration: 200, easing: 'cubic-bezier(.2, .7, .3, 1)' });
        }
      });
    };

    const applyOrder = (list, ids) => {
      const byId = new Map(itemsOf(list).map((n) => [n.dataset.sortId, n]));
      flip(list, () => ids.forEach((id) => { const n = byId.get(id); if (n) list.append(n); }));
    };

    // Guardados en fila, uno tras otro, para que nunca se crucen.
    const state = new WeakMap();
    const stateOf = (list) => {
      if (!state.has(list)) state.set(list, { saved: orderOf(list), chain: Promise.resolve(), timer: 0 });
      return state.get(list);
    };
    sortLists.forEach(stateOf);

    const save = (list) => {
      const st = stateOf(list);
      clearTimeout(st.timer);
      st.chain = st.chain.then(async () => {
        const ids = orderOf(list);
        if (ids.join() === st.saved.join()) return;

        const body = new FormData();
        body.append('_csrf', document.querySelector('input[name="_csrf"]')?.value || '');
        ids.forEach((id) => body.append('ids[]', id));
        new URLSearchParams(list.dataset.sortParams || '').forEach((v, k) => body.append(k, v));

        list.setAttribute('aria-busy', 'true');
        try {
          const res = await fetch(list.dataset.sort, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'fetch' },
          });
          const data = await res.json().catch(() => null);
          if (!res.ok || !data || !data.ok) throw new Error(data?.message || 'No se pudo guardar el orden.');
          st.saved = ids;
          notify('success', 'Orden guardado');
        } catch (err) {
          applyOrder(list, st.saved);
          notify('error', err.message || 'No se pudo guardar el orden.');
          say('No se guardó el orden. La lista volvió como estaba.');
        } finally {
          list.removeAttribute('aria-busy');
        }
      });
    };

    // Con el asa enfocada: ↑/← antes, ↓/→ después, Inicio/Fin a los extremos.
    document.addEventListener('keydown', (e) => {
      if (drag && e.key === 'Escape') { e.preventDefault(); endDrag(true); return; }

      const handle = e.target.closest?.('[data-sort-handle]');
      if (!handle || drag) return;
      const item = handle.closest('[data-sort-id]');
      const list = item?.parentElement;
      if (!list?.hasAttribute('data-sort')) return;

      const keys = { ArrowUp: -1, ArrowLeft: -1, ArrowDown: 1, ArrowRight: 1, Home: -Infinity, End: Infinity };
      if (!(e.key in keys)) return;
      e.preventDefault();

      const sibs = itemsOf(list);
      const from = sibs.indexOf(item);
      const to = Math.max(0, Math.min(sibs.length - 1, from + keys[e.key]));
      const name = item.dataset.sortName || 'Elemento';

      if (to === from) {
        say(`${name} ya está ${to === 0 ? 'al principio' : 'al final'}.`);
        return;
      }

      flip(list, () => (to < from ? sibs[to].before(item) : sibs[to].after(item)));
      handle.focus({ preventScroll: true });
      handle.scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
      say(`${name}: posición ${to + 1} de ${sibs.length}.`);

      const st = stateOf(list);
      clearTimeout(st.timer);
      st.timer = setTimeout(() => save(list), 700); // espera a que termine de mover
    });

    // Arrastre con mouse, pluma o dedo (pointer events; el asa tiene touch-action: none).
    let drag = null;

    // El elemento sigue al puntero (sin salirse de su lista) mientras su hueco se mueve.
    const place = () => {
      const { item, list, x, y } = drag;
      item.style.transform = '';
      const r = item.getBoundingClientRect();
      const box = list.getBoundingClientRect();
      const left = drag.axisY ? r.left : Math.max(box.left, Math.min(box.right - r.width, x - drag.ox));
      const top = Math.max(box.top, Math.min(box.bottom - r.height, y - drag.oy));
      item.style.transform = `translate(${left - r.left}px, ${top - r.top}px)`;
    };

    const step = () => {
      const { item, list } = drag;

      // Punto de prueba dentro de la lista: si el puntero sale, cuenta el borde más cercano.
      const box = list.getBoundingClientRect();
      const x = drag.axisY ? (box.left + box.right) / 2 : Math.max(box.left + 1, Math.min(box.right - 1, drag.x));
      const y = Math.max(box.top + 1, Math.min(box.bottom - 1, drag.y));

      // Tras un intercambio no se vuelve a intercambiar con el mismo hasta salir de él
      // (evita que elementos de distinto tamaño "tiemblen").
      if (drag.lock && !inside(drag.lock.getBoundingClientRect(), x, y)) drag.lock = null;

      const sibs = itemsOf(list);
      const target = sibs.find((n) => n !== item && n !== drag.lock && inside(n.getBoundingClientRect(), x, y));

      if (target) {
        const forward = sibs.indexOf(item) < sibs.indexOf(target);
        flip(list, () => (forward ? target.after(item) : target.before(item)), item);
        drag.lock = target;
      }
      place();
    };

    // Desplaza la página si el puntero se acerca al borde de la ventana.
    const autoScroll = () => {
      if (!drag) return;
      const edge = 70;
      const { y } = drag;
      const speed = y < edge ? -(edge - y) / 4 : y > innerHeight - edge ? (y - innerHeight + edge) / 4 : 0;
      if (speed) { window.scrollBy(0, speed); step(); }
      drag.raf = requestAnimationFrame(autoScroll);
    };

    const endDrag = (cancel) => {
      if (!drag) return;
      const { item, list, handle, start, pointerId } = drag;
      cancelAnimationFrame(drag.raf);
      drag = null;

      try { handle.releasePointerCapture(pointerId); } catch (err) { /* ya liberado */ }

      // El elemento "aterriza" desde donde quedó hasta su lugar.
      const from = item.style.transform;
      item.style.transform = '';
      item.classList.remove('is-dragging');
      list.classList.remove('is-sorting');
      document.documentElement.classList.remove('is-sorting');
      if (from && !reduceMotion) {
        item.animate([{ transform: from }, { transform: 'none' }], { duration: 180, easing: 'cubic-bezier(.2, .7, .3, 1)' });
      }

      if (cancel) {
        applyOrder(list, start);
        say('Se canceló el cambio de orden.');
        return;
      }

      const ids = orderOf(list);
      if (ids.join() !== start.join()) {
        say(`${item.dataset.sortName || 'Elemento'}: posición ${ids.indexOf(item.dataset.sortId) + 1} de ${ids.length}.`);
        save(list);
      }
    };

    document.addEventListener('pointerdown', (e) => {
      const handle = e.target.closest?.('[data-sort-handle]');
      if (!handle || drag || e.button !== 0) return;
      const item = handle.closest('[data-sort-id]');
      const list = item?.parentElement;
      if (!list?.hasAttribute('data-sort')) return;

      e.preventDefault();
      handle.setPointerCapture(e.pointerId);
      handle.focus({ preventScroll: true });

      const r = item.getBoundingClientRect();
      drag = {
        item, list, handle,
        pointerId: e.pointerId,
        axisY: list.dataset.sortAxis === 'y',
        start: orderOf(list),
        ox: e.clientX - r.left,
        oy: e.clientY - r.top,
        x: e.clientX,
        y: e.clientY,
        lock: null,
        raf: 0,
      };

      item.classList.add('is-dragging');
      list.classList.add('is-sorting');
      document.documentElement.classList.add('is-sorting');
      drag.raf = requestAnimationFrame(autoScroll);
    });

    document.addEventListener('pointermove', (e) => {
      if (!drag || e.pointerId !== drag.pointerId) return;
      drag.x = e.clientX;
      drag.y = e.clientY;
      step();
    });

    document.addEventListener('pointerup', (e) => { if (drag && e.pointerId === drag.pointerId) endDrag(false); });
    document.addEventListener('pointercancel', (e) => { if (drag && e.pointerId === drag.pointerId) endDrag(true); });
    window.addEventListener('blur', () => endDrag(true));
  }

  // Menú lateral en móvil.
  const btn = document.querySelector('[data-adm-menu]');
  const side = document.getElementById('adm-side');

  if (btn && side) {
    const setOpen = (open) => {
      side.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', String(open));
      btn.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    };

    btn.addEventListener('click', () => setOpen(!side.classList.contains('is-open')));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
    document.addEventListener('click', (e) => {
      if (side.classList.contains('is-open') && !side.contains(e.target) && !btn.contains(e.target)) setOpen(false);
    });
  }
})();
