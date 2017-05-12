<?php
namespace ORM\Collection;
class Collection implements \Countable, \ArrayAccess, \Iterator
{
	private $position = 0;
	
	private $container = [];
	
	public function __construct(array $array = [])
	{
		$this->position = 0;
		$this->container = $array;
	}
	
	public static function collect(array $array = [])
	{
		return new static($array);
	}
	
	public function current()
	{
		return $this->container[$this->position];
	}
	
	public function key()
	{
		return $this->position;
	}
	
	public function next()
	{
		++$this->position;
	}
	
	public function rewind()
	{
		$this->position = 0;
	}
	
	public function valid()
	{
		return isset($this->container[$this->position]);
	}
	
	public function offsetExists($offset)
	{
		return isset($this->container[$offset]);
	}
	
	public function offsetGet($offset)
	{
		return isset($this->container[$offset]) ? $this->container[$offset] : NULL; 
	}
	
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) 
		{
            $this->container[] = $value;
			return;
        }
        
		$this->container[$offset] = $value;
	}
	
	public function offsetUnset($offset)
	{
		unset($this->container[$offset]);
	}
	
	public function count()
	{
		return count($this->container);
	}
	
	public function contains($first, $second = NULL)
	{
		if($second === NULL)
		{
			foreach($this->container as $value)
			{
				if($value == $first)
					return;
			}
			return false;
		}
		
		return $this->container[$first] == $second;
	}
	
	public function each(Closure $closure)
	{
		foreach($this->container as $key => $value)
		{
			if($closure($key, $value))
				return;
		}
	}
}