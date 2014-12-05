<?php
require_once(dirname(__DIR__) . '/vendor/autoload.php');

use ZeroEvents\Socket;
use ZeroEvents\EventRouter;
use ZeroEvents\EventService;

/*
 * First process that fires event and sends it to second process
 */
function publisher()
{
    Event::subscribe(
        new EventRouter([
            'something.*' => Socket::get('service')
        ])
    );

    Event::fire('something.happened', ['important', 'data']);
}

/*
 * Second process that listens to event and fires it
 */
function subscriber()
{
    Event::listen('*', function () {
        dd(['event' => Event::firing(), 'payload' => func_get_args()]);
    });

    (new EventService([
        Socket::get('service.listen')
    ]))->run();
}

if (!$pid = pcntl_fork()) {
    subscriber();
}
publisher();
