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
}
