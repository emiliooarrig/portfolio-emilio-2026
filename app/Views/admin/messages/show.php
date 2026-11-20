<?php
/** @var array<string, mixed> $message */
/** @var array<string, string> $topics */
/** @var array<string, string> $statuses */
$id = (int) $message['id'];
$status = (string) $message['status'];
$topic = $topics[(string) $message['topic']] ?? (string) $message['topic'];
$reply = 'mailto:' . (string) $message['email'] . '?subject=' . rawurlencode('Re: tu mensaje en mi portafolio (' . $topic . ')');
?>
<a class="adm-link adm-back" href="<?= e(url('/admin/mensajes')) ?>"><?= icon('arrow-left') ?> Todos los mensajes</a>

<div class="show-grid">
    <article class="adm-card">
        <div class="card-h">
            <div>
                <h2 class="card-t"><?= e((string) $message['name']) ?></h2>
                <a class="adm-link" href="<?= e('mailto:' . (string) $message['email']) ?>"><?= e((string) $message['email']) ?></a>
            </div>
            <span class="chip chip--<?= e($status) ?>"><?= e($statuses[$status] ?? $status) ?></span>
        </div>

        <dl class="meta">
            <div><dt>Tema</dt><dd><?= e($topic) ?></dd></div>
            <div><dt>Recibido</dt><dd><time datetime="<?= e((string) $message['created_at']) ?>"><?= e(date_es((string) $message['created_at'], true)) ?></time></dd></div>
        </dl>

        <div class="msg-full"><?= nl2br(e((string) $message['message'])) ?></div>

        <div class="row-a">
            <a class="adm-btn" href="<?= e($reply) ?>"><?= icon('reply') ?><span>Responder por correo</span></a>
        </div>
    </article>

    <aside class="adm-card side-card">
        <h2 class="card-t">Estado</h2>
        <form class="status-form" method="post" action="<?= e(url('/admin/mensajes/estado')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <fieldset class="seg">
                <legend class="sr-only">Cambiar estado</legend>
                <?php foreach ($statuses as $value => $label): ?>
                    <label class="seg-o">
                        <input type="radio" name="status" value="<?= e($value) ?>"<?= $value === $status ? ' checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <button class="adm-btn adm-btn--ghost adm-btn--wide" type="submit"><span>Guardar estado</span></button>
        </form>

        <hr class="sep">

        <h2 class="card-t">Eliminar</h2>
        <p class="side-p">Se borra de la base de datos y no se puede recuperar. Si solo quieres sacarlo de la bandeja, archívalo.</p>
        <form method="post" action="<?= e(url('/admin/mensajes/eliminar')) ?>" data-confirm-title="¿Eliminar este mensaje?" data-confirm="Se borrará el mensaje de <?= e((string) $message['name']) ?>. No se puede deshacer." data-confirm-ok="Sí, eliminar">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <button class="adm-btn adm-btn--danger adm-btn--wide" type="submit"><?= icon('trash') ?><span>Eliminar mensaje</span></button>
        </form>
    </aside>
</div>
