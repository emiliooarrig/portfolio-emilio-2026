<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Models\ContactMessage;

/**
 * Mensajes del formulario de contacto.
 */
final class MessageController extends AdminController
{
    /** GET /admin/mensajes?estado=&tema=&q=&pagina= */
    public function index(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $filters = [
            'status' => $request->query('estado'),
            'topic'  => $request->query('tema'),
            'q'      => mb_substr($request->query('q'), 0, 100),
        ];
        $page = ctype_digit($request->query('pagina')) ? (int) $request->query('pagina') : 1;

        $this->render('messages/index', 'Mensajes', 'messages', [
            'result'   => ContactMessage::paginate($filters, $page),
            'filters'  => $filters,
            'topics'   => ContactMessage::TOPICS,
            'statuses' => ContactMessage::STATUSES,
        ]);
    }

    /** GET /admin/mensajes/ver?id= — al abrir un mensaje nuevo queda como leído. */
    public function show(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $message = ContactMessage::find($request->id());

        if ($message === null) {
            $this->notFound('Mensaje');
            return;
        }

        if ($message['status'] === 'nuevo') {
            ContactMessage::updateStatus((int) $message['id'], 'leido');
            $message['status'] = 'leido';
        }

        $this->render('messages/show', 'Mensaje de ' . $message['name'], 'messages', [
            'message'  => $message,
            'topics'   => ContactMessage::TOPICS,
            'statuses' => ContactMessage::STATUSES,
        ]);
    }

    /** POST /admin/mensajes/estado */
    public function status(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $id = $request->id();
        $status = $request->string('status');

        if (!isset(ContactMessage::STATUSES[$status]) || ContactMessage::find($id) === null) {
            $this->flash('error', 'No se pudo cambiar el estado.');
            Response::redirect(url('/admin/mensajes'));
            return;
        }

        ContactMessage::updateStatus($id, $status);
        $this->flash('ok', 'Estado cambiado a «' . ContactMessage::STATUSES[$status] . '».');
        Response::redirect(url('/admin/mensajes/ver?id=' . $id));
    }

    /** POST /admin/mensajes/eliminar */
    public function destroy(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $deleted = ContactMessage::delete($request->id());
        $this->flash($deleted ? 'ok' : 'error', $deleted ? 'Mensaje eliminado.' : 'El mensaje ya no existe.');
        Response::redirect(url('/admin/mensajes'));
    }
}
