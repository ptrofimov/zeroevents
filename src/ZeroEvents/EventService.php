<?php
namespace ZeroEvents;

use Illuminate\Support\Facades\Event;

class EventService
{
    /**
     * @var Socket[]
     */
    protected $listen = [];

    /**
     * @var bool
     */
    protected $processing = true;

    /**
     * @param Socket[] $listen
     */
    public function __construct(array $listen = [])
    {
        $this->listen = $listen;
    }

    public function run()
    {
        declare(ticks = 1);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);
        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGHUP, [$this, 'handleSignal']);

        $poll = new \ZMQPoll;
        $read = $write = [];
        foreach ($this->listen as $socket) {
            $poll->add($socket, \ZMQ::POLL_IN);
        }
        while ($this->processing) {
            try {
                $poll->poll($read, $write);
                foreach ($read as $socket) {
                    $message = $socket->pull();
                    Event::fire($message['event'], $message['payload']);
                }
            } catch (\ZMQPollException $ex) {
                if ($ex->getCode() == 4) { //  4 == EINTR, interrupted system call
                    usleep(1); //  Don't just continue, otherwise the ticks function won't be processed
                    continue;
                }
                throw $ex;
            }
        }
    }

    /**
     * Handle system signal
     *
     * @param $signal
     */
    public function handleSignal($signal)
    {
        if ($signal == SIGINT || $signal == SIGTERM) {
            $this->processing = false;
        }
    }
}
