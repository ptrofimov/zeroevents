<?php
namespace ZeroEvents;

use Illuminate\Support\Facades\Event;

class EventService
{
    /**
     * @var EventSocket[]
     */
    protected $listen = [];

    /**
     * Max timeout to wait event from socket, default = -1 (infinite)
     *
     * @var int
     */
    protected $pollTimeout = -1;

    /**
     * @param EventListener $listener
     * @return self
     */
    public function listen(EventListener $listener)
    {
        $this->listen[] = $listener->socket();

        return $this;
    }

    /**
     * Set max timeout to wait event from socket
     *
     * @param int $timeout
     * @return self
     */
    public function pollTimeout($timeout)
    {
        $this->pollTimeout = (int) $timeout;

        return $this;
    }

    /**
     * Listen to system signals
     *
     * Default handler: stop on SIGINT and SIGTERM, but ignore SIGHUP
     *
     * @param array $signals
     * @param callable $handler
     * @return self
     */
    public function listenToSignals(array $signals = [SIGINT, SIGTERM, SIGHUP], callable $handler = null)
    {
        declare(ticks = 1);
        $handler = $handler ? : function ($signal) {
            if (in_array($signal, [SIGINT, SIGTERM])) {
                Event::fire('zeroevents.service.stop');
            }
        };
        foreach ($signals as $signal) {
            pcntl_signal($signal, $handler);
        }

        return $this;
    }

    /**
     * Main processing loop
     *
     * @throws \ZMQPollException
     */
    public function run()
    {
        $poll = new \ZMQPoll;
        $readable = $writable = [];
        foreach ($this->listen as $socket) {
            $poll->add($socket, \ZMQ::POLL_IN);
        }
        $processing = true;
        Event::listen('zeroevents.service.stop', function () use (&$processing) {
            $processing = false;
        });
        while ($processing) {
            try {
                $poll->poll($readable, $writable, $this->pollTimeout);
                foreach ($readable as $socket) {
                    $socket->pullAndFire();
                }
            } catch (\ZMQPollException $ex) {
                if ($ex->getCode() == 4) { //  4 == EINTR, interrupted system call
                    usleep(1); //  Don't just continue, otherwise the ticks function won't be processed
                    continue;
                }
                throw $ex;
            }
            if (!$readable) {
                Event::fire('zeroevents.service.idle', $this);
            }
        }
    }
}
