<?php
namespace ZeroEvents;

use ZeroEvents\Serializer\JsonSerializer;
use ZeroEvents\Serializer\SerializerInterface;

class Socket extends \ZMQSocket
{
    /**
     * Socket name
     *
     * @var string
     */
    private $name;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct($name, SerializerInterface $serializer = null)
    {
        $this->name = $name;
        $this->serializer = $serializer ? : new JsonSerializer;

        parent::__construct(new \ZMQContext(1, false), \ZMQ::SOCKET_DEALER);
    }

    public function push($event, array $payload)
    {
        $frames = $this->serializer->serialize($event, $payload);

        if ($this->sendMulti($frames) === false) {
            throw new \ZMQException("Failed to send event: timeout expired");
        }
    }

    public function pull()
    {
        if (($frames = $this->recvMulti()) === false) {
            throw new \ZMQException('Failed to get reply: timeout expired');
        }

        return $this->serializer->unserialize($frames);
    }
}
