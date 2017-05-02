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
			case 'database':
				$class = \SampleORM\Persistance\Persistance::class;
			default:
				throw new \Exception('Given slug is not defined');
		}
		return $this->resolveDependencies($class);
	}
	
	protected function resolveDependencies($class)
	{
		$dependencies = [];
		foreach($this->definitions[$class] as $definition => $dependency)
		{
			if(!empty($dependency))
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
