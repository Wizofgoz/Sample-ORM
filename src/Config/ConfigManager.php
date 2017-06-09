<?php

namespace SampleORM\Config;

class ConfigManager
{
    private $config = [];

    public function __construct()
    {
        $config = require_once __DIR__.'/Config.php';
        foreach ($config as $key => $value) {
            $this->config[$key] = (object) $value;
        }
    }

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }
}
