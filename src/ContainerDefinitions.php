<?php

return [
    \SampleORM\Config\ConfigManager::class => [],

    //	raw PDO connection
    \SampleORM\Persistance\Connections\PDO::class => [
        'static' => 'fromConfiguration',
        \SampleORM\Config\ConfigManager::class,
    ],

    \SampleORM\Persistance\Grammars\GrammarFactory::class => [
        'static' => 'factory',
        \SampleORM\Config\ConfigManager::class,
    ],

    //	Query Builder for database abstraction
    \SampleORM\Persistance\Query::class => [
        \SampleORM\Persistance\Connections\PDO::class,
        \SampleORM\Persistance\Grammars\GrammarFactory::class,
    ],

    //	Collection
    \SampleORM\Collection\Collection::class => [],

];
