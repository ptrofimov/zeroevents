<?php
namespace ZeroEvents;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new Container;
        $container->bind('events', 'Illuminate\Events\Dispatcher');
        Facade::setFacadeApplication($container);
    }

    public function testSocket()
    {
        $listener = new EventListener(['connect' => 'ipc://test.ipc']);
        $socket = $listener->socket();

        $this->assertInstanceOf('ZeroEvents\EventSocket', $socket);
        $this->assertSame($socket, $listener->socket());
    }

    public function testContext()
    {
        $listener = new EventListener([
            'threads' => 2,
            'is_persistent' => false,
        ]);
        $context = $listener->context();

        $this->assertInstanceOf('ZMQContext', $context);
        $this->assertSame($context, $listener->context());
        $this->assertFalse($context->isPersistent());
    }

    public function testConnect()
    {
        $listener = new EventListener([
            'socket_type' => \ZMQ::SOCKET_PUSH,
            'socket_options' => [
                \ZMQ::SOCKOPT_SNDHWM => 2000,
            ],
            'bind' => 'ipc://test-connect-bind.ipc',
            'connect' => [
                'ipc://test-connect-1.ipc',
                'ipc://test-connect-2.ipc',
            ],
        ]);
        $socket = $listener->socket();

        $this->assertSame(\ZMQ::SOCKET_PUSH, $socket->getSocketType());
        $this->assertSame(2000, $socket->getSockOpt(\ZMQ::SOCKOPT_SNDHWM));
        $this->assertSame(
            [
                'connect' => [
                    'ipc://test-connect-1.ipc',
                    'ipc://test-connect-2.ipc',
                ],
                'bind' => [
                    'ipc://test-connect-bind.ipc'
                ],
            ],
            $socket->getEndpoints()
        );
    }

    public function testInvoke()
    {
        $dsn = 'ipc://test-invoke.ipc';

        if (!$pid = pcntl_fork()) {
            $socket = (new EventListener(['bind' => $dsn]))->socket();
            $socket->push('response.event', [$socket->pull()]);
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

        posix_kill($pid, SIGKILL);
    }
}
