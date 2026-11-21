<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\Certification;
use App\Validation\CertificationRequest;

/**
 * Certificaciones de la sección pública.
 */
final class CertificationController extends AdminController
{
    /** GET /admin/certificaciones */
    public function index(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->render('certifications/index', 'Certificaciones', 'certifications', [
            'certifications' => Certification::all(),
            'icons'          => Certification::ICONS,
        ]);
    }

    /** GET /admin/certificaciones/nueva */
    public function create(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->form('Nueva certificación', null, [
            'title' => '', 'issuer' => '', 'year' => '', 'status' => 'Vigente', 'url' => '',
            'icon' => 'award', 'is_published' => '1',
        ]);
    }

    /** POST /admin/certificaciones/nueva */
    public function store(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $form = new CertificationRequest($request);

        if (!$form->passes()) {
            $this->form('Nueva certificación', null, $form->old(), $form->errors());
            return;
        }

        Certification::create($form->validated());
        $this->flash('ok', 'Certificación agregada.');
        Response::redirect(url('/admin/certificaciones'));
    }

    /** GET /admin/certificaciones/editar?id= */
    public function edit(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $cert = Certification::find($request->id());

        if ($cert === null) {
            $this->notFound('Certificación');
            return;
        }

        $this->form('Editar certificación', (int) $cert['id'], [
            'title'        => (string) $cert['title'],
            'issuer'       => (string) $cert['issuer'],
            'year'         => $cert['year'] !== null ? (string) $cert['year'] : '',
            'status'       => (string) $cert['status'],
            'url'          => (string) $cert['url'],
            'icon'         => (string) $cert['icon'],
            'is_published' => (string) (int) $cert['is_published'],
        ]);
    }

    /** POST /admin/certificaciones/editar */
    public function update(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $id = $request->id();

        if (Certification::find($id) === null) {
            $this->notFound('Certificación');
            return;
        }

        $form = new CertificationRequest($request);

        if (!$form->passes()) {
            $this->form('Editar certificación', $id, $form->old(), $form->errors());
            return;
        }

        Certification::update($id, $form->validated());
        $this->flash('ok', 'Cambios guardados.');
        Response::redirect(url('/admin/certificaciones'));
    }

    /** POST /admin/certificaciones/eliminar */
    public function destroy(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $deleted = Certification::delete($request->id());
        $this->flash($deleted ? 'ok' : 'error', $deleted ? 'Certificación eliminada.' : 'La certificación ya no existe.');
        Response::redirect(url('/admin/certificaciones'));
    }

    /** POST /admin/certificaciones/orden */
    public function order(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->reorder($request, 'certifications', url('/admin/certificaciones'));
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $errors
     */
    private function form(string $title, ?int $id, array $values, array $errors = []): void
    {
        $this->render('certifications/form', $title, 'certifications', [
            'id'     => $id,
            'values' => $values,
            'errors' => $errors,
            'icons'  => Certification::ICONS,
        ], $errors === [] ? 200 : 422);
    }
}
