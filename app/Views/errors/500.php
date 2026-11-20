<section class="sec err">
    <div class="wrap">
        <p class="lbl ff-m">Error 500</p>
        <h1 class="h2 ff-d"><?= e($title ?? 'Algo salió mal') ?></h1>
        <p class="lead err-p">Ya quedó registrado y lo voy a revisar. Intenta de nuevo en unos minutos.</p>
        <div class="cta-row">
            <a class="btn btn-p" href="<?= e(url('/')) ?>"><span>Volver al inicio</span><?= icon('arrow-right') ?></a>
        </div>
    </div>
</section>
