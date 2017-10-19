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
        $tmpDir = sys_get_temp_dir();
        $dsn = "ipc://$tmpDir/test-run.ipc";

        if (!$pid = pcntl_fork()) {

            $listener = new EventListener(['bind' => $dsn]);

            Event::listen('request.event', function () use ($listener) {
                $listener->socket()->push(Event::firing(), func_get_args());
                Event::fire('zeroevents.service.stop');
            });

            (new EventService)
                ->listen($listener)
                ->run();
            exit;
        }

        $listener = new EventListener(['connect' => $dsn]);
        Event::listen('request.event', $listener);
        Event::fire('request.event', ['source', 'parent']);

        $this->assertSame(
            [
                'event' => 'request.event',
                'payload' => ['source', 'parent'],
                'address' => null,
            ],
            $listener->socket()->pull()
        );

        pcntl_wait($status);
        posix_kill($pid, SIGKILL);
    }

    public function testRunIdle()
    {
        $idle = 0;
        $tmpDir = sys_get_temp_dir();

        Event::listen('zeroevents.service.idle', function () use (&$idle) {
            if (++$idle >= 3) {
                Event::fire('zeroevents.service.stop');
            }
        });

        $t1 = microtime(true);

        (new EventService)
            ->listen(new EventListener(['bind' => "ipc://$tmpDir/test-run-idle.ipc"]))
            ->pollTimeout(400)
            ->run();

        $this->assertSame(3, $idle);
        $this->assertTrue(microtime(true) - $t1 >= 1.2);
    }

    public function testListenToSignals()
    {
        $tmpDir = sys_get_temp_dir();
        $t1 = microtime(true);

        if (!$pid = pcntl_fork()) {
            (new EventService)
                ->listen(new EventListener(['bind' => "ipc://$tmpDir/test-listen-to-signals.ipc"]))
                ->listenToSignals()
                ->run();
            exit;
        }

        sleep(1);
        posix_kill($pid, SIGHUP);
        sleep(1);
        posix_kill($pid, SIGTERM);

        pcntl_wait($status);

        $this->assertTrue(microtime(true) - $t1 >= 2.0);
    }
}
