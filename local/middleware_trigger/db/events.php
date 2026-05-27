<?php
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'local_middleware_trigger_observer::course_completed',
        'file'        => 'local/middleware_trigger/classes/observer.php',
        'internal'    => false,
    ],
];