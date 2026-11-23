<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Certification;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Project;
use App\Models\StackCategory;
use App\Models\StackTool;

final class HomeController
{
    public function index(Request $request): void
    {
        Response::html(View::render('home', [
            'title'          => 'Emilio Guzmán — Ingeniero en TI · Software & Care Engineering',
            'description'    => 'Ingeniero en TI. Desarrollo sistemas a la medida, los llevo a producción y acompaño a las personas que los usan todos los días.',
            'projects'       => Project::published(),
            'filters'        => Project::FILTERS,
            'certifications' => Certification::published(),
            'experience'     => Experience::published(),
            'stack'          => StackCategory::published(),
            'stackMarquee'   => StackTool::marquee(),
            'topics'         => ContactMessage::TOPICS,
            // Flash del formulario cuando se envía sin JavaScript (PRG).
            'contactSent'    => (bool) Session::pull('contact_sent', false),
            'contactErrors'  => (array) Session::pull('contact_errors', []),
            'contactMessage' => (string) Session::pull('contact_message', ''),
            'old'            => (array) Session::pull('contact_old', []),
        ]));
    }
}
