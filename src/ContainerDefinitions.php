<?php
return [
	\SampleORM\Config\ConfigManager::class => [],
	//	raw PDO connection
	\SampleORM\Persistance\PDO::class => [
		\SampleORM\Config\ConfigManager::class
	],
	//	PDO driver for use with query builder
	\SampleORM\Persistance\Drivers\PDO::class => [
		\SampleORM\Persistance\PDO::class
	],
	//	Query Builder for database abstraction
	\SampleORM\Persistance\Abstraction\Query::class => [],
	\SampleORM\Collection\Collection::class => [],
];