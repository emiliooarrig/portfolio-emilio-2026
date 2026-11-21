<?php
/** @var list<array<string, mixed>> $categories */
/** @var array<string, string> $icons */
?>
<div class="page-a">
    <p class="page-p">Se muestra en la sección <b>Stack</b> del sitio. Las herramientas con <span class="mq-dot" aria-hidden="true"></span> también salen en la cinta animada. <span class="page-hint" data-requires-js>Arrastra desde <?= icon('grip') ?> para ordenar categorías y herramientas.</span></p>
    <div class="page-btns">
        <?php if ($categories !== []): ?>
            <a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/stack/herramientas/nueva')) ?>"><?= icon('plus') ?><span>Herramienta</span></a>
        <?php endif; ?>
        <a class="adm-btn" href="<?= e(url('/admin/stack/categorias/nueva')) ?>"><?= icon('plus') ?><span>Nueva categoría</span></a>
    </div>
</div>

<?php if ($categories === []): ?>
    <section class="adm-card adm-empty">
        <p>No hay categorías todavía.</p>
        <p class="side-p">Para cargar el stack inicial ejecuta <code>composer db:seed</code>, o crea la primera categoría.</p>
    </section>
<?php else: ?>
    <div class="stack-grid" data-sort="<?= e(url('/admin/stack/categorias/orden')) ?>">
        <?php foreach ($categories as $cat): ?>
            <?php
            $catId = (int) $cat['id'];
            $tools = $cat['tools'];
            $count = count($tools);
            ?>
            <section class="adm-card stack-card<?= (int) $cat['is_published'] === 1 ? '' : ' is-hidden' ?>" id="cat-<?= $catId ?>" aria-labelledby="cat-h-<?= $catId ?>" data-sort-id="<?= $catId ?>" data-sort-name="<?= e((string) $cat['name']) ?>">
                <header class="stack-h">
                    <div class="stack-sort"><?= sort_controls('/admin/stack/categorias/orden', $catId, 'la categoría ' . (string) $cat['name']) ?></div>
                    <span class="cert-ic"><?= icon((string) $cat['icon']) ?></span>
                    <div class="stack-ht">
                        <h2 class="card-t" id="cat-h-<?= $catId ?>"><?= e((string) $cat['name']) ?></h2>
                        <span class="card-s">
                            <?= $count ?> <?= $count === 1 ? 'herramienta' : 'herramientas' ?>
                            <?php if ((int) $cat['is_published'] !== 1): ?> · <span class="chip chip--archivado">Oculta</span><?php endif; ?>
                        </span>
                    </div>
                    <div class="act">
                        <a class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--icon" href="<?= e(url('/admin/stack/categorias/editar?id=' . $catId)) ?>" aria-label="Editar categoría <?= e((string) $cat['name']) ?>"><?= icon('edit') ?></a>
                        <form method="post" action="<?= e(url('/admin/stack/categorias/eliminar')) ?>"
                              data-confirm-title="¿Eliminar «<?= e((string) $cat['name']) ?>»?"
                              data-confirm="<?= $count > 0 ? 'También se borrarán sus ' . $count . ' herramientas. ' : '' ?>No se puede deshacer."
                              data-confirm-ok="Sí, eliminar">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $catId ?>">
                            <button class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--icon" type="submit" aria-label="Eliminar categoría <?= e((string) $cat['name']) ?>"><?= icon('trash') ?></button>
                        </form>
                    </div>
                </header>

                <?php if ($tools === []): ?>
                    <p class="side-p">Sin herramientas. Agrega la primera abajo.</p>
                <?php else: ?>
                    <ul class="tool-list" data-sort="<?= e(url('/admin/stack/herramientas/orden')) ?>" data-sort-params="category_id=<?= $catId ?>">
                        <?php foreach ($tools as $tool): ?>
                            <?php $hidden = (int) $tool['is_published'] !== 1; ?>
                            <li class="tool<?= $hidden ? ' is-hidden' : '' ?>" data-sort-id="<?= (int) $tool['id'] ?>" data-sort-name="<?= e((string) $tool['name']) ?>">
                                <?= sort_controls('/admin/stack/herramientas/orden', (int) $tool['id'], (string) $tool['name'], ['category_id' => $catId], true) ?>
                                <a class="tool-n" href="<?= e(url('/admin/stack/herramientas/editar?id=' . (int) $tool['id'])) ?>" title="Editar">
                                    <?php if ((int) $tool['in_marquee'] === 1): ?><span class="mq-dot" title="En la cinta animada"></span><?php endif; ?>
                                    <?= e((string) $tool['name']) ?>
                                    <?php if ($hidden): ?><span class="sr-only">(oculta)</span><?php endif; ?>
                                </a>
                                <form method="post" action="<?= e(url('/admin/stack/herramientas/eliminar')) ?>"
                                      data-confirm-title="¿Quitar «<?= e((string) $tool['name']) ?>»?"
                                      data-confirm="Dejará de aparecer en el sitio. No se puede deshacer."
                                      data-confirm-ok="Sí, quitar">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $tool['id'] ?>">
                                    <button class="tool-x" type="submit" aria-label="Eliminar <?= e((string) $tool['name']) ?>"><?= icon('close') ?></button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <form class="quick" method="post" action="<?= e(url('/admin/stack/herramientas/nueva')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="quick" value="1">
                    <input type="hidden" name="category_id" value="<?= $catId ?>">
                    <label class="sr-only" for="q-<?= $catId ?>">Nueva herramienta en <?= e((string) $cat['name']) ?></label>
                    <input id="q-<?= $catId ?>" class="inp" type="text" name="name" maxlength="60" placeholder="Agregar herramienta…" required>
                    <button class="adm-btn adm-btn--sm" type="submit"><?= icon('plus') ?><span>Agregar</span></button>
                </form>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
