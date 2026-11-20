<?php
/** @var list<array<string, mixed>> $experience */
?>
<section id="experiencia" class="sec dark fov-near" aria-labelledby="ex-title">
    <div class="wrap exp">
        <div class="exp-h">
            <p class="lbl ff-m rv">05 — Experiencia</p>
            <h2 id="ex-title" class="h2 ff-d rv">Donde he construido y cuidado sistemas.</h2>
            <p class="lead exp-lead rv">Entre la industria, el emprendimiento y los clientes directos: el mismo enfoque de resolver y acompañar.</p>
            <div class="exp-cta rv">
                <a class="btn btn-w" href="<?= e(asset((string) config('site.cv'))) ?>" download><span>CV completo</span><?= icon('download') ?></a>
            </div>
        </div>
        <div class="tl-w">
            <div class="tl-track" aria-hidden="true"><div class="tl-fill draw-y"></div></div>
            <ol class="tl-list">
                <?php foreach ($experience as $job): ?>
                    <?php $period = App\Models\Experience::period($job); ?>
                    <li class="tl-i rv">
                        <?php if ($period !== ''): ?><p class="tl-d ff-m"><?= e($period) ?></p><?php endif; ?>
                        <h3 class="ff-d"><?= e((string) $job['company']) ?></h3>
                        <?php if ((string) $job['role'] !== ''): ?><p class="tl-r"><?= e((string) $job['role']) ?></p><?php endif; ?>
                        <?php if ((string) $job['summary'] !== ''): ?><p class="tl-t"><?= e((string) $job['summary']) ?></p><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</section>
