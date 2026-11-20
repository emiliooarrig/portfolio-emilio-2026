<?php
$links = [
    '#sobre'           => 'Sobre mí',
    '#stack'           => 'Stack',
    '#proyectos'       => 'Proyectos',
    '#fovea'           => 'Fovea',
    '#experiencia'     => 'Experiencia',
    '#certificaciones' => 'Certificaciones',
];
?>
<header class="nav">
    <div class="wrap nav-in">
        <a class="logo ff-d" href="#top" aria-label="Emilio Guzmán, inicio">emilio<b>.</b>guzmán</a>

        <nav class="nav-links" aria-label="Principal">
            <?php foreach ($links as $href => $label): ?>
                <a class="nl" href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="nav-r">
            <a class="btn btn-p nav-cta" href="#contacto"><span>Hablemos</span></a>
            <button class="menu-b" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="mnav" data-menu-toggle>
                <?= icon('menu') ?>
            </button>
        </div>
    </div>

    <nav id="mnav" class="mnav" aria-label="Menú móvil" hidden>
        <?php foreach ($links as $href => $label): ?>
            <a href="<?= e($href) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="#contacto">Contacto</a>
    </nav>
</header>
