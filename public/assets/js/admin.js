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
