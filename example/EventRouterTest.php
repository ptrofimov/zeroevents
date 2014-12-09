<?php
namespace ZeroEvents;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Facade;

class EventRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $container = new Container;
        $container->bind('events', 'Illuminate\Events\Dispatcher');
        Facade::setFacadeApplication($container);
    }

    private function mockCallable($invokeMatcher, array $args = [])
    {
        $mock = $this->getMockBuilder('stdClass')->setMethods(['handle'])->getMock();
        call_user_func_array([$mock->expects($invokeMatcher)->method('handle'), 'with'], $args);

        return [$mock, 'handle'];
    }

    public function testConstructor()
    {
        Event::subscribe(
            new EventRouter([
                'event1' => $this->mockCallable($this->once(), [1]),
                'event2' => $this->mockCallable($this->never()),
            ])
        );

        Event::fire('event1', [1]);
    }

    public function testRoute()
    {
        Event::subscribe(
            (new EventRouter)
                ->route('event1', $this->mockCallable($this->once(), [1]))
                ->route('event2', $this->mockCallable($this->never()))
        );

        Event::fire('event1', [1]);
    }

    public function testRouteTwoSubscribers()
    {
        Event::subscribe(
            (new EventRouter)
                ->route('event1', $this->mockCallable($this->once(), [1]))
                ->route('event1', $this->mockCallable($this->once(), [1]))
        );

        Event::fire('event1', [1]);
    }

    public function testRoutePattern()
    {
        Event::subscribe(
            (new EventRouter)
                ->route('event1.*', $this->mockCallable($this->once(), [1]))
                ->route('event2.*', $this->mockCallable($this->never()))
        );

        Event::fire('event1.event11', [1]);
    }

    public function testRouteArray()
    {
        Event::subscribe(
            (new EventRouter)
                ->route(['event1', 'event2'], $this->mockCallable($this->once(), [2]))
                ->route('event3', $this->mockCallable($this->never()))
        );

        Event::fire('event2', [2]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Handler must be socket or callable
     */
    public function testRouteInvalid()
    {
        (new EventRouter)
            ->route('event1', 'invalid handler');
    }

    private function mockSocket($invokeMatcher, $event = null, array $payload = [])
    {
        $mock = $this->getMockBuilder('ZeroEvents\Socket')
            ->disableOriginalConstructor()
            ->setMethods(['push'])
            ->getMock();
        call_user_func_array([$mock->expects($invokeMatcher)->method('push'), 'with'], [$event, $payload]);

        return $mock;
    }

    public function testRouteSocket()
    {
        Event::subscribe(
            (new EventRouter)
                ->route('event1.*', $this->mockSocket($this->exactly(2), 'event1.event11', [1]))
                ->route('event2.*', $this->mockSocket($this->never()))
                ->route('event3.*', $this->mockCallable($this->once(), [3]))
        );

        Event::fire('event1.event11', [1]);
        Event::fire('event3.event33', [3]);
        Event::fire('event1.event11', [1]);
    }
}
