<?php
/** @var list<array<string, mixed>> $projects */
/** @var array<string, string> $filters */
?>
<section id="proyectos" class="sec off fov-near" aria-labelledby="pj-title">
    <div class="wrap">
        <div class="p-head">
            <div>
                <p class="lbl ff-m rv">04 — Proyectos</p>
                <h2 id="pj-title" class="h2 ff-d rv">Sistemas reales, con usuarios reales.</h2>
            </div>
            <div class="tabs rv" role="group" aria-label="Filtrar proyectos" data-requires-js>
                <?php foreach ($filters as $key => $label): ?>
                    <button class="tab<?= $key === 'all' ? ' is-on' : '' ?>" type="button" data-filter="<?= e($key) ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <p class="sr-only" aria-live="polite" data-filter-status></p>

        <div class="pgrid">
            <?php foreach ($projects as $p): ?>
                <article class="pc<?= $p['is_featured'] ? ' pc-feat' : '' ?>" data-cursor="Abrir" data-cats="<?= e(implode(' ', $p['categories'])) ?>" data-feat="<?= $p['is_featured'] ? '1' : '0' ?>">
                    <div class="pv" aria-hidden="true">
                        <div class="pv-in">
                            <?= App\Core\View::partial('visuals/' . $p['visual']) ?>
                        </div>
                    </div>
                    <div class="pb">
                        <div class="pmeta">
                            <span class="ff-m"><?= e($p['kicker']) ?></span>
                            <span class="st<?= $p['is_live'] ? ' st-live' : '' ?>"><?= e($p['status']) ?></span>
                        </div>
                        <h3 class="pt ff-d"><?= e($p['title']) ?></h3>
                        <p class="ps"><?= e($p['subtitle']) ?></p>
                        <p class="pd"><?= e($p['description']) ?></p>
                        <div class="pfoot">
                            <ul class="tags ff-m">
                                <?php foreach ($p['stack'] as $tech): ?>
                                    <li><?= e($tech) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <a class="more" href="<?= e(url('/proyectos/' . $p['slug'])) ?>" aria-label="Ver caso: <?= e($p['title']) ?>">
                                <?= icon('arrow-up-right') ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
