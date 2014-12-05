<?php
require_once(__DIR__ . '/bootstrap.php');

use ZeroEvents\Socket;
use ZeroEvents\EventRouter;
use ZeroEvents\EventService;
use ZeroEvents\Connector\DefaultConnector;

/*
 * First process that fires event and sends it to second process
 */
function publisher($config)
{
    Event::subscribe(
        new EventRouter([
            'something.*' => Socket::get(null, new DefaultConnector('service', $config))
        ])
    );

    Event::fire('something.happened', ['important', 'data']);
}

/*
 * Second process that listens to event and fires it
 */
function subscriber($config)
{
    Event::listen('*', function () {
        dd(['event' => Event::firing(), 'payload' => func_get_args()]);
    });

    (new EventService([
        Socket::get(null, new DefaultConnector('service.listen', $config))
    ]))->run();
}

$config = require(dirname(__DIR__) . '/config/zeroevents.php');
if (!$pid = pcntl_fork()) {
    subscriber($config);
}
publisher($config);
