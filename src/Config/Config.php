<?php

return [
    'database' => [
        'driver' => \SampleORM\Persistance\Abstraction\Drivers\PDO::class,
        'grammar'=> 'MySql',
    ],

    \SampleORM\Persistance\Connections\PDO::class => [
        'dsn'      => '',
        'username' => '',
        'password' => '',
        'options'  => null,
    ],

];
