<?php
/** @var int|null $id */
/** @var array<string, string> $values */
/** @var array<string, string> $errors */
/** @var array<string, string> $icons */
$action = $id === null ? '/admin/certificaciones/nueva' : '/admin/certificaciones/editar';
$err = static fn (string $field): string => isset($errors[$field])
    ? '<p class="f-err" id="err-' . e($field) . '">' . e($errors[$field]) . '</p>'
    : '';
$attrs = static fn (string $field): string => isset($errors[$field])
    ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"'
    : '';
?>
<a class="adm-link adm-back" href="<?= e(url('/admin/certificaciones')) ?>"><?= icon('arrow-left') ?> Todas las certificaciones</a>

<form class="adm-card form-card" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($id !== null): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <?php if ($errors !== []): ?>
        <p class="adm-flash adm-flash--error" role="alert" data-flash="error"><?= icon('close') ?><span>Revisa los campos marcados.</span></p>
    <?php endif; ?>

    <div class="fld">
        <label class="fl" for="c-title">Nombre de la certificación</label>
        <input id="c-title" class="inp" type="text" name="title" value="<?= e($values['title']) ?>" maxlength="200" required<?= $attrs('title') ?>>
        <?= $err('title') ?>
    </div>

    <div class="form-row">
        <div class="fld fld--grow">
            <label class="fl" for="c-issuer">Emisor <small>(opcional)</small></label>
            <input id="c-issuer" class="inp" type="text" name="issuer" value="<?= e($values['issuer']) ?>" maxlength="160" placeholder="Oracle Academy, Coursera, Universidad…"<?= $attrs('issuer') ?>>
            <?= $err('issuer') ?>
        </div>
        <div class="fld fld--year">
            <label class="fl" for="c-year">Año <small>(opcional)</small></label>
            <input id="c-year" class="inp" type="text" name="year" value="<?= e($values['year']) ?>" inputmode="numeric" maxlength="4" placeholder="<?= date('Y') ?>"<?= $attrs('year') ?>>
            <?= $err('year') ?>
        </div>
    </div>

    <div class="form-row">
        <div class="fld fld--grow">
            <label class="fl" for="c-status">Estado <small>(opcional)</small></label>
            <input id="c-status" class="inp" type="text" name="status" value="<?= e($values['status']) ?>" maxlength="60" list="c-status-list" placeholder="Vigente, En curso…"<?= $attrs('status') ?>>
            <datalist id="c-status-list"><option value="Vigente"><option value="En curso"><option value="Por concluir"><option value="Concluida"></datalist>
            <?= $err('status') ?>
        </div>
        <div class="fld fld--year">
            <label class="fl" for="c-order">Orden</label>
            <input id="c-order" class="inp" type="text" name="sort_order" value="<?= e($values['sort_order']) ?>" inputmode="numeric" maxlength="6"<?= $attrs('sort_order') ?>>
            <?= $err('sort_order') ?>
        </div>
    </div>

    <div class="fld">
        <label class="fl" for="c-url">Enlace de verificación <small>(opcional)</small></label>
        <input id="c-url" class="inp" type="url" name="url" value="<?= e($values['url']) ?>" maxlength="500" placeholder="https://"<?= $attrs('url') ?>>
        <p class="f-hint">Si lo dejas vacío, la tarjeta no muestra “Ver credencial”.</p>
        <?= $err('url') ?>
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
        <span>Publicada <small>Si la desmarcas, se guarda pero no aparece en el sitio.</small></span>
    </label>

    <div class="row-a">
        <button class="adm-btn" type="submit"><?= icon('check') ?><span><?= $id === null ? 'Agregar certificación' : 'Guardar cambios' ?></span></button>
        <a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/certificaciones')) ?>">Cancelar</a>
    </div>
</form>
