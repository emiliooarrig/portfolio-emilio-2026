<?php
/** @var int|null $id */
/** @var array<string, string> $values */
/** @var array<string, string> $errors */
/** @var array<int, string> $categories */
$action = $id === null ? '/admin/stack/herramientas/nueva' : '/admin/stack/herramientas/editar';
$err = static fn (string $field): string => isset($errors[$field])
    ? '<p class="f-err" id="err-' . e($field) . '">' . e($errors[$field]) . '</p>'
    : '';
$attrs = static fn (string $field): string => isset($errors[$field])
    ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"'
    : '';
?>
<a class="adm-link adm-back" href="<?= e(url('/admin/stack')) ?>"><?= icon('arrow-left') ?> Todo el stack</a>

<form class="adm-card form-card" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($id !== null): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <?php if ($errors !== []): ?>
        <p class="adm-flash adm-flash--error" role="alert" data-flash="error"><?= icon('close') ?><span>Revisa los campos marcados.</span></p>
    <?php endif; ?>

    <div class="form-row">
        <div class="fld fld--grow">
            <label class="fl" for="st-name">Nombre</label>
            <input id="st-name" class="inp" type="text" name="name" value="<?= e($values['name']) ?>" maxlength="60" required placeholder="React, Docker, MySQL…"<?= $attrs('name') ?>>
            <?= $err('name') ?>
        </div>
    </div>

    <div class="fld">
        <label class="fl" for="st-cat">Categoría</label>
        <select id="st-cat" class="inp" name="category_id" required<?= $attrs('category_id') ?>>
            <option value="">Elige una categoría</option>
            <?php foreach ($categories as $catId => $catName): ?>
                <option value="<?= (int) $catId ?>"<?= $values['category_id'] === (string) $catId ? ' selected' : '' ?>><?= e($catName) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $err('category_id') ?>
    </div>

    <label class="check">
        <input type="checkbox" name="in_marquee" value="1"<?= $values['in_marquee'] === '1' ? ' checked' : '' ?>>
        <span>Mostrar en la cinta animada <small>La franja con texto grande que se desplaza bajo el título de la sección.</small></span>
    </label>

    <label class="check">
        <input type="checkbox" name="is_published" value="1"<?= $values['is_published'] === '1' ? ' checked' : '' ?>>
        <span>Publicada <small>Si la desmarcas, se guarda pero no aparece en el sitio.</small></span>
    </label>

    <div class="row-a">
        <button class="adm-btn" type="submit"><?= icon('check') ?><span><?= $id === null ? 'Agregar herramienta' : 'Guardar cambios' ?></span></button>
        <a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/stack')) ?>">Cancelar</a>
    </div>
</form>
