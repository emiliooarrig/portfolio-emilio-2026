<div class="auth">
    <a class="adm-brand ff-d" href="<?= e(url('/')) ?>">emilio<b>.</b>guzmán <span>Panel</span></a>

    <div class="auth-card">
        <h1 class="auth-h ff-d">El panel necesita la base de datos</h1>
        <p class="auth-p">Los mensajes y las certificaciones viven en MySQL. Para activar el panel:</p>
        <ol class="auth-steps">
            <li>Crea las tablas: <code>mysql -u root -p &lt; database/schema.sql</code></li>
            <li>En <code>.env</code> pon <code>DB_ENABLED=true</code> y tus credenciales <code>DB_*</code>.</li>
            <li>Crea tu usuario: <code>composer admin:create</code></li>
        </ol>
    </div>

    <a class="auth-back" href="<?= e(url('/')) ?>"><?= icon('arrow-left') ?> Volver al sitio</a>
</div>
