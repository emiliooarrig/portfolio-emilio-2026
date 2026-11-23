<?php

declare(strict_types=1);

/*
 | Comandos del panel de administración.
 |   composer admin:create      crea un administrador (pide nombre, correo y contraseña)
 |   composer admin:password    cambia la contraseña de un administrador
 |   composer certs:import      carga data/certifications.json en la tabla certifications
 |   composer experience:import carga data/experience.json en la tabla experiences
 |   composer projects:import   carga data/projects.json en la tabla projects
 |   composer db:migrate        crea las tablas que falten (database/schema.sql) con la conexión del .env
 |   composer db:seed           carga database/seeds/*.sql en las tablas que estén vacías
 */

use App\Core\App;
use App\Core\Database;
use App\Models\Admin;
use App\Models\Certification;
use App\Models\Experience;
use App\Models\Project;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

App::boot($root);

$command = $argv[1] ?? '';

$out = static function (string $text): void { fwrite(STDOUT, $text . PHP_EOL); };
$fail = static function (string $text): never { fwrite(STDERR, '✗ ' . $text . PHP_EOL); exit(1); };

$ask = static function (string $label): string {
    fwrite(STDOUT, $label . ': ');

    return trim((string) fgets(STDIN));
};

// Contraseña sin eco en la terminal cuando el sistema lo permite.
$askSecret = static function (string $label) use ($ask): string {
    // Entrada redirigida (script o CI): se lee tal cual.
    if (function_exists('stream_isatty') && !stream_isatty(STDIN)) {
        return $ask($label);
    }

    if (DIRECTORY_SEPARATOR === '\\') {
        $ps = 'powershell -NoProfile -Command "$p = Read-Host -AsSecureString \'' . $label . '\'; '
            . '[Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"';
        $value = shell_exec($ps);

        return is_string($value) ? rtrim($value, "\r\n") : $ask($label);
    }

    fwrite(STDOUT, $label . ': ');
    @shell_exec('stty -echo');
    $value = trim((string) fgets(STDIN));
    @shell_exec('stty echo');
    fwrite(STDOUT, PHP_EOL);

    return $value;
};

$newPassword = static function () use ($askSecret, $fail): string {
    $password = $askSecret('Contraseña (mínimo ' . Admin::MIN_PASSWORD . ' caracteres)');

    if (mb_strlen($password) < Admin::MIN_PASSWORD) {
        $fail('La contraseña debe tener al menos ' . Admin::MIN_PASSWORD . ' caracteres.');
    }

    if ($askSecret('Repite la contraseña') !== $password) {
        $fail('Las contraseñas no coinciden.');
    }

    return $password;
};

// Sentencias de un archivo .sql. Cada una termina con ";" al final de una línea
// (un ";" dentro de un texto no corta). CREATE DATABASE y USE se omiten: la base la decide .env.
$sqlStatements = static function (string $file): array {
    $sql = (string) preg_replace('/^\s*--.*$/m', '', (string) file_get_contents($file));

    return array_values(array_filter(
        array_map('trim', preg_split('/;\s*$/m', $sql) ?: []),
        static fn (string $s): bool => $s !== '' && !preg_match('/^(CREATE\s+DATABASE|USE)\b/i', $s)
    ));
};

$commands = ['admin:create', 'admin:password', 'certs:import', 'experience:import', 'projects:import', 'db:migrate', 'db:seed'];

if (!in_array($command, $commands, true)) {
    $out('Uso: php bin/console.php [' . implode(' | ', $commands) . ']');
    exit($command === '' ? 0 : 1);
}

if (!Database::enabled()) {
    $fail('DB_ENABLED está en false. Configura la base de datos en .env y crea las tablas con database/schema.sql.');
}

try {
    Database::connection();
} catch (Throwable $e) {
    $hint = match (true) {
        str_contains($e->getMessage(), '[1045]') => (string) config('db.pass') === ''
            ? ' → DB_PASSWORD está vacío en .env (¿guardaste el archivo?).'
            : ' → Revisa DB_USERNAME y DB_PASSWORD en .env.',
        str_contains($e->getMessage(), '[1049]') => ' → La base no existe: crea las tablas con database/schema.sql.',
        str_contains($e->getMessage(), '[2002]') => ' → MySQL no responde en ' . config('db.host') . ':' . config('db.port') . '.',
        default => '',
    };
    $fail('No se pudo conectar a MySQL: ' . $e->getMessage() . $hint);
}

switch ($command) {
    case 'admin:create':
        $name = $ask('Nombre');
        $email = mb_strtolower($ask('Correo'));

        if ($name === '' || mb_strlen($name) > 80) {
            $fail('Escribe un nombre de hasta 80 caracteres.');
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 160) {
            $fail('Escribe un correo válido.');
        }
        if (Admin::findByEmail($email) !== null) {
            $fail('Ya existe un administrador con ese correo. Usa composer admin:password para cambiar su contraseña.');
        }

        Admin::create($name, $email, $newPassword());
        $out("✓ Administrador creado. Entra en /admin/login con {$email}.");
        break;

    case 'admin:password':
        $email = mb_strtolower($ask('Correo del administrador'));
        $admin = Admin::findByEmail($email);

        if ($admin === null) {
            $fail('No existe un administrador con ese correo.');
        }

        Admin::updatePassword((int) $admin['id'], $newPassword());
        $out('✓ Contraseña actualizada.');
        break;

    case 'db:migrate':
        // Ejecuta schema.sql sobre la base de DB_DATABASE. Todo es CREATE TABLE IF NOT EXISTS,
        // así que no toca las tablas ni los datos que ya existen.
        $pdo = Database::connection();
        $before = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($sqlStatements($root . '/database/schema.sql') as $statement) {
            $pdo->exec($statement);
        }

        $created = array_diff($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN), $before);
        $out($created === []
            ? '✓ El esquema ya estaba al día; no se creó ninguna tabla.'
            : '✓ Tablas creadas: ' . implode(', ', $created) . '.');
        break;

    case 'db:seed':
        // Cada seed solo corre si su tabla está vacía, para no duplicar ni pisar lo editado en el panel.
        $seeds = ['stack.sql' => 'stack_categories'];
        $pdo = Database::connection();

        foreach ($seeds as $file => $table) {
            if ((int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn() > 0) {
                $out("· {$file}: {$table} ya tiene datos; se omitió.");
                continue;
            }

            $pdo->beginTransaction();
            try {
                foreach ($sqlStatements($root . '/database/seeds/' . $file) as $statement) {
                    $pdo->exec($statement);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                $fail("{$file}: " . $e->getMessage());
            }

            $out("✓ {$file} cargado.");
        }
        break;

    case 'experience:import':
        if (Experience::count() > 0) {
            $fail('La tabla experiences ya tiene datos; no se importó nada para no duplicar.');
        }

        $items = Experience::fromJson();
        foreach ($items as $job) {
            Experience::create([
                'company'      => (string) $job['company'],
                'role'         => (string) $job['role'],
                'summary'      => (string) $job['summary'],
                'start_year'   => $job['start_year'],
                'start_month'  => $job['start_month'],
                'end_year'     => $job['end_year'],
                'end_month'    => $job['end_month'],
                'is_current'   => (bool) $job['is_current'],
                'sort_order'   => (int) $job['sort_order'],
                'is_published' => true,
            ]);
        }

        $out('✓ ' . count($items) . ' experiencias importadas desde data/experience.json.');
        break;

    case 'projects:import':
        if (Project::count() > 0) {
            $fail('La tabla projects ya tiene datos; no se importó nada para no duplicar.');
        }

        $items = Project::fromJson();
        foreach ($items as $p) {
            Project::create(array_diff_key($p, array_flip(['id', 'updated_at'])) + ['repo_url' => '']);
        }

        $out('✓ ' . count($items) . ' proyectos importados desde data/projects.json.');
        break;

    case 'certs:import':
        if (Certification::count() > 0) {
            $fail('La tabla certifications ya tiene datos; no se importó nada para no duplicar.');
        }

        $items = Certification::fromJson();
        foreach ($items as $c) {
            Certification::create([
                'title'        => (string) $c['title'],
                'issuer'       => (string) $c['issuer'],
                'year'         => $c['year'],
                'status'       => (string) $c['status'],
                'url'          => (string) $c['url'],
                'icon'         => (string) $c['icon'],
                'sort_order'   => (int) $c['sort_order'],
                'is_published' => true,
            ]);
        }

        $out('✓ ' . count($items) . ' certificaciones importadas desde data/certifications.json.');
        break;
}
