<section id="sobre" class="sec sec--about" aria-labelledby="ab-title">
    <p class="ab-word ff-d px" aria-hidden="true">Sobre mí</p>
    <div class="wrap about">
        <div class="ph rv-l">
            <div class="ph-f" aria-hidden="true"></div>
            <div class="ph-i">
                <?php $photo = 'assets/img/emilio.webp'; ?>
                <?php if (is_file(App\Core\App::publicPath($photo))): ?>
                    <img src="<?= e(asset($photo)) ?>" alt="Emilio Guzmán" width="420" height="470" loading="lazy" decoding="async">
                <?php else: ?>
                    <?= icon('user') ?>
                    <span class="todo">[TU FOTO · public/assets/img/emilio.webp]</span>
                <?php endif; ?>
            </div>
            <span class="ph-tag ff-m">Base: México · remoto</span>
        </div>
        <div>
            <p class="lbl ff-m rv">01 — Sobre mí</p>
            <h2 id="ab-title" class="h2 ff-d rv">Ingeniero de sistemas con mentalidad de soporte.</h2>
            <p class="lead ab-p rv">Estoy por graduarme de Ingeniería en Sistemas y TI en la Universidad Anáhuac. Mi parte favorita del software es la que casi nadie ve: el despliegue que no se cae, el checador que sigue registrando aunque falle la red, la documentación que alguien agradece meses después.</p>
            <p class="lead ab-p rv">Hoy trabajo en Nokia y soy cofundador técnico de Fovea Studio, una agencia de marketing y tecnología para PyMEs mexicanas, donde llevo todo el lado técnico.</p>
            <dl class="facts rv">
                <div><dt>Ahora</dt><dd>Nokia</dd></div>
                <div><dt>Cofundador</dt><dd>Fovea Studio</dd></div>
                <div><dt>Formación</dt><dd>Ing. en Sistemas y TI · Anáhuac</dd></div>
            </dl>
        </div>
    </div>
</section>
