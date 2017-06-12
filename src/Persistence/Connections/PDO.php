<?php

namespace SampleORM\Persistence\Connections;

use SampleORM\Collection\Collection;
use SampleORM\Config\ConfigManager;

class PDO extends \PDO implements ConnectionInterface
{
    public static function fromConfiguration(ConfigManager $config)
    {
        return new self($config->__CLASS__->dsn, $config->__CLASS__->username, $config->__CLASS__->password, $config->__CLASS__->options);
    }
}
