<?php
/** @var int|null $id */
/** @var array<string, string> $values */
/** @var array<string, string> $errors */
/** @var list<string> $months */
$action = $id === null ? '/admin/experiencia/nueva' : '/admin/experiencia/editar';
$err = static fn (string $field): string => isset($errors[$field])
    ? '<p class="f-err" id="err-' . e($field) . '">' . e($errors[$field]) . '</p>'
    : '';
$attrs = static fn (string $field): string => isset($errors[$field])
    ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"'
    : '';
$monthSelect = static function (string $name, string $label) use ($values, $months, $attrs, $err): string {
    $html = '<div class="fld fld--month"><label class="fl" for="x-' . $name . '">' . e($label) . '</label>'
        . '<select id="x-' . $name . '" class="inp" name="' . $name . '"' . $attrs($name) . '><option value="">—</option>';
    foreach ($months as $i => $m) {
        $v = (string) ($i + 1);
        $html .= '<option value="' . $v . '"' . ($values[$name] === $v ? ' selected' : '') . '>' . e(ucfirst($m)) . '</option>';
    }

    return $html . '</select>' . $err($name) . '</div>';
};
?>
<a class="adm-link adm-back" href="<?= e(url('/admin/experiencia')) ?>"><?= icon('arrow-left') ?> Toda la experiencia</a>

<form class="adm-card form-card" method="post" action="<?= e(url($action)) ?>" novalidate>
    <?= csrf_field() ?>
    <?php if ($id !== null): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <?php if ($errors !== []): ?>
        <p class="adm-flash adm-flash--error" role="alert" data-flash="error"><?= icon('close') ?><span>Revisa los campos marcados.</span></p>
    <?php endif; ?>

    <div class="fld">
        <label class="fl" for="x-company">Empresa, proyecto o cliente</label>
        <input id="x-company" class="inp" type="text" name="company" value="<?= e($values['company']) ?>" maxlength="120" required<?= $attrs('company') ?>>
        <?= $err('company') ?>
    </div>

    <div class="fld">
        <label class="fl" for="x-role">Puesto <small>(opcional)</small></label>
        <input id="x-role" class="inp" type="text" name="role" value="<?= e($values['role']) ?>" maxlength="160" placeholder="Desarrollador full-stack, Cofundador · Líder técnico…"<?= $attrs('role') ?>>
        <?= $err('role') ?>
    </div>

    <div class="fld">
        <label class="fl" for="x-summary">Descripción <small>(opcional, máximo 600 caracteres)</small></label>
        <textarea id="x-summary" class="inp" name="summary" rows="4" maxlength="600" placeholder="Una o dos líneas sobre tu rol y los sistemas que construiste o atiendes."<?= $attrs('summary') ?>><?= e($values['summary']) ?></textarea>
        <?= $err('summary') ?>
    </div>

    <fieldset class="fld period">
        <legend class="fl">Inicio <small>(opcional)</small></legend>
        <div class="form-row">
            <?= $monthSelect('start_month', 'Mes') ?>
            <div class="fld fld--year">
                <label class="fl" for="x-start_year">Año</label>
                <input id="x-start_year" class="inp" type="text" name="start_year" value="<?= e($values['start_year']) ?>" inputmode="numeric" maxlength="4" placeholder="<?= date('Y') - 2 ?>"<?= $attrs('start_year') ?>>
                <?= $err('start_year') ?>
            </div>
        </div>
    </fieldset>

    <fieldset class="fld period">
        <legend class="fl">Fin</legend>
        <label class="check">
            <input type="checkbox" name="is_current" value="1" data-toggles="#x-end"<?= $values['is_current'] === '1' ? ' checked' : '' ?>>
            <span>Trabajo aquí actualmente <small>En el sitio se muestra como “hoy”.</small></span>
        </label>
        <div class="form-row" id="x-end">
            <?= $monthSelect('end_month', 'Mes') ?>
            <div class="fld fld--year">
                <label class="fl" for="x-end_year">Año</label>
                <input id="x-end_year" class="inp" type="text" name="end_year" value="<?= e($values['end_year']) ?>" inputmode="numeric" maxlength="4" placeholder="<?= date('Y') ?>"<?= $attrs('end_year') ?>>
                <?= $err('end_year') ?>
            </div>
        </div>
    </fieldset>

    <div class="fld fld--year">
        <label class="fl" for="x-order">Orden</label>
        <input id="x-order" class="inp" type="text" name="sort_order" value="<?= e($values['sort_order']) ?>" inputmode="numeric" maxlength="6"<?= $attrs('sort_order') ?>>
        <p class="f-hint">Menor = más arriba.</p>
        <?= $err('sort_order') ?>
    </div>

    <label class="check">
        <input type="checkbox" name="is_published" value="1"<?= $values['is_published'] === '1' ? ' checked' : '' ?>>
        <span>Publicada <small>Si la desmarcas, se guarda pero no aparece en el sitio.</small></span>
    </label>

    <div class="row-a">
        <button class="adm-btn" type="submit"><?= icon('check') ?><span><?= $id === null ? 'Agregar experiencia' : 'Guardar cambios' ?></span></button>
        <a class="adm-btn adm-btn--ghost" href="<?= e(url('/admin/experiencia')) ?>">Cancelar</a>
    </div>
</form>
