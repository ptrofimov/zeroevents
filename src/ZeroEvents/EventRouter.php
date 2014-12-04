<?php
namespace ZeroEvents;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Event;

/**
 * EventRouter subscribes to Laravel events and routes them to ZeroMQ sockets
 *
 * Usage:
 *
 * Event::subscribe(new EventRouter([
 *      'event1.*' => new Socket('socket 1'),
 *      'event2.*' => new Socket('socket 2')
 * ]));
 *
 * @package ZeroEvents
 */
class EventRouter
{
    /**
     * @var callable[]
     */
    private $handlers = [];

    /**
     * @param array $handlers Array of sockets or callables
     */
    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $event => $handler) {
            $this->route($event, $handler);
        }
    }

    /**
     * Add handler for event
     *
     * @param string|array $event
     * @param callable|Socket $handler
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function route($event, $handler)
    {
        if ($handler instanceof Socket) {
            $handler = function () use ($handler) {
                $handler->push(Event::firing(), func_get_args());
            };
        }
        if (!is_callable($handler)) {
            throw new \InvalidArgumentException('Handler must be socket or callable');
        }
        $this->handlers = ['event' => $event, 'handler' => $handler];

        return $this;
    }

    /**
     * This method is called by Laravel event dispatcher
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        foreach ($this->handlers as $item) {
            $events->listen($item['event'], $item['handler']);
        }
    }
}
