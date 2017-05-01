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
				
			default:
				throw new \Exception('Given slug is not defined');
		}
		$dependancies = $this->resolveDependencies($class);
		
		return new $class(...$dependancies);
	}
	
	protected function resolveDependencies($class)
	{
		$dependencies = [];
		foreach($this->definitions as $definition => $dependency)
		{
			
		}
	}
	
	protected function resolveDependency($class)
	{
		
	}
}
