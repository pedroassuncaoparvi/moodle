<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\dashboard_viewed',
        'callback'    => 'local_middleware_trigger_observer::course_completed',
        'file'        => 'local/middleware_trigger/classes/observer.php',
        'internal'    => false,
    ],
    [
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => 'local_middleware_trigger_observer::course_completed',
        'file'        => 'local/middleware_trigger/classes/observer.php',
        'internal'    => false,
    ]
];
