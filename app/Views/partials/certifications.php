<?php
/** @var list<array<string, mixed>> $certifications */
$ranges = ['', 'ar-40', 'ar-45', 'ar-50'];
?>
<section id="certificaciones" class="sec" aria-labelledby="ce-title">
    <div class="wrap">
        <div class="pr-head">
            <div>
                <p class="lbl ff-m rv">06 — Certificaciones</p>
                <h2 id="ce-title" class="h2 ff-d rv">Formación que respalda el trabajo.</h2>
            </div>
            <p class="lead rv">Cada credencial enlaza a su verificación oficial.</p>
        </div>
        <div class="cert">
            <?php foreach ($certifications as $i => $c): ?>
                <?php
                $issuer = (string) $c['issuer'];
                $year = $c['year'] !== null ? (string) $c['year'] : '';
                $url = (string) $c['url'];
                ?>
                <article class="cc rv-s <?= e($ranges[$i % count($ranges)]) ?>">
                    <div class="cc-ic"><?= icon((string) $c['icon']) ?></div>
                    <?php if ($issuer !== '' || $year !== ''): ?>
                        <p class="cc-k ff-m"><?= e(implode(' · ', array_filter([$issuer, $year], static fn (string $v): bool => $v !== ''))) ?></p>
                    <?php endif; ?>
                    <h3 class="ff-d"><?= e((string) $c['title']) ?></h3>
                    <div class="cc-f">
                        <?php if ((string) $c['status'] !== ''): ?><span class="st"><?= e((string) $c['status']) ?></span><?php endif; ?>
                        <?php if ($url !== ''): ?>
                            <a class="ul" href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer">Ver credencial</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
