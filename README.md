zeroevents
==========

Events between processes. Built on top of Illuminate\Events and ZeroMQ

## Installation

Install package using [composer](https://getcomposer.org/)
```
composer require ptrofimov/zeroevents:2.*
```

## Quick Introduction

* 1. **In the first process:** subscribe EventListener to desired events.

```php
Event::listen('my.events.*', new EventListener(['connect' => 'ipc://my.ipc']));
```

In EventListener constructor could be passed either array of options (will be merged with default options)
or string (path to options for Config::get())

* 2. **In the second process:** define listeners for events. And run event service that will listen to messages
on socket and fire incoming events.

```php
Event::listen('my.events.*', function () {
    echo 'Catched event:', Event::firing(), PHP_EOL;
});

(new EventService)
    ->listen(new EventListener(['bind' => 'ipc://my.ipc']))
    ->run();
```

That's it. Each time the event is fired in first process, that will be transferred to the second process and be fired there as well.

## Running tests

* 1. Install [phpunit](https://phpunit.de/getting-started.html)
* 2. Run tests with phpunit
```
phpunit --bootstrap vendor/autoload.php --process-isolation tests
```

## EventSocket class

EventSocket class is inherited from ZMQSocket. Its supports all native methods like connect and send,
plus it adds methods for sending and receiving events.

### Methods

* **encode**(string *event*, array *payload*) - serialize event and payload into array of frames before sending
 * event goes as string in the first frame of message
 * payload is serialized in JSON and goes in the following frames of message
 * encode method is used in **push** method
* **decode**(array *frames*) - unserialize event and payload from array of frames after receiving
 * return array [event, payload]
 * event is supposed to go as string in the first frame of message
 * payload is supposed to go as serialized in JSON in the following frames of message
 * decode method is used in **pull** method

### Connecting socket

You can use usual way to connect to socket via calling the constructor of ZMQSocket
or, better, use **EventListener** that gives you connected socket on the base of options from config.

```php
use ZeroEvents\EventSocket;

$socket = new EventSocket(new ZMQContext, ZMQ::SOCKET_PUSH);
$socket->connect('ipc:///var/tmp/test.ipc');
```

### Pushing events

It is recommended to listen to events that occur during sending of the message (ttl expired, out of connection)
to handle them.

```php
Event::listen('zeroevents.push.error', function () {
    // logging or something else
});

$socket->push('event', ['payload']);
```

## EventListener class

EventListener class is supposed to be passed to event dispatcher as listener callback.
It has magic method **__invoke**, that is called, when an event is fired.
EventListener creates EventSocket instance on-demand (lazy connection) and call method **EventSocket::push**.

```php
Event::listen('my.events.*', new EventListener('my.socket.config.key'));
```

Constructor accepts either string (config key) or array of options.

### Connection options

```php
$options = [

    'example.connection' => [

        /*
         * Number of io-threads in context, default = 1
         */

        'threads' => 1,

        /*
         * Persistent context is stored over multiple requests, default = false
         */

        'is_persistent' => false,

        /*
         * Socket type
         *
         * Full list of available types http://php.net/manual/en/class.zmq.php
         * Description of sockets http://zguide.zeromq.org/page:all#toc11
         */

        'socket_type' => ZMQ::SOCKET_PUSH,

        /*
         * Default options for ZeroMQ socket
         */

        'socket_options' => [
            ZMQ::SOCKOPT_LINGER => 2000, // wait before disconnect (ms)
            ZMQ::SOCKOPT_SNDTIMEO => 2000, // send message timeout (ms)
            ZMQ::SOCKOPT_RCVTIMEO => 2000, // receive message timeout (ms)
        ],

        /*
         * Addresses to bind. Only one process can bind address
         *
         * About available transports (inproc, ipc, tcp) http://zguide.zeromq.org/page:all#toc13
         */

        'bind' => [
            'tcp://127.0.0.1:5555',
        ],

        /*
         * Addresses of sockets, the same time can be connected multiple addresses
         *
         * About available transports (inproc, ipc, tcp) http://zguide.zeromq.org/page:all#toc13
         */

        'connect' => [
            'tcp://127.0.0.1:5555',
        ],

        /*
         * Type of events to subscribe. Events masks (*) are not here supported.
         *
         * Only useful for SOCKET_SUB socket type
         */

        'subscribe' => 'my.events',

        /*
         * Send/wait confirmation after sending/receiving message
         */

        'confirmed' => false,
    ],
];
```

## EventService class

Class is used to listen to incoming events and fire them.

### Polling sockets

EventService class is able to listen to several sockets the same time.

```php
(new EventService)
    ->listen(new EventListener(['connect' => 'ipc://first.ipc']))
    ->listen(new EventListener(['connect' => 'ipc://second.ipc']))
    ->run();
```

### Idle events

You can specify the polling time - max time that service waits for incoming events
and if there are no such events, it fires **zeroevents.service.idle** event,
which you can handle and execute your own code while there is no work for the service.

### System signals

EventService class has default handler for system POSIX signals.
It ignores **SIGHUP**, and gracefully stops on **SIGTERM** and **SIGINT** signals.
Sure, you could define your own system signal handler.

### Stopping the service

Normally, the service is being stopped by system signal.
But you could easily stop the service by firing **zeroevents.service.stop** event.

## License

Copyright (c) 2014 Petr Trofimov

MIT License

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
