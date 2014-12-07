<?php

return [

    /*
     * Default options for ZeroMQ sockets
     *
     * These options could be overridden for each socket
     */

    'default_options' => [
        ZMQ::SOCKOPT_LINGER => 2000, // wait before disconnect (ms)
        ZMQ::SOCKOPT_SNDTIMEO => 2000, // send message timeout (ms)
        ZMQ::SOCKOPT_RCVTIMEO => 2000, // receive message timeout (ms)
    ],

    /*
     * Publisher socket
     */

    'service' => [

        /*
         * Socket type
         *
         * Full list of available type http://php.net/manual/en/class.zmq.php
         * Description of sockets http://zguide.zeromq.org/page:all#toc11
         */

        'socket_type' => ZMQ::SOCKET_PUSH,

        /*
         * Addresses of sockets, the same time can be connected multiple addresses
         *
         * About available transports (inproc, ipc, tcp) http://zguide.zeromq.org/page:all#toc13
         */

        'connect' => [
            'tcp://127.0.0.1:5555',
        ],

        /*
         * Number of io-threads in context, default = 1
         */

        'threads' => 1,

        /*
         * Persistent context is stored over multiple requests, default = false
         */

        'is_persistent' => false,

        /*
         * Here can be overridden default socket options
         */

        'options' => [],
    ],

    /*
     * Subscriber socket
     */

    'service.listen' => [

        /*
         * Socket type
         *
         * Full list of available type http://php.net/manual/en/class.zmq.php
         * Description of sockets http://zguide.zeromq.org/page:all#toc11
         */

        'socket_type' => ZMQ::SOCKET_PULL,

        /*
         * Only one process can bind one socket address
         */

        'bind' => 'tcp://127.0.0.1:5555',
    ],
];
