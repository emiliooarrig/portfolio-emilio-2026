<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\StackCategory;
use App\Models\StackTool;
use App\Validation\StackCategoryRequest;
use App\Validation\StackToolRequest;

/**
 * Stack tecnológico: categorías y sus herramientas.
 */
final class StackController extends AdminController
{
    /** GET /admin/stack */
    public function index(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->render('stack/index', 'Stack', 'stack', [
            'categories' => StackCategory::allWithTools(),
            'icons'      => StackCategory::ICONS,
        ]);
    }

    // ─── Categorías ──────────────────────────────────────────

    /** GET /admin/stack/categorias/nueva */
    public function createCategory(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->categoryForm('Nueva categoría', null, [
            'name' => '', 'icon' => 'code', 'sort_order' => (string) StackCategory::nextOrder(), 'is_published' => '1',
        ]);
    }

    /** POST /admin/stack/categorias/nueva */
    public function storeCategory(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $form = new StackCategoryRequest($request);

        if (!$form->passes()) {
            $this->categoryForm('Nueva categoría', null, $form->old(), $form->errors());
            return;
        }

        $data = $form->validated();
        StackCategory::create($data);
        $this->flash('ok', 'Categoría «' . $data['name'] . '» creada. Ya puedes agregarle herramientas.');
        Response::redirect(url('/admin/stack'));
    }

    /** GET /admin/stack/categorias/editar?id= */
    public function editCategory(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $category = StackCategory::find($request->id());

        if ($category === null) {
            $this->notFound('Categoría');
            return;
        }

        $this->categoryForm('Editar categoría', (int) $category['id'], [
            'name'         => (string) $category['name'],
            'icon'         => (string) $category['icon'],
            'sort_order'   => (string) $category['sort_order'],
            'is_published' => (string) (int) $category['is_published'],
        ]);
    }

    /** POST /admin/stack/categorias/editar */
    public function updateCategory(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $id = $request->id();

        if (StackCategory::find($id) === null) {
            $this->notFound('Categoría');
            return;
        }

        $form = new StackCategoryRequest($request, $id);

        if (!$form->passes()) {
            $this->categoryForm('Editar categoría', $id, $form->old(), $form->errors());
            return;
        }

        StackCategory::update($id, $form->validated());
        $this->flash('ok', 'Categoría guardada.');
        Response::redirect(url('/admin/stack'));
    }

    /** POST /admin/stack/categorias/eliminar — también borra sus herramientas. */
    public function destroyCategory(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $deleted = StackCategory::delete($request->id());
        $this->flash($deleted ? 'ok' : 'error', $deleted ? 'Categoría eliminada junto con sus herramientas.' : 'La categoría ya no existe.');
        Response::redirect(url('/admin/stack'));
    }

    // ─── Herramientas ────────────────────────────────────────

    /** GET /admin/stack/herramientas/nueva?categoria= */
    public function createTool(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $categoryId = $request->id('categoria');

        $this->toolForm('Nueva herramienta', null, [
            'category_id'  => $categoryId > 0 ? (string) $categoryId : '',
            'name'         => '',
            'in_marquee'   => '0',
            'sort_order'   => $categoryId > 0 ? (string) StackTool::nextOrder($categoryId) : '10',
            'is_published' => '1',
        ]);
    }

    /**
     * POST /admin/stack/herramientas/nueva
     * Con quick=1 viene del campo "Agregar" de cada tarjeta: vuelve a la lista.
     */
    public function storeTool(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $quick = $request->string('quick') === '1';
        $form = new StackToolRequest($request, 0, $quick);

        if (!$form->passes()) {
            if ($quick) {
                $this->flash('error', $form->firstError());
                Response::redirect(url('/admin/stack') . '#cat-' . (int) $request->string('category_id'));
                return;
            }
            $this->toolForm('Nueva herramienta', null, $form->old(), $form->errors());
            return;
        }

        $data = $form->validated();
        StackTool::create($data);
        $this->flash('ok', '«' . $data['name'] . '» agregada.');
        Response::redirect(url('/admin/stack') . '#cat-' . $data['category_id']);
    }

    /** GET /admin/stack/herramientas/editar?id= */
    public function editTool(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $tool = StackTool::find($request->id());

        if ($tool === null) {
            $this->notFound('Herramienta');
            return;
        }

        $this->toolForm('Editar herramienta', (int) $tool['id'], [
            'category_id'  => (string) $tool['category_id'],
            'name'         => (string) $tool['name'],
            'in_marquee'   => (string) (int) $tool['in_marquee'],
            'sort_order'   => (string) $tool['sort_order'],
            'is_published' => (string) (int) $tool['is_published'],
        ]);
    }

    /** POST /admin/stack/herramientas/editar */
    public function updateTool(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $id = $request->id();

        if (StackTool::find($id) === null) {
            $this->notFound('Herramienta');
            return;
        }

        $form = new StackToolRequest($request, $id);

        if (!$form->passes()) {
            $this->toolForm('Editar herramienta', $id, $form->old(), $form->errors());
            return;
        }

        $data = $form->validated();
        StackTool::update($id, $data);
        $this->flash('ok', 'Herramienta guardada.');
        Response::redirect(url('/admin/stack') . '#cat-' . $data['category_id']);
    }

    /** POST /admin/stack/herramientas/eliminar */
    public function destroyTool(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $tool = StackTool::find($request->id());
        $deleted = $tool !== null && StackTool::delete((int) $tool['id']);
        $this->flash($deleted ? 'ok' : 'error', $deleted ? '«' . $tool['name'] . '» eliminada.' : 'La herramienta ya no existe.');
        Response::redirect(url('/admin/stack') . ($tool !== null ? '#cat-' . (int) $tool['category_id'] : ''));
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $errors
     */
    private function categoryForm(string $title, ?int $id, array $values, array $errors = []): void
    {
        $this->render('stack/category-form', $title, 'stack', [
            'id'     => $id,
            'values' => $values,
            'errors' => $errors,
            'icons'  => StackCategory::ICONS,
        ], $errors === [] ? 200 : 422);
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $errors
     */
    private function toolForm(string $title, ?int $id, array $values, array $errors = []): void
    {
        $categories = StackCategory::options();

        if ($categories === []) {
            $this->flash('error', 'Primero crea una categoría.');
            Response::redirect(url('/admin/stack/categorias/nueva'));
            return;
        }

        $this->render('stack/tool-form', $title, 'stack', [
            'id'         => $id,
            'values'     => $values,
            'errors'     => $errors,
            'categories' => $categories,
        ], $errors === [] ? 200 : 422);
    }
}
