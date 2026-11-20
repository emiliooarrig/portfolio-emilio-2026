<?php
$steps = [
    ['n' => '01', 'title' => 'Escucho',   'range' => '',      'text' => 'Antes del código entiendo la operación: quién lo usa, en qué turno, qué duele hoy y qué reglas del negocio no se pueden romper.'],
    ['n' => '02', 'title' => 'Construyo', 'range' => 'ar-36', 'text' => 'Arquitectura clara, roles y permisos desde el inicio, y nada que el siguiente desarrollador no pueda leer.'],
    ['n' => '03', 'title' => 'Despliego', 'range' => 'ar-42', 'text' => 'Producción real: puertos, CORS, volúmenes y dominios resueltos uno por uno hasta que el sistema queda en pie.'],
    ['n' => '04', 'title' => 'Cuido',     'range' => 'ar-48', 'text' => 'Monitoreo, alertas, documentación versionada y soporte cercano a quien usa el sistema todos los días.'],
];
?>
<section class="sec proc-sec" aria-labelledby="pr-title" data-proc>
    <!-- En escritorio app.js agrega .proc-h: la sección se fija y el scroll vertical
         mueve los pasos en horizontal (ver scss/sections/_process.scss). -->
    <div class="proc-stick">
        <div class="wrap">
            <div class="pr-head">
                <div>
                    <p class="lbl ff-m rv">03 — Cómo trabajo</p>
                    <h2 id="pr-title" class="h2 ff-d rv">Care engineering: el software también se cuida.</h2>
                </div>
                <p class="lead rv">Un sistema no termina en el deploy. Termina cuando la gente lo usa sin miedo y alguien responde cuando algo falla.</p>
            </div>
        </div>
        <div class="proc-vp">
            <div class="wrap">
                <ol class="proc" data-proc-track>
                    <li class="proc-line draw-x" aria-hidden="true"></li>
                    <?php foreach ($steps as $step): ?>
                        <li class="step rv <?= e($step['range']) ?>" data-proc-step>
                            <div class="step-n ff-m" aria-hidden="true"><?= e($step['n']) ?></div>
                            <h3 class="ff-d"><?= e($step['title']) ?></h3>
                            <p><?= e($step['text']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <div class="wrap proc-meta" aria-hidden="true">
            <span class="proc-count ff-m"><b data-proc-count>01</b> / <?= sprintf('%02d', count($steps)) ?></span>
            <span class="proc-bar"><i data-proc-bar></i></span>
            <span class="proc-hint ff-m">Sigue bajando <?= icon('arrow-right') ?></span>
        </div>
    </div>
</section>
