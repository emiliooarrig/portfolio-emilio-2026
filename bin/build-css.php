<?php

declare(strict_types=1);

/*
 | Compila con scssphp (Composer):
 |   scss/main.scss  → public/assets/css/main.css   (sitio)
 |   scss/admin.scss → public/assets/css/admin.css  (panel /admin)
 |
 |   composer build:css          compila una vez (comprimido)
 |   composer watch:css          recompila al guardar cualquier .scss
 |   php bin/build-css.php --dev  sin comprimir, con source map
 */

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

if (!class_exists(Compiler::class)) {
    fwrite(STDERR, "Falta scssphp. Ejecuta: composer install\n");
    exit(1);
}

$args = array_slice($argv, 1);
$watch = in_array('--watch', $args, true);
$dev = in_array('--dev', $args, true) || $watch;

$entries = ['main', 'admin'];

$compile = static function (string $name) use ($root, $dev): bool {
    $input = $root . '/scss/' . $name . '.scss';
    $output = $root . '/public/assets/css/' . $name . '.css';

    $compiler = new Compiler();
    $compiler->setImportPaths($root . '/scss');
    $compiler->setOutputStyle($dev ? OutputStyle::EXPANDED : OutputStyle::COMPRESSED);

    if ($dev) {
        $compiler->setSourceMap(Compiler::SOURCE_MAP_INLINE);
        $compiler->setSourceMapOptions([
            'sourceMapBasepath' => $root,
            'sourceRoot'        => '/',
        ]);
    }

    try {
        $start = microtime(true);
        $result = $compiler->compileString((string) file_get_contents($input), $input);

        if (!is_dir(dirname($output))) {
            mkdir(dirname($output), 0775, true);
        }

        file_put_contents($output, $result->getCss());
        printf("✓ CSS compilado (%s, %.0f ms) → public/assets/css/%s.css\n", number_format(filesize($output) / 1024, 1) . ' KB', (microtime(true) - $start) * 1000, $name);

        return true;
    } catch (Throwable $e) {
        fwrite(STDERR, '✗ Error de SCSS (' . $name . '): ' . $e->getMessage() . "\n");

        return false;
    }
};

$build = static function () use ($entries, $compile): bool {
    $ok = true;
    foreach ($entries as $name) {
        $ok = $compile($name) && $ok;
    }

    return $ok;
};

$snapshot = static function () use ($root): string {
    $state = '';
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/scss', FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if ($file->getExtension() === 'scss') {
            $state .= $file->getPathname() . ':' . $file->getMTime() . ';';
        }
    }

    return md5($state);
};

$ok = $build();

if (!$watch) {
    exit($ok ? 0 : 1);
}

echo "Observando scss/ … (Ctrl+C para salir)\n";
$last = $snapshot();

while (true) {
    usleep(400_000);
    clearstatcache();
    $now = $snapshot();
    if ($now !== $last) {
        $last = $now;
        $build();
    }
}
