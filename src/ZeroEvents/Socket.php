<?php
namespace ZeroEvents;

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

    public function __construct($name, ConnectorInterface $connector = null, SerializerInterface $serializer = null)
    {
        $this->connector = $connector ? : new DefaultConnector($name);
        $this->serializer = $serializer ? : new JsonSerializer;

        parent::__construct($this->connector->context(), $this->connector->socketType());
    }

    private function connector()
    {
        if ($this->connector) {
            $this->connector->connect($this);
            $this->connector = null;
        }
    }

    public function push($event, array $payload)
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
}
