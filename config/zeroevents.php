<?php

return [
    'options' => [
        ZMQ::SOCKOPT_LINGER => 100, // wait before disconnect
        ZMQ::SOCKOPT_SNDTIMEO => 100, // send message timeout
        ZMQ::SOCKOPT_RCVTIMEO => 100, // receive message timeout
    ],
    'service' => [
        'socket_type' => ZMQ::SOCKET_DEALER,
        'connect' => [
            'tcp://localhost:5555',
        ],
    ],
    'service.listen' => [
        'socket_type' => ZMQ::SOCKET_ROUTER,
        'bind' => 'tcp://localhost:5555',
    ],
];
