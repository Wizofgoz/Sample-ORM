<?php
namespace SampleORM\Persistance;
use SampleORM\Config\Configuration;

class PDO extends PDO
{
	public static function fromConfiguration(Configuration $config)
	{
		return new self($config->pdo->dsn, $config->pdo->username, $config->pdo->password, $config->pdo->options);
	}
}