<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Experience;
use App\Validation\ExperienceRequest;

/**
 * Experiencia laboral de la sección pública.
 */
final class ExperienceController extends AdminController
{
    /** GET /admin/experiencia */
    public function index(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->render('experience/index', 'Experiencia', 'experience', [
            'experience' => Experience::all(),
        ]);
    }

    /** GET /admin/experiencia/nueva */
    public function create(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->form('Nueva experiencia', null, [
            'company' => '', 'role' => '', 'summary' => '',
            'start_year' => '', 'start_month' => '', 'end_year' => '', 'end_month' => '',
            'is_current' => '1', 'sort_order' => (string) Experience::nextOrder(), 'is_published' => '1',
        ]);
    }

    /** POST /admin/experiencia/nueva */
    public function store(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $form = new ExperienceRequest($request);

        if (!$form->passes()) {
            $this->form('Nueva experiencia', null, $form->old(), $form->errors());
            return;
        }

        Experience::create($form->validated());
        $this->flash('ok', 'Experiencia agregada.');
        Response::redirect(url('/admin/experiencia'));
    }

    /** GET /admin/experiencia/editar?id= */
    public function edit(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $job = Experience::find($request->id());

        if ($job === null) {
            $this->notFound('Experiencia');
            return;
        }

        $str = static fn (mixed $v): string => $v === null ? '' : (string) $v;

        $this->form('Editar experiencia', (int) $job['id'], [
            'company'      => (string) $job['company'],
            'role'         => (string) $job['role'],
            'summary'      => (string) $job['summary'],
            'start_year'   => $str($job['start_year']),
            'start_month'  => $str($job['start_month']),
            'end_year'     => $str($job['end_year']),
            'end_month'    => $str($job['end_month']),
            'is_current'   => (string) (int) $job['is_current'],
            'sort_order'   => (string) $job['sort_order'],
            'is_published' => (string) (int) $job['is_published'],
        ]);
    }

    /** POST /admin/experiencia/editar */
    public function update(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $id = $request->id();

        if (Experience::find($id) === null) {
            $this->notFound('Experiencia');
            return;
        }

        $form = new ExperienceRequest($request);

        if (!$form->passes()) {
            $this->form('Editar experiencia', $id, $form->old(), $form->errors());
            return;
        }

        Experience::update($id, $form->validated());
        $this->flash('ok', 'Cambios guardados.');
        Response::redirect(url('/admin/experiencia'));
    }

    /** POST /admin/experiencia/eliminar */
    public function destroy(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $deleted = Experience::delete($request->id());
        $this->flash($deleted ? 'ok' : 'error', $deleted ? 'Experiencia eliminada.' : 'La experiencia ya no existe.');
        Response::redirect(url('/admin/experiencia'));
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $errors
     */
    private function form(string $title, ?int $id, array $values, array $errors = []): void
    {
        $this->render('experience/form', $title, 'experience', [
            'id'     => $id,
            'values' => $values,
            'errors' => $errors,
            'months' => Experience::MONTHS,
        ], $errors === [] ? 200 : 422);
    }
}
