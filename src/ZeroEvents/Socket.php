<?php
namespace ZeroEvents;

use Illuminate\Support\Facades\Event;
use ZeroEvents\Serializer\JsonSerializer;
use ZeroEvents\Connector\DefaultConnector;
use ZeroEvents\Connector\ConnectorInterface;
use ZeroEvents\Serializer\SerializerInterface;

class Socket extends \ZMQSocket
{
    /**
     * @var ConnectorInterface
     */
    private $connector;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param string $name
     * @param ConnectorInterface $connector
     * @param SerializerInterface $serializer
     * @return self
     */
    public static function get($name, ConnectorInterface $connector = null, SerializerInterface $serializer = null)
    {
        $connector = $connector ? : new DefaultConnector($name);

        $socket = new static($connector->context(), $connector->socketType());

        $socket->connector = $connector;
        $socket->serializer = $serializer ? : new JsonSerializer;

        return $socket;
    }

    public function connector()
    {
        if ($this->connector) {
            $this->connector->connect($this);
            $this->connector = null;
        }
    }

    public function push($event, array $payload = [])
    {
        $this->connector();

        $frames = $this->serializer->serialize($event, $payload);

        if ($this->sendMulti($frames) === false) {
            throw new \ZMQException('Failed to send event: timeout expired');
        }
    }

    public function pull()
    {
        $this->connector();

        if (($frames = $this->recvMulti()) === false) {
            throw new \ZMQException('Failed to get reply: timeout expired');
        }

        return $this->serializer->unserialize($frames);
    }

    public function pullAndFire()
    {
        $message = $this->pull();

        Event::fire($message['event'], $message['payload']);
    }
}
