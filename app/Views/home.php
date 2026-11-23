<?php

use App\Core\View;


echo View::partial('hero');
echo View::partial('manifesto');
echo View::partial('about');
echo View::partial('stack', ['stack' => $stack, 'marquee' => $stackMarquee]);
echo View::partial('process');
echo View::partial('projects', ['projects' => $projects, 'filters' => $filters]);
echo View::partial('fovea');
echo View::partial('experience', ['experience' => $experience]);
echo View::partial('certifications', ['certifications' => $certifications]);
echo View::partial('contact', [
    'topics'         => $topics,
    'contactSent'    => $contactSent,
    'contactErrors'  => $contactErrors,
    'contactMessage' => $contactMessage,
    'old'            => $old,
]);
