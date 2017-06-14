<?php

namespace SampleORM;

class SampleORM
{
    /*
    *	Array of booted modules
    *
    *	@var array
    */
    private $booted = [];

    /*
    *	Array of definitions for the container
    *
    *	@var array
    */
    private $definitions = [];

    /*
    *	Initialize the object
    *
    *	@return void
    */
    public function __construct()
    {
        $this->definitions = require_once __DIR__.'/ContainerDefinitions.php';
    }

    /*
    *	Gets the instance of a module
    *
    *	@param string $name
    *
    *	@return object
    */
    public function __get(string $name)
    {
        switch (strtolower($name)) {
            case 'config':
                $class = \SampleORM\Config\ConfigManager::class;
            case 'persistence':
                $class = \SampleORM\Persistence\Query::class;
            case 'query':
                $class = \SampleORM\Persistence\Abstraction\Query::class;
            case 'collection':
                $class = \SampleORM\Collection\Collection::class;
            case 'mapper':
                $class = \SampleORM\ModelMapper::class;
            default:
                throw new \Exception('Given slug is not defined');
        }
        if (!isset($this->booted[$class])) {
            $this->booted[$class] = $this->resolveDependencies($class);
        }

        return $this->booted[$class];
    }

    /*
    *	Resolve dependancies for the class and initialize it
    *
    *	@param string $class
    *
    *	@return object
    */
    protected function resolveDependencies($class)
    {
        $dependencies = [];
        foreach ($this->definitions[$class] as $dependency) {
            if (!empty($dependency) && $dependency != 'static') {
                foreach ($dependency as $item) {
                    $dependencies = array_merge($dependencies, $this->resolveDependencies($item));
                }

                continue;
            }
        }
        if (isset($this->definitions[$class]['static'])) {
            $method = $this->definitions[$class]['static'];

            return $class::$method(...$dependencies);
        }

        return new $class(...$dependencies);
    }
}
