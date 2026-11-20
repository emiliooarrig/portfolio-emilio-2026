<?php
/** @var list<array<string, mixed>> $experience */
use App\Models\Experience;
?>
<div class="page-a">
    <p class="page-p">Se muestra en la sección <b>Experiencia</b> del sitio, en el orden de la columna “Orden”.</p>
    <a class="adm-btn" href="<?= e(url('/admin/experiencia/nueva')) ?>"><?= icon('plus') ?><span>Nueva experiencia</span></a>
</div>

<section class="adm-card adm-card--flush">
    <?php if ($experience === []): ?>
        <div class="adm-empty">
            <p>No hay experiencia registrada todavía.</p>
            <p class="side-p">Para cargar la de <code>data/experience.json</code> ejecuta <code>composer experience:import</code>.</p>
        </div>
    <?php else: ?>
        <div class="tbl-w">
            <table class="tbl">
                <thead>
                    <tr>
                        <th scope="col" class="num">Orden</th>
                        <th scope="col">Empresa y puesto</th>
                        <th scope="col">Periodo</th>
                        <th scope="col">Visible</th>
                        <th scope="col"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($experience as $job): ?>
                        <?php $period = Experience::period($job); ?>
                        <tr>
                            <td class="num ff-m"><?= (int) $job['sort_order'] ?></td>
                            <td>
                                <div class="cert-cell">
                                    <span class="cert-ic"><?= icon('briefcase') ?></span>
                                    <span>
                                        <b><?= e((string) $job['company']) ?></b>
                                        <small><?= (string) $job['role'] !== '' ? e((string) $job['role']) : '<span class="warn">Sin puesto</span>' ?><?= (string) $job['summary'] === '' ? ' · <span class="warn">sin descripción</span>' : '' ?></small>
                                    </span>
                                </div>
                            </td>
                            <td class="nowrap"><?= $period !== '' ? e($period) : '<span class="warn">Sin fechas</span>' ?></td>
                            <td>
                                <?php if ((int) $job['is_published'] === 1): ?>
                                    <span class="chip chip--respondido">Publicada</span>
                                <?php else: ?>
                                    <span class="chip chip--archivado">Oculta</span>
                                <?php endif; ?>
                            </td>
                            <td class="act">
                                <a class="adm-btn adm-btn--ghost adm-btn--sm" href="<?= e(url('/admin/experiencia/editar?id=' . (int) $job['id'])) ?>"><?= icon('edit') ?><span>Editar</span></a>
                                <form method="post" action="<?= e(url('/admin/experiencia/eliminar')) ?>" data-confirm-title="¿Eliminar esta experiencia?" data-confirm="«<?= e((string) $job['company']) ?>» dejará de aparecer en el sitio. No se puede deshacer." data-confirm-ok="Sí, eliminar">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $job['id'] ?>">
                                    <button class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--icon" type="submit" aria-label="Eliminar <?= e((string) $job['company']) ?>"><?= icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
