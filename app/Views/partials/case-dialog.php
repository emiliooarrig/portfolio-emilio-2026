<?php
/** @var list<array<string, mixed>> $projects */
?>
<dialog id="case-dialog" class="case" aria-labelledby="case-title">
    <div class="m-p">
        <button class="m-x" type="button" aria-label="Cerrar" data-case-close><?= icon('close') ?></button>
        <div data-case-body></div>
    </div>
</dialog>

<?php foreach ($projects as $p): ?>
<template id="case-<?= e($p['id']) ?>">
    <div class="pmeta pmeta--start">
        <span class="ff-m"><?= e($p['kicker']) ?></span>
        <span class="st<?= $p['live'] ? ' st-live' : '' ?>"><?= e($p['status']) ?></span>
    </div>
    <h3 id="case-title" class="m-title ff-d"><?= e($p['title']) ?></h3>
    <p class="ps m-sub"><?= e($p['subtitle']) ?></p>
    <p class="pd m-desc"><?= e($p['description']) ?></p>
    <?php if ($p['care'] !== ''): ?>
        <p class="m-note"><?= e($p['care']) ?></p>
    <?php endif; ?>
    <ul class="tags ff-m m-tags">
        <?php foreach ($p['stack'] as $tech): ?>
            <li><?= e($tech) ?></li>
        <?php endforeach; ?>
    </ul>
    <div class="cta-row m-cta">
        <a class="btn btn-p" href="#contacto" data-case-close><span>Platiquemos de algo similar</span><?= icon('arrow-right') ?></a>
        <?php if ($p['repo'] !== null): ?>
            <a class="btn btn-s" href="<?= e($p['repo']) ?>" target="_blank" rel="noopener noreferrer"><span>Repositorio</span><?= icon('arrow-up-right') ?></a>
        <?php endif; ?>
    </div>
</template>
<?php endforeach; ?>
