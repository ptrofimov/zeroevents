<?php
namespace ZeroEvents\Connector;

use ZeroEvents\Socket;

class DefaultConnector implements ConnectorInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $config = Config::get('zeroevents');

        $this->config = array_merge(
            [
                'threads' => 1,
                'is_persistent' => false,
                'options' => array_get($config, 'default_options', []),
                'socket_type' => null,
                'bind' => null,
                'connect' => [],
            ],
            $config[$name]
        );
    }

    /**
     * @inheritdoc
     */
    public function context()
    {
        return new \ZMQContext($this->config['threads'], $this->config['is_persistent']);
    }

    /**
     * @inheritdoc
     */
    public function socketType()
    {
        return $this->config['socket_type'];
    }

    /**
     * @inheritdoc
     */
    public function connect(Socket $socket)
    {
        foreach ($this->config['options'] as $key => $value) {
            $socket->setSockOpt($key, $value);
        }
        if ($dsn = $this->config['bind']) {
            $socket->bind($dsn);
            if (substr($dsn, 0, 3) == 'ipc') {
                chmod(str_replace('ipc://', '', $dsn), 0777);
            }
        }
        foreach ($this->config['connect'] as $dsn) {
            $socket->connect($dsn);
        }
    }
}
