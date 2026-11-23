<?php
/** @var int|null $id */
/** @var array<string, string|list<string>> $values */
/** @var array<string, string> $errors */
use App\Models\Project;

$action = $id === null ? '/admin/proyectos/nueva' : '/admin/proyectos/editar';
$err = static fn (string $field): string => isset($errors[$field])
    ? '<p class="f-err" id="err-' . e($field) . '">' . e($errors[$field]) . '</p>'
    : '';
$attrs = static fn (string $field): string => isset($errors[$field])
    ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"'
    : '';
$val = static fn (string $field): string => is_string($values[$field] ?? null) ? $values[$field] : '';
$cats = is_array($values['categories'] ?? null) ? $values['categories'] : [];

$starHints = [
    'situation' => 'contexto y problema',
    'task'      => 'qué te tocaba resolver',
    'action'    => 'qué hiciste y cómo',
    'result'    => 'qué cambió',
    'learning'  => 'qué te llevas',
];
?>
<a class="adm-link adm-back" href="<?= e(url('/admin/proyectos')) ?>"><?= icon('arrow-left') ?> Todos los proyectos</a>

<form class="adm-card form-card" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($id !== null): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <?php if ($errors !== []): ?>
        <p class="adm-flash adm-flash--error" role="alert" data-flash="error"><?= icon('close') ?><span>Revisa los campos marcados.</span></p>
    <?php endif; ?>

    <fieldset class="fld fset">
        <legend class="fset-t">Tarjeta <small>Lo que se ve en el listado de proyectos.</small></legend>

        <div class="fld">
            <label class="fl" for="x-title">Título</label>
            <input id="x-title" class="inp" type="text" name="title" value="<?= e($val('title')) ?>" maxlength="120" required<?= $attrs('title') ?>>
            <?= $err('title') ?>
        </div>

        <div class="fld">
            <label class="fl" for="x-slug">Dirección <small>(opcional)</small></label>
            <input id="x-slug" class="inp" type="text" name="slug" value="<?= e($val('slug')) ?>" maxlength="80" placeholder="mi-proyecto" autocapitalize="off" spellcheck="false" aria-describedby="hint-slug<?= isset($errors['slug']) ? ' err-slug' : '' ?>"<?= isset($errors['slug']) ? ' aria-invalid="true"' : '' ?>>
            <p class="f-hint" id="hint-slug">Se genera del título si lo dejas vacío · /proyectos/<b><?= e($val('slug') !== '' ? $val('slug') : '…') ?></b></p>
            <?= $err('slug') ?>
        </div>

        <div class="fld">
            <label class="fl" for="x-subtitle">Subtítulo <small>(opcional)</small></label>
            <input id="x-subtitle" class="inp" type="text" name="subtitle" value="<?= e($val('subtitle')) ?>" maxlength="200"<?= $attrs('subtitle') ?>>
            <?= $err('subtitle') ?>
        </div>

        <div class="form-row">
            <div class="fld fld--grow">
                <label class="fl" for="x-kicker">Etiqueta <small>(opcional)</small></label>
                <input id="x-kicker" class="inp" type="text" name="kicker" value="<?= e($val('kicker')) ?>" maxlength="80" placeholder="Cliente · Full-stack"<?= $attrs('kicker') ?>>
                <?= $err('kicker') ?>
            </div>
            <div class="fld fld--grow">
                <label class="fl" for="x-status">Estado <small>(opcional)</small></label>
                <input id="x-status" class="inp" type="text" name="status" value="<?= e($val('status')) ?>" maxlength="60" placeholder="En producción"<?= $attrs('status') ?>>
                <?= $err('status') ?>
            </div>
        </div>

        <label class="check">
            <input type="checkbox" name="is_live" value="1"<?= $val('is_live') === '1' ? ' checked' : '' ?>>
            <span>En vivo <small>Muestra un punto verde junto al estado.</small></span>
        </label>

        <label class="check">
            <input type="checkbox" name="is_featured" value="1"<?= $val('is_featured') === '1' ? ' checked' : '' ?>>
            <span>Destacado <small>La tarjeta se muestra en tamaño grande.</small></span>
        </label>

        <fieldset class="fld period">
            <legend class="fl">Categorías <small>(para los filtros del sitio)</small></legend>
            <div class="check-grid">
                <?php foreach (Project::FILTERS as $key => $label): ?>
                    <?php if ($key === 'all') { continue; } ?>
                    <label class="check">
                        <input type="checkbox" name="categories[]" value="<?= e($key) ?>"<?= in_array($key, $cats, true) ? ' checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="fld">
            <label class="fl" for="x-visual">Ilustración</label>
            <select id="x-visual" class="inp" name="visual"<?= $attrs('visual') ?>>
                <?php foreach (Project::VISUALS as $key => $label): ?>
                    <option value="<?= e($key) ?>"<?= $val('visual') === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $err('visual') ?>
        </div>

        <div class="fld">
            <label class="fl" for="x-description">Descripción <small>(resumen de la tarjeta, máximo 400 caracteres)</small></label>
            <textarea id="x-description" class="inp" name="description" rows="3" maxlength="400"<?= $attrs('description') ?>><?= e($val('description')) ?></textarea>
            <?= $err('description') ?>
        </div>

        <div class="fld">
            <label class="fl" for="x-stack">Stack <small>(opcional, separado por comas)</small></label>
            <input id="x-stack" class="inp" type="text" name="stack" value="<?= e($val('stack')) ?>" placeholder="React, Node.js, MySQL"<?= $attrs('stack') ?>>
            <?= $err('stack') ?>
        </div>

        <div class="fld">
            <label class="fl" for="x-repo_url">Repositorio <small>(opcional)</small></label>
            <input id="x-repo_url" class="inp" type="url" name="repo_url" value="<?= e($val('repo_url')) ?>" maxlength="500" placeholder="https://github.com/usuario/proyecto"<?= $attrs('repo_url') ?>>
            <?= $err('repo_url') ?>
        </div>
    </fieldset>

    <fieldset class="fld fset">
        <legend class="fset-t">Caso (STAR) <small>Se muestra en la página del proyecto. Los saltos de línea se conservan.</small></legend>

        <?php foreach (Project::STAR as $key => $label): ?>
            <div class="fld">
                <label class="fl" for="x-<?= e($key) ?>"><?= e($label) ?> <small>(<?= e($starHints[$key] ?? '') ?>)</small></label>
                <textarea id="x-<?= e($key) ?>" class="inp inp--tall" name="<?= e($key) ?>" rows="6" maxlength="2000"<?= $attrs($key) ?>><?= e($val($key)) ?></textarea>
                <?= $err($key) ?>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <label class="check">
        <input type="checkbox" name="is_published" value="1"<?= $val('is_published') === '1' ? ' checked' : '' ?>>
        <span>Publicado <small>Si lo desmarcas, se guarda pero no aparece en el sitio.</small></span>
    </label>

    <div class="row-a">
        <button class="adm-btn" type="submit"><?= icon('check') ?><span><?= $id === null ? 'Agregar proyecto' : 'Guardar cambios' ?></span></button>
        <a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/proyectos')) ?>">Cancelar</a>
    </div>
</form>
