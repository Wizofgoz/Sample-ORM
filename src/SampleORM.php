<?php
namespace SampleORM;
class SampleORM
{
	private $booted = [];
	
	private $definitions = [];
	
	public function __construct()
	{
		$this->definitions = require_once(__DIR__.'/ContainerDefinitions.php');
	}
	
	public function __get(string $name)
	{
		switch(strtolower($name))
		{
			case 'config':
				$class = \SampleORM\Config\ConfigManager::class;
			case 'persistence':
				$class = \SampleORM\Persistence\Persistence::class;
			case 'query':
				$class = \SampleORM\Persistence\Abstraction\Query::class;
			case 'collection':
				$class = \SampleORM\Collection\Collection::class;
			case 'mapper':
				$class = \SampleORM\ModelMapper::class;
			default:
				throw new \Exception('Given slug is not defined');
		}
		if(!isset($this->booted[$class]))
		{
			$this->booted[$class] = $this->resolveDependencies($class);
		}
		
		return $this->booted[$class];
	}
	
	protected function resolveDependencies($class)
	{
		$dependencies = [];
		foreach($this->definitions[$class] as $dependency)
		{
			if(!empty($dependency) && $dependency != 'static')
			{
				foreach($dependency as $item)
				{
					$dependencies = array_merge($dependencies, $this->resolveDependencies($item));
				}
				
				continue;
			}
		}
		if(isset($this->definitions[$class]['static']))
		{
			$method = $this->definitions[$class]['static'];
			return $class::$method(...$dependencies);
		}
		
		return new $class(...$dependencies);
	}
}
