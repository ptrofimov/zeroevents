<?php
namespace ZeroEvents;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;

class EventListener
{
    /**
     * @var array
     */
    protected $options = [
        'threads' => 1,
        'is_persistent' => false,
        'socket_type' => \ZMQ::SOCKET_DEALER,
        'socket_options' => [],
        'bind' => null,
        'connect' => [],
        'subscribe' => null,
    ];

    /**
     * @var \ZMQContext|null
     */
    protected $context;

    /**
     * @var EventSocket|null
     */
    protected $socket;

    /**
     * @param array|string $options
     */
    public function __construct($options)
    {
        $this->options = array_merge(
            $this->options,
            is_array($options) ? $options : Config::get($options)
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
            $this->context = new \ZMQContext($this->options['threads'], $this->options['is_persistent']);
        }

        return $this->context;
    }

    public function connect()
    {
        $socket = new EventSocket($this->context(), $this->options['socket_type']);
        foreach ($this->options['options'] as $key => $value) {
            $socket->setSockOpt($key, $value);
        }
        if ($dsn = $this->options['bind']) {
            $socket->bind($dsn);
            if (substr($dsn, 0, 3) == 'ipc') {
                chmod(str_replace('ipc://', '', $dsn), 0777);
            }
        }
        foreach ((array) $this->options['connect'] as $dsn) {
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
