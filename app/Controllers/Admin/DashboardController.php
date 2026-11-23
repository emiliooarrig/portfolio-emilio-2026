<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Models\Certification;
use App\Models\ContactMessage;
use App\Models\Project;

/**
 * GET /admin
 */
final class DashboardController extends AdminController
{
    public function index(Request $request): void
    {
        if (!$this->guard($request)) {
            return;
        }

        $this->render('dashboard', 'Dashboard', 'dashboard', [
            'stats'      => ContactMessage::stats(14),
            'latest'     => ContactMessage::latest(5),
            'certsTotal' => Certification::count(),
            'certsLive'  => Certification::count(true),
            'projectsTotal' => Project::count(),
            'projectsLive'  => Project::count(true),
            'topics'    => ContactMessage::TOPICS,
            'statuses'   => ContactMessage::STATUSES,
        ]);
    }
}
