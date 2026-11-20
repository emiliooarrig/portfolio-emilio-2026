<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\CertificationController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\ExperienceController;
use App\Controllers\Admin\MessageController;
use App\Controllers\Admin\StackController;
use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Core\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->post('/contacto', [ContactController::class, 'store']);

    // ─── Panel de administración ────────────────────────────
    $router->get('/admin/login', [AuthController::class, 'show']);
    $router->post('/admin/login', [AuthController::class, 'login']);
    $router->post('/admin/salir', [AuthController::class, 'logout']);

    $router->get('/admin', [DashboardController::class, 'index']);

    $router->get('/admin/mensajes', [MessageController::class, 'index']);
    $router->get('/admin/mensajes/ver', [MessageController::class, 'show']);
    $router->post('/admin/mensajes/estado', [MessageController::class, 'status']);
    $router->post('/admin/mensajes/eliminar', [MessageController::class, 'destroy']);

    $router->get('/admin/certificaciones', [CertificationController::class, 'index']);
    $router->get('/admin/certificaciones/nueva', [CertificationController::class, 'create']);
    $router->post('/admin/certificaciones/nueva', [CertificationController::class, 'store']);
    $router->get('/admin/certificaciones/editar', [CertificationController::class, 'edit']);
    $router->post('/admin/certificaciones/editar', [CertificationController::class, 'update']);
    $router->post('/admin/certificaciones/eliminar', [CertificationController::class, 'destroy']);

    $router->get('/admin/stack', [StackController::class, 'index']);
    $router->get('/admin/stack/categorias/nueva', [StackController::class, 'createCategory']);
    $router->post('/admin/stack/categorias/nueva', [StackController::class, 'storeCategory']);
    $router->get('/admin/stack/categorias/editar', [StackController::class, 'editCategory']);
    $router->post('/admin/stack/categorias/editar', [StackController::class, 'updateCategory']);
    $router->post('/admin/stack/categorias/eliminar', [StackController::class, 'destroyCategory']);
    $router->get('/admin/stack/herramientas/nueva', [StackController::class, 'createTool']);
    $router->post('/admin/stack/herramientas/nueva', [StackController::class, 'storeTool']);
    $router->get('/admin/stack/herramientas/editar', [StackController::class, 'editTool']);
    $router->post('/admin/stack/herramientas/editar', [StackController::class, 'updateTool']);
    $router->post('/admin/stack/herramientas/eliminar', [StackController::class, 'destroyTool']);

    $router->get('/admin/experiencia', [ExperienceController::class, 'index']);
    $router->get('/admin/experiencia/nueva', [ExperienceController::class, 'create']);
    $router->post('/admin/experiencia/nueva', [ExperienceController::class, 'store']);
    $router->get('/admin/experiencia/editar', [ExperienceController::class, 'edit']);
    $router->post('/admin/experiencia/editar', [ExperienceController::class, 'update']);
    $router->post('/admin/experiencia/eliminar', [ExperienceController::class, 'destroy']);
};
