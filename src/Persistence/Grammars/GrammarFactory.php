<?php

namespace SampleORM\Persistence\Grammars;

use SampleORM\Config\ConfigManager;

class GrammarFactory
{
    /*
    *	Create a new grammar according to the set configuration
    *
    *	@return \SampleORM\Persistence\Grammars\GrammarInterface
    */
    public static function factory(ConfigManager $config)
    {
        $class = $config->database->grammar;

        return new $class();
    }
}
