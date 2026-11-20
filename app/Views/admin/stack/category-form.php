<?php
/** @var int|null $id */
/** @var array<string, string> $values */
/** @var array<string, string> $errors */
/** @var array<string, string> $icons */
$action = $id === null ? '/admin/stack/categorias/nueva' : '/admin/stack/categorias/editar';
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
            <label class="fl" for="sc-name">Nombre de la categoría</label>
            <input id="sc-name" class="inp" type="text" name="name" value="<?= e($values['name']) ?>" maxlength="80" required placeholder="Desarrollo, Datos, Seguridad…"<?= $attrs('name') ?>>
            <?= $err('name') ?>
        </div>
        <div class="fld fld--year">
            <label class="fl" for="sc-order">Orden</label>
            <input id="sc-order" class="inp" type="text" name="sort_order" value="<?= e($values['sort_order']) ?>" inputmode="numeric" maxlength="6"<?= $attrs('sort_order') ?>>
            <?= $err('sort_order') ?>
        </div>
    </div>

    <fieldset class="fld">
        <legend class="fl">Ícono</legend>
        <div class="icon-pick">
            <?php foreach ($icons as $value => $label): ?>
                <label class="icon-o">
                    <input type="radio" name="icon" value="<?= e($value) ?>"<?= $values['icon'] === $value ? ' checked' : '' ?>>
                    <span><?= icon($value) ?><small><?= e($label) ?></small></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?= $err('icon') ?>
    </fieldset>

    <label class="check">
        <input type="checkbox" name="is_published" value="1"<?= $values['is_published'] === '1' ? ' checked' : '' ?>>
        <span>Publicada <small>Si la desmarcas, la tarjeta y sus herramientas no aparecen en el sitio (tampoco en la cinta).</small></span>
    </label>

    <div class="row-a">
        <button class="adm-btn" type="submit"><?= icon('check') ?><span><?= $id === null ? 'Crear categoría' : 'Guardar cambios' ?></span></button>
        <a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/stack')) ?>">Cancelar</a>
    </div>
</form>
