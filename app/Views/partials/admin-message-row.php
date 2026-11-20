<?php
/** @var array<string, mixed> $m */
/** @var array<string, string> $topics */
/** @var array<string, string> $statuses */
$status = (string) $m['status'];
?>
<a class="msg<?= $status === 'nuevo' ? ' is-new' : '' ?>" href="<?= e(url('/admin/mensajes/ver?id=' . (int) $m['id'])) ?>">
    <span class="msg-who">
        <b><?= e((string) $m['name']) ?></b>
        <small><?= e((string) $m['email']) ?></small>
    </span>
    <span class="msg-body">
        <span class="msg-topic"><?= e($topics[(string) $m['topic']] ?? (string) $m['topic']) ?></span>
        <span class="msg-ex"><?= e((string) $m['excerpt']) ?></span>
    </span>
    <span class="msg-meta">
        <span class="chip chip--<?= e($status) ?>"><?= e($statuses[$status] ?? $status) ?></span>
        <time datetime="<?= e((string) $m['created_at']) ?>"><?= e(date_es((string) $m['created_at'], true)) ?></time>
    </span>
</a>
