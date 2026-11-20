<?php
/** @var string $content */
/** @var string|null $title */
/** @var string|null $description */
$title = $title ?? config('app.name');
$description = $description ?? '';
$appUrl = (string) config('app.url');
?>
<!doctype html>
<html lang="es" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="theme-color" content="#1F47E0">
    <meta name="color-scheme" content="light">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <?php if ($appUrl !== ''): ?>
    <meta property="og:url" content="<?= e($appUrl) ?>">
    <link rel="canonical" href="<?= e($appUrl) ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&amp;family=Plus+Jakarta+Sans:wght@400;500;600;700&amp;family=JetBrains+Mono:wght@400;500&amp;family=DM+Serif+Display:ital@0;1&amp;family=DM+Sans:wght@400;500;700&amp;display=swap">
    <link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">
    <script src="<?= e(asset('assets/js/alerts.js')) ?>" defer></script>
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</head>
<body>
    <a class="skip" href="#contenido">Saltar al contenido</a>
    <div class="prog" aria-hidden="true"></div>

    <?= App\Core\View::partial('nav') ?>

    <main id="contenido">
        <?= $content ?>
    </main>

    <?= App\Core\View::partial('footer') ?>
</body>
</html>
