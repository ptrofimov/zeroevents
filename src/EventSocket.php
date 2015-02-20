<?php
namespace ZeroEvents;

use Illuminate\Support\Facades\Event;

/**
 * Class EventSocket pushes and pulls events via ZeroMQ
 *
 * @package ZeroEvents
 */
class EventSocket extends \ZMQSocket
{
    /**
     * Send/wait confirmation after sending/receiving message
     *
     * @var bool
     */
    private $confirmed = false;

    /**
     * Get/set confirmation flag
     *
     * @param bool|null $confirmed
     * @return self
     */
    public function confirmed($confirmed = null)
    {
        if (is_null($confirmed)) {
            return $this->confirmed;
        }

        $this->confirmed = $confirmed === true;

        return $this;
    }

    /**
     * Push event to ZeroMQ socket
     *
     * @param string $event
     * @param array $payload
     * @param string $address
     */
    public function push($event, array $payload = [], $address = null)
    {
        $frames = $this->encode($event, $payload);

        if ($this->getSocketType() == \ZMQ::SOCKET_ROUTER) {
            array_unshift($frames, $address);
        }

        if ($this->sendMulti($frames) === false) {
            return Event::until('zeroevents.push.error', [$this, $event, $payload, $address]);
        }

        if ($this->confirmed && $event != 'zeroevents.confirmed') {
            return $this->pull();
        }
    }

    /**
     * Pull event from ZeroMQ socket
     *
     * @return array [event, payload, address]
     */
    public function pull()
    {
        if (($frames = $this->recvMulti()) === false) {
            return Event::until('zeroevents.pull.error', $this);
        }

        $address = $this->getSocketType() == \ZMQ::SOCKET_ROUTER ? array_shift($frames) : null;
        $message = array_add($this->decode($frames), 'address', $address);

        if ($this->confirmed && $message['event'] != 'zeroevents.confirmed') {
            $this->push('zeroevents.confirmed', [$message['event']], $message['address']);
        }

        return $message;
    }

    /**
     * Pull event from ZeroMQ socket and fire it
     */
    public function pullAndFire()
    {
        if ($event = array_get($message = $this->pull(), 'event')) {
            return Event::until($event, array_get($message, 'payload', []));
        }
    }

    /**
     * Transform event name and payload array into array of frames
     *
     * @param string $event
     * @param array $payload
     * @return array
     */
    public function encode($event, array $payload)
    {
        return array_merge([$event], array_map(function ($value) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $payload));
    }

    /**
     * Transform array of frames into event name and payload array
     *
     * @param array $frames
     * @return array [event, payload]
     */
    public function decode(array $frames)
    {
        return [
            'event' => array_shift($frames),
            'payload' => array_map(function ($frame) {
                return json_decode($frame, true);
            }, $frames)
        ];
    }
}
