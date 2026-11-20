<?php
/** @var array{total: int, new: int, week: int, by_status: array<string, int>, by_topic: array<string, int>, daily: array<string, int>} $stats */
/** @var list<array<string, mixed>> $latest */
/** @var int $certsTotal */
/** @var int $certsLive */
/** @var array<string, string> $topics */
/** @var array<string, string> $statuses */
$days = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
$dailyMax = max(1, ...array_values($stats['daily']));
$topicMax = max(1, ...array_values($stats['by_topic']));
// Altura/anchura en clases .v-0 … .v-100 (la CSP no permite estilos en línea).
$pct = static fn (int $n, int $max): int => $n > 0 ? max(4, (int) round($n / $max * 100)) : 0;
?>
<div class="kpis">
    <a class="kpi kpi--accent" href="<?= e(url('/admin/mensajes?estado=nuevo')) ?>">
        <span class="kpi-k">Sin leer</span>
        <span class="kpi-v ff-d"><?= $stats['new'] ?></span>
        <span class="kpi-s">Ver mensajes nuevos <?= icon('arrow-right') ?></span>
    </a>
    <div class="kpi">
        <span class="kpi-k">Últimos 7 días</span>
        <span class="kpi-v ff-d"><?= $stats['week'] ?></span>
        <span class="kpi-s"><?= $stats['week'] === 1 ? 'mensaje recibido' : 'mensajes recibidos' ?></span>
    </div>
    <div class="kpi">
        <span class="kpi-k">Total de mensajes</span>
        <span class="kpi-v ff-d"><?= $stats['total'] ?></span>
        <span class="kpi-s"><?= $stats['by_status']['respondido'] ?> respondidos · <?= $stats['by_status']['archivado'] ?> archivados</span>
    </div>
    <a class="kpi" href="<?= e(url('/admin/certificaciones')) ?>">
        <span class="kpi-k">Certificaciones</span>
        <span class="kpi-v ff-d"><?= $certsLive ?></span>
        <span class="kpi-s"><?= $certsLive ?> de <?= $certsTotal ?> publicadas <?= icon('arrow-right') ?></span>
    </a>
</div>

<div class="dash-grid">
    <section class="adm-card" aria-labelledby="ch-daily">
        <div class="card-h">
            <h2 id="ch-daily" class="card-t">Mensajes por día</h2>
            <span class="card-s">Últimos 14 días</span>
        </div>

        <div class="cols" aria-hidden="true">
            <span class="cols-max ff-m"><?= $dailyMax ?></span>
            <div class="cols-plot">
                <?php foreach ($stats['daily'] as $day => $n): ?>
                    <?php $ts = strtotime($day); ?>
                    <div class="col<?= $day === date('Y-m-d') ? ' is-today' : '' ?>">
                        <span class="col-bar v-<?= $pct($n, $dailyMax) ?>"></span>
                        <span class="tip"><b><?= $n ?></b> <?= $n === 1 ? 'mensaje' : 'mensajes' ?><br><?= e($days[(int) date('w', $ts)] . ' ' . date_es($day)) ?></span>
                        <span class="col-x ff-m"><?= date('j', $ts) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <table class="sr-only">
            <caption>Mensajes recibidos por día, últimos 14 días</caption>
            <thead><tr><th scope="col">Día</th><th scope="col">Mensajes</th></tr></thead>
            <tbody>
                <?php foreach ($stats['daily'] as $day => $n): ?>
                    <tr><td><?= e(date_es($day)) ?></td><td><?= $n ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="adm-card" aria-labelledby="ch-topic">
        <div class="card-h">
            <h2 id="ch-topic" class="card-t">Por tema</h2>
            <span class="card-s">Todos los mensajes</span>
        </div>

        <ul class="hbars">
            <?php foreach ($stats['by_topic'] as $topic => $n): ?>
                <li class="hbar">
                    <a class="hbar-l" href="<?= e(url('/admin/mensajes?tema=' . $topic)) ?>"><?= e($topics[$topic] ?? $topic) ?></a>
                    <span class="hbar-v ff-m"><?= $n ?></span>
                    <span class="hbar-track" aria-hidden="true"><span class="hbar-fill v-<?= $pct($n, $topicMax) ?>"></span></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>

<section class="adm-card" aria-labelledby="latest-h">
    <div class="card-h">
        <h2 id="latest-h" class="card-t">Últimos mensajes</h2>
        <a class="adm-link" href="<?= e(url('/admin/mensajes')) ?>">Ver todos <?= icon('arrow-right') ?></a>
    </div>

    <?php if ($latest === []): ?>
        <p class="adm-empty">Todavía no llega ningún mensaje del formulario de contacto.</p>
    <?php else: ?>
        <ul class="msg-list">
            <?php foreach ($latest as $m): ?>
                <li>
                    <?= App\Core\View::partial('admin-message-row', ['m' => $m, 'topics' => $topics, 'statuses' => $statuses]) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
