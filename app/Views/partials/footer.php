<?php
$linkedin = (string) config('site.linkedin');
?>
<footer class="foot">
    <div class="wrap">
        <div class="f-cols">
            <div>
                <a class="logo logo--light ff-d" href="#top">emilio<b>.</b>guzmán</a>
                <p class="f-about">Ingeniero en TI. Desarrollo, despliego y doy soporte a sistemas que la gente usa todos los días.</p>
            </div>
            <div>
                <h3 class="ff-m">Navegación</h3>
                <ul>
                    <li><a class="ul" href="#sobre">Sobre mí</a></li>
                    <li><a class="ul" href="#proyectos">Proyectos</a></li>
                    <li><a class="ul" href="#fovea">Fovea Studio</a></li>
                    <li><a class="ul" href="#experiencia">Experiencia</a></li>
                    <li><a class="ul" href="#certificaciones">Certificaciones</a></li>
                </ul>
            </div>
            <div>
                <h3 class="ff-m">En línea</h3>
                <ul>
                    <li><a class="ul" href="<?= e((string) config('site.github')) ?>" target="_blank" rel="noopener noreferrer">GitHub</a></li>
                    <?php if ($linkedin !== ''): ?>
                        <li><a class="ul" href="<?= e($linkedin) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a></li>
                    <?php endif; ?>
                    <li><a class="ul" href="<?= e(asset((string) config('site.cv'))) ?>" download>Descargar CV</a></li>
                </ul>
            </div>
            <div>
                <h3 class="ff-m">Estado</h3>
                <ul>
                    <li class="f-status-line"><span class="dot" aria-hidden="true"></span>Disponible para proyectos</li>
                    <li>México · remoto</li>
                </ul>
            </div>
        </div>
        <p class="f-word ff-d fw" aria-hidden="true">Emilio Guzmán</p>
        <div class="f-bot">
            <span>© <?= date('Y') ?> Emilio Guzmán · Diseñado y desarrollado a mano.</span>
            <a class="totop" href="#top">Volver arriba <?= icon('arrow-up') ?></a>
        </div>
    </div>
</footer>
