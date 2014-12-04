<?php
namespace ZeroEvents\Connector;

use ZeroEvents\Socket;

interface ConnectorInterface
{
    /**
     * @return \ZMQContext
     */
    public function context();

    /**
     * @return string
     */
    public function socketType();

    /**
     * Establish connection
     */
    public function connect(Socket $socket);
}
