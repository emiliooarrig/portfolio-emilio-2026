<?php
/** @var array<string, mixed> $project */
/** @var array<string, mixed>|null $prev */
/** @var array<string, mixed>|null $next */

use App\Core\View;
use App\Models\Project;

$home = url('/');
$letters = ['situation' => 'S', 'task' => 'T', 'action' => 'A', 'result' => 'R', 'learning' => '+'];

$blocks = [];
foreach (Project::STAR as $key => $label) {
    $text = trim((string) ($project[$key] ?? ''));
    if ($text !== '') {
        $blocks[] = ['key' => $key, 'label' => $label, 'letter' => $letters[$key] ?? '', 'text' => $text];
    }
}
?>
<!-- Apertura del caso (solo CSS, ≤ 1.5 s): la cortina presenta el proyecto y se levanta. -->
<div class="pj-wipe" aria-hidden="true"></div>
<div class="pj-cur" aria-hidden="true">
    <div class="pj-cur-in">
        <span class="pj-cur-k ff-m"><?= e($project['kicker'] !== '' ? $project['kicker'] : 'Caso') ?></span>
        <span class="pj-cur-t ff-d"><span><?= e($project['title']) ?></span></span>
        <span class="pj-cur-star ff-d"><?php foreach ($letters as $l): ?><i><?= e($l) ?></i><?php endforeach; ?></span>
    </div>
</div>

<article class="sec pj">
    <div class="wrap">
        <a class="pj-back ff-m" href="<?= e($home . '#proyectos') ?>"><?= icon('arrow-left') ?><span>Todos los proyectos</span></a>

        <header class="pj-head">
            <div class="pj-info">
                <div class="pmeta pmeta--start">
                    <span class="ff-m"><?= e($project['kicker']) ?></span>
                    <?php if ($project['status'] !== ''): ?>
                        <span class="st<?= $project['is_live'] ? ' st-live' : '' ?>"><?= e($project['status']) ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="pj-title ff-d"><?= e($project['title']) ?></h1>
                <?php if ($project['subtitle'] !== ''): ?>
                    <p class="ps pj-sub"><?= e($project['subtitle']) ?></p>
                <?php endif; ?>
                <?php if ($project['description'] !== ''): ?>
                    <p class="pd pj-desc"><?= e($project['description']) ?></p>
                <?php endif; ?>
                <?php if ($project['stack'] !== []): ?>
                    <ul class="tags ff-m pj-tags">
                        <?php foreach ($project['stack'] as $tech): ?>
                            <li><?= e($tech) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($project['repo_url'] !== null): ?>
                    <div class="cta-row pj-repo">
                        <a class="btn btn-s" href="<?= e($project['repo_url']) ?>" target="_blank" rel="noopener noreferrer"><span>Repositorio</span><?= icon('arrow-up-right') ?></a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pj-vis pv" aria-hidden="true">
                <div class="pv-in">
                    <?= View::partial('visuals/' . $project['visual']) ?>
                </div>
            </div>
        </header>

        <div class="pj-star">
            <?php if ($blocks === []): ?>
                <p class="pj-empty">Pronto agregaré el detalle de este caso.</p>
            <?php endif; ?>
            <?php foreach ($blocks as $i => $b): ?>
                <section class="pj-b rv" aria-labelledby="pj-<?= e($b['key']) ?>">
                    <div class="pj-k">
                        <span class="pj-l ff-d" aria-hidden="true"><?= e($b['letter']) ?></span>
                        <h2 id="pj-<?= e($b['key']) ?>" class="pj-h ff-m"><span class="pj-n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><?= e($b['label']) ?></h2>
                    </div>
                    <div class="pj-t"><?= nl2br(e($b['text'])) ?></div>
                </section>
            <?php endforeach; ?>
        </div>

        <aside class="pj-end rv" aria-label="Siguientes pasos">
            <div>
                <h2 class="pj-cta-t ff-d">¿Tienes algo parecido en mente?</h2>
                <p class="pj-cta-p">Cuéntame el problema y vemos cómo resolverlo.</p>
            </div>
            <a class="btn btn-p" href="<?= e($home . '#contacto') ?>"><span>Platiquemos de algo similar</span><?= icon('arrow-right') ?></a>
        </aside>

        <?php if ($prev !== null || $next !== null): ?>
            <nav class="pj-nav" aria-label="Otros proyectos">
                <?php if ($prev !== null): ?>
                    <a class="pj-pn pj-pn--prev" href="<?= e(url('/proyectos/' . $prev['slug'])) ?>" rel="prev">
                        <span class="pj-pl ff-m"><?= icon('arrow-left') ?>Anterior</span>
                        <span class="pj-pt ff-d"><?= e($prev['title']) ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($next !== null): ?>
                    <a class="pj-pn pj-pn--next" href="<?= e(url('/proyectos/' . $next['slug'])) ?>" rel="next">
                        <span class="pj-pl ff-m">Siguiente<?= icon('arrow-right') ?></span>
                        <span class="pj-pt ff-d"><?= e($next['title']) ?></span>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</article>
