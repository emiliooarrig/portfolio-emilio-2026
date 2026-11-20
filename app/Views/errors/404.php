<section class="sec err">
    <div class="wrap">
        <p class="lbl ff-m">Error 404</p>
        <h1 class="h2 ff-d"><?= e($title ?? 'Página no encontrada') ?></h1>
        <p class="lead err-p">Esta ruta no existe, pero el resto del sitio sí.</p>
        <div class="cta-row">
            <a class="btn btn-p" href="<?= e(url('/')) ?>"><span>Volver al inicio</span><?= icon('arrow-right') ?></a>
        </div>
    </div>
</section>
