<?php
/** @var string $content */
/** @var string|null $title */
/** @var array<string, mixed>|null $admin */
/** @var string|null $heading */
/** @var string|null $section */
/** @var int|null $newCount */
/** @var array{type: string, text: string}|null $flash */
$admin = $admin ?? null;
$section = $section ?? '';
$nav = [
    'dashboard'      => ['/admin', 'Dashboard', 'grid'],
    'messages'       => ['/admin/mensajes', 'Mensajes', 'mail'],
    'stack'          => ['/admin/stack', 'Stack', 'code'],
    'projects'       => ['/admin/proyectos', 'Proyectos', 'folder'],
    'experience'     => ['/admin/experiencia', 'Experiencia', 'briefcase'],
    'certifications' => ['/admin/certificaciones', 'Certificaciones', 'award'],
];
?>
<!doctype html>
<html lang="es" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Panel') ?></title>
    <meta name="color-scheme" content="light">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&amp;family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;family=JetBrains+Mono:wght@400;500&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
    <script src="<?= e(asset('assets/js/alerts.js')) ?>" defer></script>
    <script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</head>
<body class="adm<?= $admin === null ? ' adm--bare' : '' ?><?= !empty($welcome) ? ' adm--welcome' : '' ?>">
<?php if ($admin === null): ?>
    <main class="adm-bare"><?= $content ?></main>
<?php else: ?>
    <?php if (!empty($welcome)): ?>
        <!-- Recién iniciada la sesión: la cortina saluda y se levanta (solo CSS). -->
        <div class="curtain curtain--out" aria-hidden="true">
            <div class="curtain-in">
                <span class="curtain-brand ff-d">emilio<b>.</b>guzmán</span>
                <p class="curtain-t ff-d">Hola, <?= e((string) $admin['name']) ?></p>
            </div>
        </div>
    <?php endif; ?>
    <a class="skip" href="#adm-main">Saltar al contenido</a>

    <aside class="adm-side" id="adm-side">
        <a class="adm-brand ff-d" href="<?= e(url('/admin')) ?>">emilio<b>.</b>guzmán <span>Panel</span></a>

        <nav class="adm-nav" aria-label="Panel">
            <?php foreach ($nav as $key => [$href, $label, $ic]): ?>
                <a class="adm-nl<?= $section === $key ? ' is-on' : '' ?>" href="<?= e(url($href)) ?>"<?= $section === $key ? ' aria-current="page"' : '' ?>>
                    <?= icon($ic) ?><span><?= e($label) ?></span>
                    <?php if ($key === 'messages' && ($newCount ?? 0) > 0): ?>
                        <b class="adm-badge" aria-label="<?= (int) $newCount ?> sin leer"><?= (int) $newCount ?></b>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="adm-side-f">
            <a class="adm-nl" href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('external') ?><span>Ver el sitio</span></a>
        </div>
    </aside>

    <div class="adm-app">
        <header class="adm-top">
            <button class="adm-menu" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="adm-side" data-adm-menu><?= icon('menu') ?></button>
            <h1 class="adm-h ff-d"><?= e($heading ?? '') ?></h1>
            <div class="adm-user">
                <span class="adm-av" aria-hidden="true"><?= e(mb_strtoupper(mb_substr((string) $admin['name'], 0, 1))) ?></span>
                <span class="adm-un"><?= e((string) $admin['name']) ?></span>
                <form method="post" action="<?= e(url('/admin/salir')) ?>" data-logout>
                    <?= csrf_field() ?>
                    <button class="adm-btn adm-btn--ghost adm-btn--sm" type="submit"><?= icon('logout') ?><span>Salir</span></button>
                </form>
            </div>
        </header>

        <main class="adm-main" id="adm-main">
            <?php if (!empty($flash)): ?>
                <p class="adm-flash adm-flash--<?= e($flash['type'] === 'ok' ? 'ok' : 'error') ?>" role="status" data-flash="<?= e($flash['type'] === 'ok' ? 'success' : 'error') ?>">
                    <?= icon($flash['type'] === 'ok' ? 'check' : 'close') ?><span><?= e($flash['text']) ?></span>
                </p>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
<?php endif; ?>
</body>
</html>
