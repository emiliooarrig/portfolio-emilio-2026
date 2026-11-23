<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Project;
use App\Validation\ProjectRequest;

/**
 * Proyectos del sitio: tarjeta del listado y caso en formato STAR.
 */
final class ProjectController extends AdminController
{
    /** GET /admin/proyectos */
    public function index(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->render('projects/index', 'Proyectos', 'projects', [
            'projects' => Project::all(),
        ]);
    }

    /** GET /admin/proyectos/nueva */
    public function create(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->form('Nuevo proyecto', null, [
            'title' => '', 'slug' => '', 'subtitle' => '', 'kicker' => '', 'status' => '',
            'is_live' => '0', 'is_featured' => '0', 'visual' => 'dash', 'categories' => [],
            'description' => '', 'stack' => '', 'repo_url' => '',
            'situation' => '', 'task' => '', 'action' => '', 'result' => '', 'learning' => '',
            'is_published' => '1',
        ]);
    }

    /** POST /admin/proyectos/nueva */
    public function store(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $form = new ProjectRequest($request);

        if (!$form->passes()) {
            $this->form('Nuevo proyecto', null, $form->old(), $form->errors());
            return;
        }

        Project::create($form->validated());
        $this->flash('ok', 'Proyecto agregado.');
        Response::redirect(url('/admin/proyectos'));
    }

    /** GET /admin/proyectos/editar?id= */
    public function edit(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $project = Project::find($request->id());

        if ($project === null) {
            $this->notFound('Proyecto');
            return;
        }

        $this->form('Editar proyecto', (int) $project['id'], [
            'title'        => (string) $project['title'],
            'slug'         => (string) $project['slug'],
            'subtitle'     => (string) $project['subtitle'],
            'kicker'       => (string) $project['kicker'],
            'status'       => (string) $project['status'],
            'is_live'      => $project['is_live'] ? '1' : '0',
            'is_featured'  => $project['is_featured'] ? '1' : '0',
            'visual'       => (string) $project['visual'],
            'categories'   => array_values(array_map('strval', $project['categories'])),
            'description'  => (string) $project['description'],
            'stack'        => implode(', ', $project['stack']),
            'repo_url'     => (string) ($project['repo_url'] ?? ''),
            'situation'    => (string) $project['situation'],
            'task'         => (string) $project['task'],
            'action'       => (string) $project['action'],
            'result'       => (string) $project['result'],
            'learning'     => (string) $project['learning'],
            'is_published' => $project['is_published'] ? '1' : '0',
        ]);
    }

    /** POST /admin/proyectos/editar */
    public function update(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $id = $request->id();

        if (Project::find($id) === null) {
            $this->notFound('Proyecto');
            return;
        }

        $form = new ProjectRequest($request, $id);

        if (!$form->passes()) {
            $this->form('Editar proyecto', $id, $form->old(), $form->errors());
            return;
        }

        Project::update($id, $form->validated());
        $this->flash('ok', 'Cambios guardados.');
        Response::redirect(url('/admin/proyectos'));
    }

    /** POST /admin/proyectos/eliminar */
    public function destroy(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $deleted = Project::delete($request->id());
        $this->flash($deleted ? 'ok' : 'error', $deleted ? 'Proyecto eliminado.' : 'El proyecto ya no existe.');
        Response::redirect(url('/admin/proyectos'));
    }

    /** POST /admin/proyectos/orden */
    public function order(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->reorder($request, 'projects', url('/admin/proyectos'));
    }

    /**
     * @param array<string, string|list<string>> $values
     * @param array<string, string> $errors
     */
    private function form(string $title, ?int $id, array $values, array $errors = []): void
    {
        $this->render('projects/form', $title, 'projects', [
            'id'     => $id,
            'values' => $values,
            'errors' => $errors,
        ], $errors === [] ? 200 : 422);
    }
}
