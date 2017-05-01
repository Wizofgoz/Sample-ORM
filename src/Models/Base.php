<?php
namespace SampleORM\Models;
class Base
{
	protected $table;
	
	protected $primaryKey;
	
	protected $relationships = [];
	
	protected $softDeletes = false;
	
	protected $incrementing = true;
	
	protected $timestamps = true;
	
	protected $dateFormat = 'U';
	
	protected $properties = [];
	
	private $persistance;
	
	public function findById(int $id)
	{
		
	}
	
	public function save()
	{
		
	}
	
	public function __set(string $name, $value)
	{
		$this->properties[$name] = $value;
	}
	
	public function __get(string $name)
	{
		if(!isset($this->properties[$name]))
		{
			return NULL;
		}
		
		return $this->properties[$name];
	}
}