<?php
/** @var array<string, string> $topics */
/** @var bool $contactSent */
/** @var array<string, string> $contactErrors */
/** @var string $contactMessage */
/** @var array<string, string> $old */
$email = (string) config('site.public_email');
$linkedin = (string) config('site.linkedin');
$github = (string) config('site.github');
$response = (string) config('site.response_time');

$err = static fn (string $field): string => $contactErrors[$field] ?? '';
$val = static fn (string $field): string => (string) ($old[$field] ?? '');
$selectedTopic = $val('tema') !== '' ? $val('tema') : array_key_first($topics);

$invalid = static fn (string $field): string => isset($contactErrors[$field])
    ? ' aria-invalid="true"'
    : '';
?>
<section id="contacto" class="sec cobalt" aria-labelledby="co-title">
    <div class="co-ring" aria-hidden="true"></div>
    <div class="co-ring2" aria-hidden="true"></div>
    <div class="wrap ct">
        <div>
            <p class="lbl ff-m rv">07 — Contacto</p>
            <h2 id="co-title" class="ct-h ff-d rv">¿Tienes un sistema por construir, o uno por rescatar?</h2>
            <p class="ct-p rv">Cuéntame qué necesitas: un desarrollo nuevo, soporte para algo que ya existe o una vacante en tu equipo de TI.</p>
            <ul class="ct-list rv">
                <li>
                    <a class="ct-row" href="<?= e($email !== '' ? 'mailto:' . $email : '#contacto') ?>">
                        <span><span class="ct-k ff-m">Correo</span><span class="ct-v"><?= ph($email, '[PUBLIC_EMAIL en .env]') ?></span></span><?= icon('arrow-up-right') ?>
                    </a>
                </li>
                <li>
                    <a class="ct-row" href="<?= e($github) ?>" target="_blank" rel="noopener noreferrer">
                        <span><span class="ct-k ff-m">GitHub</span><span class="ct-v"><?= e(preg_replace('#^https?://#', '', $github)) ?></span></span><?= icon('arrow-up-right') ?>
                    </a>
                </li>
                <li>
                    <a class="ct-row" href="<?= e($linkedin !== '' ? $linkedin : '#contacto') ?>"<?= $linkedin !== '' ? ' target="_blank" rel="noopener noreferrer"' : '' ?>>
                        <span><span class="ct-k ff-m">LinkedIn</span><span class="ct-v"><?= $linkedin !== '' ? 'Ver perfil' : ph(null, '[LINKEDIN_URL en .env]') ?></span></span><?= icon('arrow-up-right') ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="rv-s">
            <form class="form" method="post" action="<?= e(url('/contacto')) ?>" novalidate data-contact-form<?= $contactSent ? ' hidden' : '' ?>>
                <?= csrf_field() ?>

                <!-- Campo trampa para bots: las personas no lo ven ni lo llenan. -->
                <div class="hp" aria-hidden="true">
                    <label for="f-website">No llenes este campo</label>
                    <input id="f-website" type="text" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="f2">
                    <div class="fld">
                        <label class="fl" for="f-name">Nombre</label>
                        <input id="f-name" class="inp" type="text" name="nombre" autocomplete="name" maxlength="80" required placeholder="¿Cómo te llamas?" value="<?= e($val('nombre')) ?>" aria-describedby="err-nombre"<?= $invalid('nombre') ?>>
                        <p class="f-err" id="err-nombre" data-error-for="nombre"><?= e($err('nombre')) ?></p>
                    </div>
                    <div class="fld">
                        <label class="fl" for="f-mail">Correo</label>
                        <input id="f-mail" class="inp" type="email" name="correo" autocomplete="email" maxlength="160" required placeholder="nombre@empresa.com" value="<?= e($val('correo')) ?>" aria-describedby="err-correo"<?= $invalid('correo') ?>>
                        <p class="f-err" id="err-correo" data-error-for="correo"><?= e($err('correo')) ?></p>
                    </div>
                </div>

                <fieldset class="fld fld-set">
                    <legend class="fl">¿En qué te ayudo?</legend>
                    <div class="topics">
                        <?php foreach ($topics as $value => $label): ?>
                            <label class="tp">
                                <input type="radio" name="tema" value="<?= e($value) ?>"<?= $value === $selectedTopic ? ' checked' : '' ?>>
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="f-err" data-error-for="tema"><?= e($err('tema')) ?></p>
                </fieldset>

                <div class="fld">
                    <label class="fl" for="f-msg">Mensaje</label>
                    <textarea id="f-msg" class="inp" name="mensaje" rows="5" minlength="10" maxlength="3000" required placeholder="Cuéntame del proyecto, el problema o la vacante." aria-describedby="err-mensaje"<?= $invalid('mensaje') ?>><?= e($val('mensaje')) ?></textarea>
                    <p class="f-err" id="err-mensaje" data-error-for="mensaje"><?= e($err('mensaje')) ?></p>
                </div>

                <p class="f-status" role="alert" data-form-status><?= e($contactMessage) ?></p>

                <div class="f-foot">
                    <p class="f-note">Respondo en <?= ph($response, '[RESPONSE_TIME en .env]') ?></p>
                    <button class="btn btn-p" type="submit">
                        <span class="btn-idle"><span>Enviar mensaje</span><?= icon('arrow-right') ?></span>
                        <span class="btn-busy" aria-hidden="true"><span class="spin"></span><span>Enviando…</span></span>
                    </button>
                </div>
            </form>

            <div class="form ok-wrap" role="status" data-contact-ok<?= $contactSent ? '' : ' hidden' ?>>
                <div class="ok">
                    <div class="ok-c"><?= icon('check') ?></div>
                    <h3 class="ff-d" tabindex="-1">Mensaje enviado.</h3>
                    <p>Gracias por escribir. Lo leo con calma y te respondo pronto con los siguientes pasos.</p>
                    <a class="btn btn-s" href="#contacto" data-contact-reset><span>Enviar otro mensaje</span></a>
                </div>
            </div>
        </div>
    </div>
</section>
