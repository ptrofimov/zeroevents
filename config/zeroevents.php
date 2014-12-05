<?php

return [
    'default_options' => [
        ZMQ::SOCKOPT_LINGER => 2000, // wait before disconnect
        ZMQ::SOCKOPT_SNDTIMEO => 2000, // send message timeout
        ZMQ::SOCKOPT_RCVTIMEO => 2000, // receive message timeout
    ],
    'service' => [
        'socket_type' => ZMQ::SOCKET_PUSH,
        'connect' => [
            'tcp://127.0.0.1:5555',
        ],
    ],
    'service.listen' => [
        'socket_type' => ZMQ::SOCKET_PULL,
        'bind' => 'tcp://127.0.0.1:5555',
    ],
];
