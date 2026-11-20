<?php
/** @var list<array{id: int, name: string, icon: string, tools: list<string>}> $stack */
/** @var list<string> $marquee */
// Categorías y herramientas vienen de MySQL (/admin/stack).
$ranges = ['', 'ar-36', 'ar-42', 'ar-48'];
?>
<section id="stack" class="sec ice" aria-labelledby="st-title">
    <div class="wrap st-head">
        <div>
            <p class="lbl ff-m rv">02 — Stack</p>
            <h2 id="st-title" class="h2 ff-d rv">Herramientas para construir, desplegar y cuidar.</h2>
        </div>
        <p class="lead rv">Elijo tecnología sobria y bien documentada: lo que el siguiente equipo pueda mantener sin mí.</p>
    </div>

    <?php if ($marquee !== []): ?>
        <div class="mq-w" aria-hidden="true">
            <div class="mq ff-d">
                <?php for ($copy = 0; $copy < 2; $copy++): ?>
                    <div class="mq-g">
                        <?php foreach ($marquee as $i => $item): ?>
                            <span<?= $i % 2 === 1 ? ' class="o"' : '' ?>><?= e($item) ?></span><b></b>
                        <?php endforeach; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($stack !== []): ?>
        <div class="wrap grp-grid<?= $marquee === [] ? ' grp-grid--top' : '' ?>">
            <?php foreach ($stack as $i => $group): ?>
                <article class="grp rv <?= e($ranges[$i % count($ranges)]) ?>">
                    <div class="grp-ic"><?= icon($group['icon']) ?></div>
                    <h3 class="ff-d"><?= e($group['name']) ?></h3>
                    <?php if ($group['tools'] !== []): ?>
                        <ul class="chips">
                            <?php foreach ($group['tools'] as $tool): ?>
                                <li><?= e($tool) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
