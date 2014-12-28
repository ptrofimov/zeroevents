<?php
namespace ZeroEvents;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSocket()
    {
        $listener = new EventListener(['connect' => 'ipc://test.ipc']);
        $socket = $listener->socket();

        $this->assertInstanceOf('ZeroEvents\EventSocket', $socket);
        $this->assertSame($socket, $listener->socket());
    }
}
