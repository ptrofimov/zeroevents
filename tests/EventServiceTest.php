<?php
namespace ZeroEvents;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;

class EventServiceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new Container;
        $container->bind('events', 'Illuminate\Events\Dispatcher');
        Facade::setFacadeApplication($container);
    }

    public function testRun()
    {
        $dsn = 'ipc://test-run.ipc';

        if (!$pid = pcntl_fork()) {

            $listener = new EventListener(['bind' => $dsn]);

            Event::listen('request.event', function () use ($listener) {
//                $listener->socket()->push(Event::firing(), func_get_args());
//                Event::fire('zeroevents.service.stop');
            });

            try {
                (new EventService)
                    ->listen($listener)
                    ->run();
            } catch (\Exception $ex) {
                var_dump($ex->__toString());
            }

            exit;
        }

        $listener = new EventListener(['connect' => $dsn]);
        Event::listen('request.event', $listener);
        Event::fire('request.event', ['source', 'parent']);

        $this->assertSame(
            [
                'event' => 'response.event',
                'payload' => [
                    [
                        'event' => 'request.event',
                        'payload' => ['source', 'parent'],
                        'address' => null,
                    ],
                ],
                'address' => null,
            ],
            $listener->socket()->pull()
        );

//        pcntl_wait($status);
//        posix_kill($pid, SIGKILL);
    }
}
