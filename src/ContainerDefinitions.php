<?php

return [
    \SampleORM\Config\ConfigManager::class => [],

    //	raw PDO connection
    \SampleORM\Persistance\Connections\PDO::class => [
        'static' => 'fromConfiguration',
        \SampleORM\Config\ConfigManager::class,
    ],

    \SampleORM\Persistance\Abstraction\Grammars\GrammarFactory::class => [
        'static' => 'factory',
        \SampleORM\Config\ConfigManager::class,
    ],

    //	PDO driver for use with query builder
    \SampleORM\Persistance\Abstraction\Drivers\PDO::class => [
        \SampleORM\Persistance\PDO::class,
    ],

    //	Query Builder for database abstraction
    \SampleORM\Persistance\Abstraction\Query::class => [
        \SampleORM\Persistance\Abstraction\Drivers\PDO::class,
        \SampleORM\Persistance\Abstraction\Grammars\GrammarFactory::class,
    ],

    //	Collection
    \SampleORM\Collection\Collection::class => [],

];
