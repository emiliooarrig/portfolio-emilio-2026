/**
 * Alerta — modales y toasts al estilo SweetAlert. Reemplaza alert() y confirm().
 *
 *   await Alerta.fire({ icon: 'success', title: 'Listo', text: 'Se guardó.' });
 *   if (await Alerta.confirm({ title: '¿Eliminar?', text: 'No se puede deshacer.', danger: true })) { … }
 *   Alerta.toast({ icon: 'success', title: 'Cambios guardados' });
 *   Alerta.success('Título', 'Texto') · .error() · .warning() · .info()
 *
 * Íconos: success | error | warning | info | question.
 *
 * Uso declarativo (sin escribir JS):
 *   <form data-confirm="¿Eliminar X?" data-confirm-title="Eliminar" data-confirm-ok="Sí, eliminar">
 *     → pide confirmación antes de enviar (si no hay JS, se envía directo).
 *   <p data-flash="success|error|warning|info">Texto</p>
 *     → se muestra como toast y se quita de la página (sin JS se queda visible).
 *
 * El texto siempre se inserta como texto (nunca HTML).
 */
(() => {
  'use strict';

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const NS = 'http://www.w3.org/2000/svg';
  let uid = 0;

  /* ─── Íconos SVG animados ────────────────────────────────── */
  const MARKS = {
    success:  [['path', { class: 'al-mark', d: 'M25 43l11 11 23-25' }]],
    error:    [['path', { class: 'al-mark', d: 'M30 30l24 24M54 30L30 54' }]],
    warning:  [['path', { class: 'al-mark', d: 'M42 24v24' }], ['circle', { class: 'al-dot', cx: 42, cy: 60, r: 3.5 }]],
    info:     [['circle', { class: 'al-dot', cx: 42, cy: 25, r: 3.5 }], ['path', { class: 'al-mark', d: 'M42 37v24' }]],
    question: [['path', { class: 'al-mark', d: 'M33 33a9 9 0 1 1 13.5 7.8c-2.6 1.4-4.5 3.2-4.5 6.2v2' }], ['circle', { class: 'al-dot', cx: 42, cy: 60, r: 3.5 }]],
  };

  const svg = (tag, attrs) => {
    const el = document.createElementNS(NS, tag);
    Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, String(v)));
    return el;
  };

  const iconEl = (type) => {
    const kind = MARKS[type] ? type : 'info';
    const wrap = document.createElement('div');
    wrap.className = `al-ic al-ic--${kind}`;
    wrap.setAttribute('aria-hidden', 'true');

    const root = svg('svg', { viewBox: '0 0 84 84' });
    root.append(svg('circle', { class: 'al-halo', cx: 42, cy: 42, r: 40 }));
    root.append(svg('circle', { class: 'al-ring', cx: 42, cy: 42, r: 38, pathLength: 100, transform: 'rotate(-90 42 42)' }));
    MARKS[kind].forEach(([tag, attrs]) => {
      root.append(svg(tag, tag === 'path' ? { ...attrs, pathLength: 100 } : attrs));
    });

    wrap.append(root);
    return wrap;
  };

  const el = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  };

  // Espera el fin de la animación de salida (con tope por si no hay animación).
  const afterAnimation = (node, ms) => new Promise((resolve) => {
    if (reduceMotion) { resolve(); return; }
    const done = () => { clearTimeout(timer); resolve(); };
    const timer = setTimeout(done, ms);
    node.addEventListener('animationend', done, { once: true });
  });

  const timerBar = (ms) => {
    const bar = el('div', 'al-timer');
    const fill = el('i');
    fill.style.animationDuration = `${ms}ms`;
    bar.append(fill);
    return { bar, fill };
  };

  /* ─── Modal ──────────────────────────────────────────────── */
  const open = (opts) => new Promise((resolve) => {
    const o = {
      icon: 'info',
      title: '',
      text: '',
      confirmText: 'Aceptar',
      cancelText: 'Cancelar',
      showCancel: false,
      danger: false,
      timer: 0,
      allowOutsideClick: true,
      ...opts,
    };

    const id = ++uid;
    const dialog = el('dialog', 'al');
    const box = el('div', 'al-box');

    if (o.icon) box.append(iconEl(o.icon));

    if (o.title) {
      const title = el('h2', 'al-t', o.title);
      title.id = `al-t-${id}`;
      dialog.setAttribute('aria-labelledby', title.id);
      box.append(title);
    }

    if (o.text) {
      const text = el('p', 'al-x', o.text);
      text.id = `al-x-${id}`;
      dialog.setAttribute('aria-describedby', text.id);
      box.append(text);
    }

    const actions = el('div', 'al-a');
    const ok = el('button', `al-b ${o.danger ? 'al-b--danger' : 'al-b--ok'}`, o.confirmText);
    ok.type = 'button';
    let cancel = null;

    if (o.showCancel) {
      cancel = el('button', 'al-b al-b--ghost', o.cancelText);
      cancel.type = 'button';
      actions.append(cancel);
    }
    actions.append(ok);
    box.append(actions);

    let timeout = 0;
    if (o.timer > 0) {
      box.append(timerBar(o.timer).bar);
    }

    dialog.append(box);
    document.body.append(dialog);

    let settled = false;
    const close = async (result) => {
      if (settled) return;
      settled = true;
      clearTimeout(timeout);
      dialog.classList.add('is-closing');
      await afterAnimation(box, 260);
      dialog.close();
      dialog.remove();
      resolve(result);
    };

    ok.addEventListener('click', () => close({ isConfirmed: true, isDismissed: false }));
    if (cancel) cancel.addEventListener('click', () => close({ isConfirmed: false, isDismissed: true, reason: 'cancel' }));

    // Escape: cierra animado en lugar de golpe.
    dialog.addEventListener('cancel', (e) => {
      e.preventDefault();
      close({ isConfirmed: false, isDismissed: true, reason: 'esc' });
    });

    // Clic en el fondo.
    dialog.addEventListener('click', (e) => {
      if (e.target === dialog && o.allowOutsideClick) close({ isConfirmed: false, isDismissed: true, reason: 'backdrop' });
    });

    dialog.showModal();
    // En acciones destructivas el foco empieza en "Cancelar".
    ((o.danger && cancel) ? cancel : ok).focus();

    if (o.timer > 0) {
      timeout = setTimeout(() => close({ isConfirmed: false, isDismissed: true, reason: 'timer' }), o.timer);
    }
  });

  // Un modal a la vez: los siguientes esperan su turno.
  let queue = Promise.resolve();
  const fire = (opts = {}) => {
    const next = queue.then(() => open(opts));
    queue = next.catch(() => {});
    return next;
  };

  const confirm = async (opts = {}) => {
    const result = await fire({
      icon: 'warning',
      title: '¿Estás seguro?',
      confirmText: opts.danger ? 'Sí, eliminar' : 'Sí, continuar',
      ...opts,
      showCancel: true,
    });
    return result.isConfirmed;
  };

  /* ─── Toasts ─────────────────────────────────────────────── */
  let stack = null;

  const toast = (opts = {}) => {
    const o = { icon: 'success', title: '', text: '', timer: 4500, ...opts };

    if (!stack) {
      stack = el('div', 'al-toasts');
      stack.setAttribute('role', 'region');
      stack.setAttribute('aria-label', 'Notificaciones');
      document.body.append(stack);
    }

    const kind = MARKS[o.icon] ? o.icon : 'info';
    const item = el('div', `al-toast al-toast--${kind}`);
    // Los errores interrumpen al lector de pantalla; el resto espera su turno.
    item.setAttribute('role', kind === 'error' ? 'alert' : 'status');

    const body = el('div', 'al-toast-b');
    if (o.title) body.append(el('strong', 'al-toast-t', o.title));
    if (o.text) body.append(el('span', 'al-toast-x', o.text));

    const x = el('button', 'al-close');
    x.type = 'button';
    x.setAttribute('aria-label', 'Cerrar notificación');
    const xs = svg('svg', { viewBox: '0 0 24 24', 'aria-hidden': 'true' });
    xs.append(svg('path', { d: 'M6 6l12 12M18 6L6 18' }));
    x.append(xs);

    item.append(iconEl(kind), body, x);

    let closed = false;
    const dismiss = async () => {
      if (closed) return;
      closed = true;
      item.classList.add('is-closing');
      await afterAnimation(item, 300);
      item.remove();
    };

    x.addEventListener('click', dismiss);

    if (o.timer > 0) {
      const { bar, fill } = timerBar(o.timer);
      item.append(bar);
      // Se cierra cuando la barra llega a cero; al pasar el cursor se pausa.
      fill.addEventListener('animationend', dismiss, { once: true });
      if (reduceMotion) setTimeout(dismiss, o.timer);
    }

    stack.append(item);
    return { close: dismiss };
  };

  const shortcut = (icon) => (title, text = '', extra = {}) => fire({ icon, title, text, ...extra });

  window.Alerta = {
    fire,
    confirm,
    toast,
    success: shortcut('success'),
    error: shortcut('error'),
    warning: shortcut('warning'),
    info: shortcut('info'),
  };

  /* ─── Uso declarativo ────────────────────────────────────── */
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') return;

    e.preventDefault();
    const d = form.dataset;
    const yes = await confirm({
      icon: d.confirmIcon || 'warning',
      title: d.confirmTitle || '¿Estás seguro?',
      text: d.confirm,
      confirmText: d.confirmOk || 'Sí, eliminar',
      danger: d.confirmDanger !== '0',
    });

    if (yes) {
      d.confirmed = '1';
      form.requestSubmit(e.submitter || undefined);
    }
  });

  const flashes = () => {
    document.querySelectorAll('[data-flash]').forEach((node) => {
      const map = { ok: 'success', success: 'success', error: 'error', warning: 'warning', info: 'info' };
      toast({ icon: map[node.dataset.flash] || 'info', title: node.textContent.trim(), timer: node.dataset.flash === 'error' ? 7000 : 4500 });
      node.remove();
    });
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', flashes);
  else flashes();
})();
