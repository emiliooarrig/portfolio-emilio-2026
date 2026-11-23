<?php
/** @var list<array<string, mixed>> $projects */
use App\Models\Project;

$starDone = static function (array $project): int {
    $n = 0;
    foreach (array_keys(Project::STAR) as $field) {
        if (trim((string) $project[$field]) !== '') {
            $n++;
        }
    }

    return $n;
};
?>
<div class="page-a">
    <p class="page-p">Cada proyecto tiene su tarjeta en la sección <b>Proyectos</b> y su propia página con el caso. <span class="page-hint" data-requires-js>Arrastra desde <?= icon('grip') ?> para cambiar el orden; se guarda solo.</span></p>
    <a class="adm-btn" href="<?= e(url('/admin/proyectos/nueva')) ?>"><?= icon('plus') ?><span>Nuevo proyecto</span></a>
</div>

<section class="adm-card adm-card--flush">
    <?php if ($projects === []): ?>
        <div class="adm-empty">
            <p>No hay proyectos registrados todavía.</p>
            <p class="side-p">Para cargar los de <code>data/projects.json</code> ejecuta <code>composer projects:import</code>.</p>
        </div>
    <?php else: ?>
        <div class="tbl-w">
            <table class="tbl">
                <thead>
                    <tr>
                        <th scope="col" class="drag-c"><span class="sr-only">Orden</span></th>
                        <th scope="col">Proyecto</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Destacado</th>
                        <th scope="col">Visible</th>
                        <th scope="col"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody data-sort="<?= e(url('/admin/proyectos/orden')) ?>" data-sort-axis="y">
                    <?php foreach ($projects as $project): ?>
                        <?php
                        $done = $starDone($project);
                        $sub = (string) $project['kicker'] !== '' ? (string) $project['kicker'] : (string) $project['subtitle'];
                        $published = (bool) $project['is_published'];
                        ?>
                        <tr data-sort-id="<?= (int) $project['id'] ?>" data-sort-name="<?= e((string) $project['title']) ?>">
                            <td class="drag-c"><?= sort_controls('/admin/proyectos/orden', (int) $project['id'], (string) $project['title']) ?></td>
                            <td>
                                <div class="cert-cell">
                                    <span class="cert-ic"><?= icon('folder') ?></span>
                                    <span>
                                        <b><?= e((string) $project['title']) ?></b>
                                        <small>
                                            <?= $sub !== '' ? e($sub) : '<span class="warn">Sin subtítulo</span>' ?>
                                            <?= (string) $project['description'] === '' ? ' · <span class="warn">sin descripción</span>' : '' ?>
                                            <?= $done < count(Project::STAR) ? ' · <span class="warn">STAR ' . $done . '/' . count(Project::STAR) . '</span>' : '' ?>
                                        </small>
                                    </span>
                                </div>
                            </td>
                            <td class="nowrap">
                                <?php if ((string) $project['status'] !== ''): ?>
                                    <span class="chip chip--leido"><?php if ($project['is_live']): ?><span class="live-dot" aria-hidden="true"></span><?php endif; ?><?= e((string) $project['status']) ?></span>
                                <?php else: ?>
                                    <span class="warn">Sin estado</span>
                                <?php endif; ?>
                            </td>
                            <td class="nowrap"><?= $project['is_featured'] ? '<span class="chip chip--respondido">Destacado</span>' : '<span class="side-p">—</span>' ?></td>
                            <td>
                                <?php if ($published): ?>
                                    <span class="chip chip--respondido">Publicada</span>
                                <?php else: ?>
                                    <span class="chip chip--archivado">Oculta</span>
                                <?php endif; ?>
                            </td>
                            <td class="act">
                                <a class="adm-btn adm-btn--ghost adm-btn--sm" href="<?= e(url('/admin/proyectos/editar?id=' . (int) $project['id'])) ?>"><?= icon('edit') ?><span>Editar</span></a>
                                <?php if ($published): ?>
                                    <a class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--icon" href="<?= e(url('/proyectos/' . (string) $project['slug'])) ?>" target="_blank" rel="noopener" aria-label="Ver «<?= e((string) $project['title']) ?>» en el sitio (se abre en otra pestaña)"><?= icon('external') ?></a>
                                <?php endif; ?>
                                <form method="post" action="<?= e(url('/admin/proyectos/eliminar')) ?>" data-confirm-title="¿Eliminar este proyecto?" data-confirm="«<?= e((string) $project['title']) ?>» dejará de aparecer en el sitio. No se puede deshacer." data-confirm-ok="Sí, eliminar">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $project['id'] ?>">
                                    <button class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--icon" type="submit" aria-label="Eliminar <?= e((string) $project['title']) ?>"><?= icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
