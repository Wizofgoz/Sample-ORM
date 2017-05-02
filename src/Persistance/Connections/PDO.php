<?php
namespace SampleORM\Persistance\Connections;
use SampleORM\Config\ConfigManager;

class PDO extends PDO
{
	public static function fromConfiguration(ConfigManager $config)
	{
		return new self($config->__CLASS__->dsn, $config->__CLASS__->username, $config->__CLASS__->password, $config->__CLASS__->options);
	}
}