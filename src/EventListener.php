<?php
namespace ZeroEvents;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;

class EventListener
{
    protected $config;

    protected $context;

    protected $socket;

    public function __construct($socket)
    {
        if (is_string($socket)) {
            $this->config = Config::get($socket);
        } elseif (is_array($socket)) {
            $this->config = $socket;
        } elseif ($socket instanceof EventSocket) {
            $this->socket = $socket;
        }
        $this->config = array_merge(
            [
                'threads' => 1,
                'is_persistent' => false,
                'options' => array_get($config, 'default_options', []),
                'socket_type' => null,
                'bind' => null,
                'connect' => [],
            ],
            array_get($config, $name, [])
        );
    }

    public function socket()
    {
        return $this->socket ? : $this->connect();
    }

    public function context(\ZMQContext $context = null)
    {
        if ($context) {
            $this->context = $context;
        }
        if (!$this->context) {
            $this->context = new \ZMQContext($this->config['threads'], $this->config['is_persistent']);
        }

        return $this->context;
    }

    public function connect()
    {
        $socket = new EventSocket($this->context(), $this->config['socket_type']);
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
        // todo subscribe

        return $this->socket = $socket;
    }

    public function __invoke()
    {
        return $this->socket()->push(Event::firing(), func_get_args());
    }
}
