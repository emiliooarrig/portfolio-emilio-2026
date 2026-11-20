<?php
/** @var array{items: list<array<string, mixed>>, total: int, page: int, pages: int} $result */
/** @var array{status: string, topic: string, q: string} $filters */
/** @var array<string, string> $topics */
/** @var array<string, string> $statuses */
$filtered = $filters['status'] !== '' || $filters['topic'] !== '' || $filters['q'] !== '';
$pageUrl = static function (int $page) use ($filters): string {
    $query = array_filter(['estado' => $filters['status'], 'tema' => $filters['topic'], 'q' => $filters['q'], 'pagina' => $page > 1 ? (string) $page : '']);

    return url('/admin/mensajes' . ($query !== [] ? '?' . http_build_query($query) : ''));
};
?>
<form class="filters" method="get" action="<?= e(url('/admin/mensajes')) ?>" role="search">
    <div class="fld fld--grow">
        <label class="fl" for="f-q">Buscar</label>
        <span class="inp-ic"><?= icon('search') ?><input id="f-q" class="inp" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Nombre, correo o texto del mensaje" maxlength="100"></span>
    </div>
    <div class="fld">
        <label class="fl" for="f-estado">Estado</label>
        <select id="f-estado" class="inp" name="estado" data-autosubmit>
            <option value="">Todos</option>
            <?php foreach ($statuses as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $filters['status'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="fld">
        <label class="fl" for="f-tema">Tema</label>
        <select id="f-tema" class="inp" name="tema" data-autosubmit>
            <option value="">Todos</option>
            <?php foreach ($topics as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $filters['topic'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filters-a">
        <button class="adm-btn" type="submit"><span>Filtrar</span></button>
        <?php if ($filtered): ?><a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/mensajes')) ?>">Limpiar</a><?php endif; ?>
    </div>
</form>

<section class="adm-card">
    <div class="card-h">
        <h2 class="card-t"><?= $result['total'] ?> <?= $result['total'] === 1 ? 'mensaje' : 'mensajes' ?><?= $filtered ? ' con estos filtros' : '' ?></h2>
        <?php if ($result['pages'] > 1): ?><span class="card-s">Página <?= $result['page'] ?> de <?= $result['pages'] ?></span><?php endif; ?>
    </div>

    <?php if ($result['items'] === []): ?>
        <p class="adm-empty"><?= $filtered ? 'Ningún mensaje coincide con los filtros.' : 'Todavía no llega ningún mensaje del formulario de contacto.' ?></p>
    <?php else: ?>
        <ul class="msg-list">
            <?php foreach ($result['items'] as $m): ?>
                <li><?= App\Core\View::partial('admin-message-row', ['m' => $m, 'topics' => $topics, 'statuses' => $statuses]) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($result['pages'] > 1): ?>
        <nav class="pager" aria-label="Páginas">
            <?php if ($result['page'] > 1): ?>
                <a class="adm-btn adm-btn--ghost adm-btn--sm" href="<?= e($pageUrl($result['page'] - 1)) ?>"><?= icon('arrow-left') ?><span>Anterior</span></a>
            <?php endif; ?>
            <?php if ($result['page'] < $result['pages']): ?>
                <a class="adm-btn adm-btn--ghost adm-btn--sm" href="<?= e($pageUrl($result['page'] + 1)) ?>"><span>Siguiente</span><?= icon('arrow-right') ?></a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
