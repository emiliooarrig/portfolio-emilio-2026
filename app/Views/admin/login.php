<?php
/** @var string $error */
/** @var string $notice */
/** @var string $email */
/** @var bool $bye */
?>
<?php if ($bye): ?>
    <!-- Tras cerrar sesión: la cortina se levanta y aparece el login. -->
    <div class="curtain curtain--out" aria-hidden="true">
        <div class="curtain-in">
            <span class="curtain-brand ff-d">emilio<b>.</b>guzmán</span>
            <p class="curtain-t ff-d">Hasta pronto</p>
        </div>
    </div>
<?php endif; ?>

<div class="auth<?= $bye ? ' auth--after' : '' ?>">
    <a class="adm-brand ff-d" href="<?= e(url('/')) ?>">emilio<b>.</b>guzmán <span>Panel</span></a>

    <form class="auth-card<?= $error !== '' ? ' is-error' : '' ?>" method="post" action="<?= e(url('/admin/login')) ?>" novalidate data-login-form>
        <?= csrf_field() ?>
        <h1 class="auth-h ff-d">Inicia sesión</h1>
        <p class="auth-p">Acceso solo para administración del sitio.</p>

        <?php if ($error !== ''): ?>
            <p class="adm-flash adm-flash--error" role="alert" data-flash="error"><?= icon('close') ?><span><?= e($error) ?></span></p>
        <?php elseif ($notice !== ''): ?>
            <p class="adm-flash adm-flash--info" role="status" data-flash="info"><span><?= e($notice) ?></span></p>
        <?php endif; ?>

        <div class="fld">
            <label class="fl" for="l-email">Correo</label>
            <input id="l-email" class="inp" type="email" name="email" value="<?= e($email) ?>" autocomplete="username" maxlength="160" required<?= $email === '' ? ' autofocus' : '' ?>>
        </div>
        <div class="fld">
            <label class="fl" for="l-pass">Contraseña</label>
            <div class="inp-pw">
                <input id="l-pass" class="inp" type="password" name="password" autocomplete="current-password" required<?= $email !== '' ? ' autofocus' : '' ?>>
                <button class="pw-t" type="button" aria-controls="l-pass" aria-pressed="false" aria-label="Mostrar contraseña" data-pw-toggle data-requires-js>
                    <?= icon('eye', 'ic pw-show') ?><?= icon('eye-off', 'ic pw-hide') ?>
                </button>
            </div>
        </div>

        <button class="adm-btn adm-btn--wide" type="submit" data-login-submit>
            <span data-login-label>Entrar</span><?= icon('arrow-right') ?><i class="adm-spin" aria-hidden="true"></i>
        </button>
    </form>

    <a class="auth-back" href="<?= e(url('/')) ?>"><?= icon('arrow-left') ?> Volver al sitio</a>
</div>
