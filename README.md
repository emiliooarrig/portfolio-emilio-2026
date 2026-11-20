# Portafolio — Emilio Guzmán

Landing de una sola página en **PHP vanilla con MVC propio**, **SCSS** compilado con **scssphp** y dependencias por **Composer**. Incluye la sección *conoce: Fovea*, en la que la página cambia a la paleta Bone & Violet de Fovea Studio, y un **panel de administración** en `/admin` para leer los mensajes de contacto y editar las certificaciones.

## Requisitos

- PHP 8.1 o superior, con las extensiones `pdo_mysql` y `mbstring`
- Composer 2
- MySQL 8 o MariaDB 10.4+ (necesario para el panel: guarda los mensajes, las certificaciones y los administradores)

## Puesta en marcha

```bash
composer install          # instala PHPMailer y scssphp, y crea .env si no existe
composer build:css        # compila scss/ → public/assets/css/main.css y admin.css
composer serve            # http://localhost:8000
```

El CSS compilado ya viene incluido, así que la página se ve bien aunque no lo compiles. Mientras editas estilos:

```bash
composer watch:css        # recompila cada vez que guardas un .scss
```

### Con XAMPP o Apache

Apunta el `DocumentRoot` (o el VirtualHost) a la carpeta **`public/`**. Si el servidor solo puede apuntar a la raíz del proyecto, el `.htaccess` de la raíz redirige a `public/` y bloquea el acceso a `app/`, `vendor/`, `.env`, etc. También funciona dentro de una subcarpeta, por ejemplo `http://localhost/portfolio-emilio-2026/public/`.

## Configuración (`.env`)

| Variable | Para qué sirve |
|---|---|
| `APP_DEBUG` | `true` en local, **`false` en producción** |
| `APP_KEY` | Clave para hashear IPs. Genera una con `php -r "echo bin2hex(random_bytes(32));"` |
| `DB_ENABLED`, `DB_*` | MySQL: mensajes del formulario, certificaciones y el panel `/admin` |
| `MAIL_ENABLED`, `MAIL_*`, `CONTACT_TO` | Te envía cada mensaje por correo (SMTP con PHPMailer) |
| `PUBLIC_EMAIL`, `LINKEDIN_URL`, `RESPONSE_TIME` | Datos de la sección de contacto |
| `FOVEA_URL`, `FOVEA_WHATSAPP` | Botones de la sección de Fovea |

Mientras una variable pública esté vacía, el sitio muestra un `[PLACEHOLDER]` en su lugar para que no se te pase.

**Gmail:** usa `smtp.gmail.com`, puerto `587`, `tls` y una *contraseña de aplicación* (no la de tu cuenta).

### Base de datos

```bash
mysql -u root -p < database/schema.sql
```

Después pon `DB_ENABLED=true` y tus credenciales en `.env`. El script usa `CREATE TABLE IF NOT EXISTS`, así que puedes volver a correrlo sobre una base existente para agregar las tablas nuevas.

Si MySQL corre en Docker o no tienes el cliente `mysql` a la mano, usa la conexión del `.env`:

```bash
composer db:migrate       # crea solo las tablas que falten; no toca las existentes ni sus datos
composer db:seed          # carga database/seeds/*.sql (el stack inicial) solo en tablas vacías
```

## Panel de administración (`/admin`)

```bash
composer admin:create     # pide nombre, correo y contraseña (mínimo 12 caracteres)
composer certs:import     # carga data/certifications.json en MySQL (solo si la tabla está vacía)
composer experience:import # carga data/experience.json en MySQL (solo si la tabla está vacía)
composer admin:password   # cambia la contraseña de un administrador (cierra sus sesiones abiertas)
```

Entra en `/admin/login`. El panel tiene:

- **Dashboard**: mensajes sin leer, de los últimos 7 días y totales; mensajes por día (últimos 14 días) y por tema; los 5 más recientes.
- **Mensajes**: búsqueda por nombre, correo o texto; filtros por estado y tema; detalle con "Responder por correo". Al abrir un mensaje nuevo queda como *Leído*; también se puede marcar como *Respondido* o *Archivado*, o eliminar.
- **Stack**: categorías (nombre, ícono, orden, publicada) y sus herramientas (nombre, orden, publicada y si aparece en la cinta animada). Cada categoría es una tarjeta con un campo para agregar herramientas rápido; clic en una herramienta para editarla o moverla de categoría. Al borrar una categoría se borran sus herramientas.
- **Experiencia**: empresa, puesto, descripción y periodo (mes opcional, año, o "trabajo aquí actualmente"); orden, publicar u ocultar y eliminar. En el sitio el periodo se ve como "mar 2021 — ago 2023", "2023 — hoy" o "Actual".
- **Certificaciones**: alta, edición, orden, publicar u ocultar y eliminar. El sitio muestra solo las publicadas, ordenadas por la columna *Orden*.

Transiciones del login:

- **Entrar**: el botón pasa a "Entrando…" con un indicador de carga. En la primera página del panel, una cortina azul saluda ("Hola, {nombre}") y se levanta; el menú lateral, la barra superior y el contenido entran por partes. Es solo CSS y aparece una vez por inicio de sesión (flash `admin_welcome`).
- **Error**: la tarjeta del login tiembla y aparece el toast de error.
- **Salir**: la cortina sube y cubre el panel con "Cerrando sesión", y después se envía el formulario. En el login se levanta con "Hasta pronto" (flash `login_bye`).
- **Contraseña**: el botón con ícono de ojo dentro del campo la muestra u oculta sin perder el foco ni la posición del cursor. Al enviar, el campo vuelve a ser de tipo contraseña.
- Con "reducir movimiento" activado no hay cortinas ni animaciones; sin JavaScript, el botón del ojo se oculta y salir es inmediato.

Seguridad del panel:

- Contraseñas con `password_hash` (bcrypt). No hay registro público: los admins solo se crean desde la terminal.
- Tras 5 intentos fallidos, el login se bloquea 15 minutos por IP y por correo. El mensaje de error no revela si el correo existe.
- Se regenera el ID de sesión al entrar y al salir. La sesión caduca tras 2 h sin uso o 12 h desde el login, y se invalida si cambia el navegador o la contraseña.
- CSRF en todos los formularios. Salir es un POST.
- Las respuestas llevan `Cache-Control: no-store` y `X-Robots-Tag: noindex`.

Si `DB_ENABLED=false`, `/admin` muestra cómo activarlo y el sitio lee la experiencia y las certificaciones de `data/experience.json` y `data/certifications.json` como respaldo.

## Cómo funciona el formulario

`POST /contacto` → `ContactController::store`

1. Verifica el **token CSRF** de la sesión.
2. **Honeypot**: si un bot llena el campo oculto, responde "ok" y descarta el mensaje.
3. **Límite de envíos**: 5 cada 10 minutos por IP (configurable con `CONTACT_MAX_ATTEMPTS` y `CONTACT_WINDOW_SECONDS`).
4. **Validación** en el servidor (`app/Validation/ContactRequest.php`).
5. **Guarda** el mensaje en MySQL y **te envía el correo**. Si al menos uno de los dos funciona, el usuario ve "Mensaje enviado"; los fallos quedan en `storage/logs/app.log`.

Con JavaScript, el formulario envía por `fetch` y muestra los estados "Enviando…" y "Enviado" sin recargar la página. Sin JavaScript, hace un POST normal y redirige de vuelta (patrón PRG) con los errores y los datos que el usuario ya había escrito.

## Editar contenido

| Qué | Dónde |
|---|---|
| Proyectos (y el texto de su modal) | `data/projects.json` |
| Certificaciones | Panel `/admin/certificaciones` |
| Mensajes del formulario | Panel `/admin/mensajes` |
| Stack tecnológico (tarjetas y cinta) | Panel `/admin/stack` |
| Experiencia | Panel `/admin/experiencia` |
| Textos de cada sección | `app/Views/partials/*.php` |
| Tu foto | `public/assets/img/emilio.webp` (proporción 4:5) |
| Tu CV | `public/assets/docs/cv-emilio-guzman.pdf` |

En los JSON, un campo con `null` se muestra como placeholder. Para agregar un proyecto, copia un bloque en `projects.json`. Las ilustraciones disponibles para el campo `visual` son `dash`, `menu`, `chain` y `list`; las categorías válidas son `dev`, `ops` y `data`.

## Estructura

```
app/
  Core/          App, Router, Request, Response, View, Auth, Csrf, Session, Database, RateLimiter, Logger, Env, helpers
  Controllers/   HomeController, ContactController
    Admin/       AdminController (base), Auth, Dashboard, Message, Stack, Experience, Certification
  Models/        Project (JSON) · ContactMessage, StackCategory → StackTool, Experience, Certification, Admin (MySQL)
  Services/      Mailer (PHPMailer)
  Validation/    ContactRequest, StackCategoryRequest, StackToolRequest, ExperienceRequest, CertificationRequest
  Views/         layout, home, partials/, partials/visuals/, errors/, admin/
config/          config.php, routes.php
data/            contenido editable en JSON
database/        schema.sql, seeds/ (stack.sql)
public/          index.php (front controller), .htaccess, assets/ (main.css/app.js del sitio, admin.css/admin.js del panel)
scss/            main.scss (sitio), admin.scss (panel), abstracts/ base/ layout/ sections/ motion/
bin/             build-css.php, console.php (comandos del panel)
storage/         logs/ y cache/ (deben tener permisos de escritura)
```

## Animaciones

- **Al cargar**: el titular sube línea por línea, la terminal escribe sus líneas y la tarjeta flota.
- **Con el scroll**: barra de progreso, revelado de secciones, el manifiesto que se ilumina, parallax, y las líneas del proceso y del timeline que se dibujan.
  - En **Chrome y Edge** esto se hace solo con CSS (`animation-timeline`), en `scss/motion/_scroll.scss`.
  - En **Firefox y Safari** lo hace `app.js` con IntersectionObserver, en `scss/motion/_fallback.scss`.
- **Cómo trabajo** (escritorio): al llegar, la sección se fija y el scroll vertical mueve los cuatro pasos en horizontal. Cada paso se enciende al pasar (el texto sube y se aclara y el círculo se llena), la línea y la barra de progreso avanzan, y el contador marca el paso actual. Al terminar el recorrido la sección se suelta y el scroll vuelve a ser vertical. La lógica está en `app.js` y los estilos en `.proc-h` (`scss/sections/_process.scss`); la velocidad se cambia con `SPEED`. En pantallas de menos de 901 px de ancho o 560 px de alto, sin JavaScript o con "reducir movimiento", se queda la cuadrícula vertical.
- **Fovea**: cuando su sección ocupa toda la pantalla, `app.js` agrega `html.is-fovea`. Un círculo violeta profundo (`$fv-wash`, #2B2266) nace del centro y cubre la página en 0.8 s, y la sección, sus vecinas (Proyectos y Experiencia), el menú y la barra de progreso pasan a la paleta Bone & Violet. Cuando la sección ocupa menos de la mitad de la pantalla, todo regresa. Las paletas están en `$fov-palette`, `$fov-projects-palette`, `$fov-exp-palette` y `$fov-nav-palette` (`scss/abstracts/_tokens.scss`), y los tiempos en `$fov-dur` y `$fov-color-*`.
- **"conoce: Fovea" con pausa de scroll** (escritorio): al llegar al contenido de Fovea se fija en pantalla y el scroll hace aparecer sus 6 servicios uno por uno, con una guía "sigue bajando" y un contador; al terminar se suelta y el scroll continúa. Se ajusta con `TRAVEL` en `app.js`; estilos en `scss/sections/_fovea.scss`. En móvil, pantallas bajas o con movimiento reducido no hay pausa.
- **Cursor personalizado** (solo con mouse): un punto y un anillo que lo sigue con retraso y se estira al moverse. Sobre enlaces crece; sobre los CTA se llena y muestra una palabra. Para ponerle palabra a cualquier elemento usa `data-cursor="Abrir"`; las descargas, enlaces externos, `mailto:`/WhatsApp y el botón de enviar la toman solos ("Descargar", "Visitar", "Escribir", "Enviar"). Estilos en `scss/layout/_cursor.scss`.
- **Movimiento reducido**: si el sistema operativo tiene activado "reducir movimiento", se desactivan todas las animaciones.

## Alertas (`Alerta`)

El proyecto no usa `alert()` ni `confirm()`. Todas las alertas salen de un componente propio al estilo SweetAlert: `public/assets/js/alerts.js` y `scss/components/_alerts.scss`. Se carga en el sitio y en el panel.

```js
await Alerta.fire({ icon: 'success', title: 'Listo', text: 'Se guardó.' });
if (await Alerta.confirm({ title: '¿Eliminar?', text: 'No se puede deshacer.', danger: true })) { /* … */ }
Alerta.toast({ icon: 'error', title: 'No se pudo guardar' });
Alerta.success('Título', 'Texto');   // también .error(), .warning(), .info()
```

- **Íconos animados**: `success`, `error`, `warning`, `info`, `question`.
- **Modal**: usa `<dialog>` nativo (atrapa el foco, Escape y clic en el fondo cancelan). Muestra uno a la vez; los demás esperan en cola. En acciones con `danger` el botón es rojo y el foco empieza en "Cancelar".
- **Toasts**: se apilan arriba a la derecha (abajo en móvil), se cierran solos con barra de tiempo y se pausan al pasar el cursor. Los de error usan `role="alert"`.
- **Sin escribir JS**:
  - `<form data-confirm="Texto" data-confirm-title="¿Eliminar?" data-confirm-ok="Sí, eliminar">` pide confirmación antes de enviar.
  - `<p data-flash="success|error|warning|info">Texto</p>` se convierte en toast.
  - Sin JavaScript, el formulario se envía directo y el aviso se queda visible en la página.
- El texto siempre se inserta como texto, nunca como HTML.

Dónde se usa: confirmaciones de borrado y avisos del panel (guardado, errores, login), y errores del formulario de contacto (validación como toast; sin conexión, límite de envíos o falla del servidor como modal).

## Seguridad incluida

- CSP estricta: sin scripts ni estilos inline.
- Cabeceras `X-Frame-Options`, `nosniff`, `Referrer-Policy` y `Permissions-Policy`.
- Cookie de sesión `HttpOnly` y `SameSite=Lax`, y `Secure` cuando el sitio va por HTTPS.
- Consultas preparadas con PDO, y salida escapada en todas las vistas con `e()`.
- Las IPs se guardan como HMAC con `APP_KEY`, nunca en claro.

## Antes de publicar

- [ ] `APP_DEBUG=false` y `APP_KEY` generado
- [ ] Llenar los placeholders (`[TU PUESTO]`, fechas, certificaciones, foto, CV, enlaces)
- [ ] Probar el formulario con SMTP real y con la base de datos
- [ ] Crear las tablas (`composer db:migrate`), cargar el stack (`composer db:seed`), tu usuario (`composer admin:create`) e importar la experiencia y las certificaciones (`composer experience:import`, `composer certs:import`)
- [ ] Usar un usuario de MySQL con permisos mínimos (ver el final de `database/schema.sql`)
- [ ] `composer install --no-dev --optimize-autoloader` en el servidor
- [ ] Dar permisos de escritura a `storage/`
