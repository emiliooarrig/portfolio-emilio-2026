<?php
/** @var list<array<string, mixed>> $certifications */
/** @var array<string, string> $icons */
?>
<div class="page-a">
    <p class="page-p">Se muestran en la sección <b>Certificaciones</b> del sitio en este mismo orden. <span class="page-hint" data-requires-js>Arrastra desde <?= icon('grip') ?> para cambiar el orden; se guarda solo.</span></p>
    <a class="adm-btn" href="<?= e(url('/admin/certificaciones/nueva')) ?>"><?= icon('plus') ?><span>Nueva certificación</span></a>
</div>

<section class="adm-card adm-card--flush">
    <?php if ($certifications === []): ?>
        <div class="adm-empty">
            <p>No hay certificaciones todavía.</p>
            <p class="side-p">Para cargar las de <code>data/certifications.json</code> ejecuta <code>composer certs:import</code>.</p>
        </div>
    <?php else: ?>
        <div class="tbl-w">
            <table class="tbl">
                <thead>
                    <tr>
                        <th scope="col" class="drag-c"><span class="sr-only">Orden</span></th>
                        <th scope="col">Certificación</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Visible</th>
                        <th scope="col"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody data-sort="<?= e(url('/admin/certificaciones/orden')) ?>" data-sort-axis="y">
                    <?php foreach ($certifications as $c): ?>
                        <tr data-sort-id="<?= (int) $c['id'] ?>" data-sort-name="<?= e((string) $c['title']) ?>">
                            <td class="drag-c"><?= sort_controls('/admin/certificaciones/orden', (int) $c['id'], (string) $c['title']) ?></td>
                            <td>
                                <div class="cert-cell">
                                    <span class="cert-ic"><?= icon((string) $c['icon']) ?></span>
                                    <span>
                                        <b><?= e((string) $c['title']) ?></b>
                                        <small><?= e(implode(' · ', array_filter([(string) $c['issuer'], $c['year'] !== null ? (string) $c['year'] : ''], static fn (string $v): bool => $v !== ''))) ?: '—' ?></small>
                                    </span>
                                </div>
                            </td>
                            <td><?= e((string) $c['status']) ?: '—' ?></td>
                            <td>
                                <?php if ((int) $c['is_published'] === 1): ?>
                                    <span class="chip chip--respondido">Publicada</span>
                                <?php else: ?>
                                    <span class="chip chip--archivado">Oculta</span>
                                <?php endif; ?>
                            </td>
                            <td class="act">
                                <a class="adm-btn adm-btn--ghost adm-btn--sm" href="<?= e(url('/admin/certificaciones/editar?id=' . (int) $c['id'])) ?>"><?= icon('edit') ?><span>Editar</span></a>
                                <form method="post" action="<?= e(url('/admin/certificaciones/eliminar')) ?>" data-confirm-title="¿Eliminar esta certificación?" data-confirm="«<?= e((string) $c['title']) ?>» dejará de aparecer en el sitio. No se puede deshacer." data-confirm-ok="Sí, eliminar">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                    <button class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--icon" type="submit" aria-label="Eliminar <?= e((string) $c['title']) ?>"><?= icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
