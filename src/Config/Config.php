<?php

return [
    'database' => \SampleORM\Persistance\Abstraction\Drivers\PDO::class,

    \SampleORM\Persistance\Connections\PDO::class => [
        'dsn'      => '',
        'username' => '',
        'password' => '',
        'options'  => null,
    ],

];
