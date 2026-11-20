<?php
$foveaUrl = (string) config('site.fovea_url');
$whatsapp = preg_replace('/\D+/', '', (string) config('site.fovea_whatsapp')) ?? '';
$waUrl = $whatsapp !== '' ? 'https://wa.me/' . $whatsapp : '';

$services = [
    ['title' => 'Gestión de redes sociales',            'note' => '',                                                     'range' => ''],
    ['title' => 'Automatización creativa',              'note' => 'WhatsApp Business y CRM',                              'range' => 'ar-34'],
    ['title' => 'Desarrollo web y sistemas a la medida', 'note' => '',                                                     'range' => 'ar-38'],
    ['title' => 'Dashboards de marca',                  'note' => '',                                                     'range' => 'ar-42'],
    ['title' => 'GEO + SEO',                            'note' => 'Que te encuentren en buscadores y en asistentes de IA', 'range' => 'ar-46'],
    ['title' => 'Motion graphics',                      'note' => '',                                                     'range' => 'ar-50'],
];

$marquee = ['Redes sociales', 'Automatización', 'Web a la medida', 'Dashboards', 'GEO + SEO', 'Motion graphics'];
?>
<section id="fovea" class="fov" aria-labelledby="fv-title">
    <div class="fov-wash" aria-hidden="true"></div>
    <div class="fov-orb" aria-hidden="true"></div>

    <div class="wrap fov-in">
        <div class="fov-top">
            <h2 id="fv-title" class="fov-k" aria-label="conoce: Fovea">
                <span class="fov-k1 rv" aria-hidden="true">conoce:</span>
                <span class="fov-k2" aria-hidden="true"><span class="fov-word">Fovea</span></span>
            </h2>
            <div class="eye rv-s" aria-hidden="true">
                <i class="eye-r r1"></i><i class="eye-r r2"></i><i class="eye-r r3"></i>
                <i class="eye-x"></i><i class="eye-y"></i>
                <div class="eye-iris"><div class="eye-pupil"></div></div>
            </div>
        </div>

        <!-- En escritorio app.js agrega .fov-pin-on: el contenido se fija en pantalla y el
             scroll revela los servicios uno a uno (ver scss/sections/_fovea.scss). -->
        <div class="fov-pin" data-fov-pin>
        <div class="fov-stick">
        <div class="fov-grid">
            <div>
                <p class="fov-lead rv">La agencia de marketing y tecnología que cofundé para PyMEs mexicanas: restaurantes, comercios y servicios locales que quieren verse bien y funcionar mejor.</p>
                <blockquote class="fov-q ff-s rv">“La fóvea es el punto del ojo donde la visión es más nítida. Eso le damos a cada marca: enfoque.”</blockquote>
                <div class="fov-role rv"><span class="fov-role-k">Mi rol</span><span>Cofundador y líder técnico: web, sistemas y automatización.</span></div>
                <div class="cta-row rv">
                    <a class="btn btn-v" href="<?= e($foveaUrl !== '' ? $foveaUrl : '#fovea') ?>"<?= $foveaUrl !== '' ? ' target="_blank" rel="noopener"' : '' ?>><span>Conoce Fovea Studio</span><?= icon('arrow-up-right') ?></a>
                    <a class="btn btn-vo" href="<?= e($waUrl !== '' ? $waUrl : '#fovea') ?>"<?= $waUrl !== '' ? ' target="_blank" rel="noopener"' : '' ?>><span>Escríbenos por WhatsApp</span></a>
                </div>
                <?php if ($foveaUrl === '' || $waUrl === ''): ?>
                    <p class="fov-url rv">
                        <?php if ($foveaUrl === ''): ?>Sitio: <span class="todo">[FOVEA_URL en .env]</span><?php endif; ?>
                        <?php if ($foveaUrl === '' && $waUrl === ''): ?> · <?php endif; ?>
                        <?php if ($waUrl === ''): ?>WhatsApp: <span class="todo">[FOVEA_WHATSAPP en .env]</span><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <div>
                <p class="fov-sl rv">Lo que hacemos</p>
                <ol class="svc">
                    <?php foreach ($services as $i => $service): ?>
                        <li class="rv <?= e($service['range']) ?>" data-fov-svc>
                            <span class="svc-n"><?= sprintf('%02d', $i + 1) ?></span>
                            <span class="svc-t"><?= e($service['title']) ?><?php if ($service['note'] !== ''): ?><small><?= e($service['note']) ?></small><?php endif; ?></span>
                            <?= icon('arrow-right') ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>

        <div class="fov-cue" aria-hidden="true">
            <span class="fov-mouse"><i></i></span>
            <span class="fov-cue-t">Sigue bajando para conocer lo que hacemos</span>
            <span class="fov-cue-n"><b data-fov-count>00</b> / <?= sprintf('%02d', count($services)) ?></span>
        </div>
        </div>
        </div>
    </div>

    <div class="mq-w fov-mq" aria-hidden="true">
        <div class="mq">
            <?php for ($copy = 0; $copy < 2; $copy++): ?>
                <div class="mq-g">
                    <?php foreach ($marquee as $item): ?>
                        <span><?= e($item) ?></span><b></b>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
