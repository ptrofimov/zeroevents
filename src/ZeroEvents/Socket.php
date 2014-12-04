<?php
namespace ZeroEvents;

class Socket //extends \ZMQSocket
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
//        parent::__construct(new \ZMQContext(1, false), \ZMQ::SOCKET_DEALER);
    }

    public function push(/*array $frames*/)
    {
        var_dump($this->name);
//        if ($this->sendMulti($frames) === false) {
//            throw new \ZMQException("Failed to send event: timeout expired");
//        }
    }

    public function pull()
    {
        if (($reply = $this->recvMulti()) === false) {
            throw new \ZMQException('Failed to get reply: timeout expired');
        }

        return $reply;
    }
}
