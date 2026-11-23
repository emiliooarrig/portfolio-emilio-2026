<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Project;

/**
 * Página pública de un proyecto: GET /proyectos/{slug}.
 */
final class ProjectController
{
    public function show(Request $request): void
    {
        $project = Project::findPublishedBySlug($request->param('slug'));

        if ($project === null) {
            Response::html(View::render('errors/404', ['title' => 'Página no encontrada']), 404);
            return;
        }

        // Vecinos dentro del orden publicado, para la navegación anterior/siguiente.
        $prev = null;
        $next = null;
        $all = Project::published();

        foreach ($all as $i => $candidate) {
            if ($candidate['slug'] === $project['slug']) {
                $prev = $all[$i - 1] ?? null;
                $next = $all[$i + 1] ?? null;
                break;
            }
        }

        $appUrl = (string) config('app.url');
        $data = [
            'title'       => $project['title'] . ' — Emilio Guzmán',
            'description' => $project['description'],
            'project'     => $project,
            'prev'        => $prev,
            'next'        => $next,
        ];

        if ($appUrl !== '') {
            $data['canonical'] = rtrim($appUrl, '/') . '/proyectos/' . $project['slug'];
        }

        Response::html(View::render('project', $data));
    }
}
