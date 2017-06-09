<?php

return [
    \SampleORM\Config\ConfigManager::class => [],

    //	raw PDO connection
    \SampleORM\Persistance\Connections\PDO::class => [
        'static' => 'fromConfiguration',
        \SampleORM\Config\ConfigManager::class,
    ],

    //	PDO driver for use with query builder
    \SampleORM\Persistance\Abstraction\Drivers\PDO::class => [
        \SampleORM\Persistance\PDO::class,
    ],

    //	Persistance entry point
    \SampleORM\Persistance\Persistance::class => [
        \SampleORM\Persistance\Abstraction\Drivers\PDO::class,
        \SampleORM\Persistance\Abstraction\Query::class,
    ],

    //	Query Builder for database abstraction
    \SampleORM\Persistance\Abstraction\Query::class => [],

    //	Collection
    \SampleORM\Collection\Collection::class => [],

];
