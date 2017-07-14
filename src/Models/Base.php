<?php

namespace SampleORM\Models;

use SampleORM\Persistence\Query;
use SampleORM\SampleORM as Container;

class Base
{
	/*
	*	Name of the table for the model
	*
	*	@var string
	*/
    protected static $table = null;

	/*
	*	Name of the primary key column for the model's table
	*
	*	@var string
	*/
    protected static $primaryKey = 'id';

	/*
	*	Whether the primary key should be treated as an incrementing integer
	*
	*	@var bool
	*/
    protected $incrementing = true;

	/*
	*	Whether the model expects there to be created_at and updated_at timestamps
	*	in the database
	*
	*	@var bool
	*/
    protected $timestamps = true;

	/*
	*	What format the system expects dates to be in and what they will be cast to
	*
	*	@var string
	*/
    protected $dateFormat = 'U';

	/*
	*	Array of the properties that are synced with the database
	*
	*	@var array
	*/
    protected $properties = [];
	
	/*
	*	Array of properties that are fillable when making mass-assignment
	*
	*	@var array
	*/
	protected $fillable = null;
	
	/*
	*	Array of property names that represent dates
	*
	*	@var array
	*/
	protected $dates = null;
	
	/*
	*	Whether the model is soft deleting
	*
	*	@var bool
	*/
	protected $softDeleting = false;
	
	/*
	*	Whether the model has been persisted to the database
	*
	*	@var bool
	*/
	protected $persisted = false;
	
	/*
	*	Whether the model has been changed since last sync with the database
	*
	*	@var bool
	*/
	protected $synced = false;
	
	/*
	*	Initialize the object
	*
	*	@param array $properties
	*
	*	@return void
	*/
	public function __construct(array $properties = [])
	{
		$this->properties = $properties;
	}

	/*
	*	Find entry in the database with given id(s)
	*
	*	@param numeric|numeric[] $id
	*
	*	@return static|Collection
	*/
    public static function find($id)
    {
		if (is_array($id)) {
			$rows = static::whereIn(static::$primaryKey, $id)->get();
			$models = [];
			foreach ($rows as $row) {
				$models[] = new static($row);
			}
			
			return new Collection($models);
		}
		elseif (is_numeric($id)) {
			$rows = static::where(static::$primaryKey, $id)->get();
			return new static($rows[0]);
		}
		
		throw new Exception('');
    }
	
	/*
	*	Create a new query builder
	*
	*	@return SampleORM\Persistence\Query
	*/
	public static function getQuery()
	{
		return (new Container)->query;
	}
	
	/*
	*	Resolves table name for the model
	*
	*	@return string
	*/
	public static function getTable()
	{
		if (static::$table !== null) {
			return lcfirst(__CLASS__).'s';
		}
		
		return static::$table;
	}
	
	/*
	*	Returns the query builder filled with base query and where constraints
	*
	*	@return SampleORM\Persistence\Query
	*/
	public static function where(...$args)
	{
		return static::baseQuery()->where(...$args);
	}
	
	/*
	*	Creates a new instance of the model and saves it to the database
	*
	*	@param array $properties
	*
	*	@return static
	*/
	public static function create(array $properties = [])
	{
		$model = new static($properties);
		$model->save();
		return $model;
	}
	
	/*
	*	Deletes model(s) from the database by key
	*
	*	@param array $args
	*
	*	@return SampleORM\Persistence\Query
	*/
	public static function destroy(...$args)
	{
		return static::baseQuery()->whereIn($this->getKeyName(), static::array_flatten($args))->delete();
	}
	
	/*
	*	Returns a query builder with model-specific information pre-filled
	*
	*	@return SampleORM\Persistence\Query
	*/
	protected static function baseQuery()
	{
		return static::getQuery()->table(static::getTable());
	}
	
	/*
	*	Flattens an array to 1 dimension
	*
	*	@param array $arr
	*
	*	@return array
	*/
	public static function array_flatten($arr) 
	{
		$arr = array_values($arr);
		while (list($k,$v)=each($arr)) {
			if (is_array($v)) {
				array_splice($arr,$k,1,$v);
				next($arr);
			}
		}
		return $arr;
	}
	
	/*
	*	Returns the column name of the model's primary key
	*
	*	@return string
	*/
	public function getKeyName()
	{
		return !is_null(static::$primaryKey) ? static::$primaryKey : 'id';
	}
	
	/*
	*	Returns the primary key value of the model
	*
	*	@return mixed
	*/
	public function getKey()
	{
		return $this->__get($this->getKeyName());
	}
	
	/*
	*	Sets properties for the model
	*
	*	@param array $properties
	*
	*	@return void
	*/
	public function fill(array $properties = [])
	{
		$this->properties = $properties;
		$this->synced = false;
	}

	/*
	*	Saves model state to the database
	*
	*	@return void
	*/
    public function save()
    {
		static::where($this->getKeyName(), $this->getKey())->update($this->properties);
		$this->persisted = true;
		$this->synced = true;
    }
	
	/*
	*	Filters array according to configured guards
	*
	*	@param array $array
	*
	*	@return array
	*/
	public function applyGuard(array $array)
	{
		$filtered = [];
		foreach ($array as $key => $value) {
			if ($this->isFillable($key)) {
				$filtered[$key] = $value;
			}
		}
		
		return $filtered;
	}
	
	/*
	*	Determines if a property is fillable by mass-assignment
	*
	*	@param string $name
	*
	*	@return bool
	*/
	public function isFillable(string $name)
	{
		if (is_null($this->fillable)) {
			return true;
		}
		
		return isset($this->fillable[$name]);
	}
	
	/*
	*	Returns whether a property has been set
	*
	*	@param string $name
	*
	*	@return bool
	*/
	public function __isset(string $name)
	{
		return isset($this->properties[$name]);
	}

	/*
	*	Sets a property to a given value
	*
	*	@param string $name
	*	@param mixed $value
	*
	*	@return void
	*/
    public function __set(string $name, $value)
    {
        $this->properties[$name] = $value;
		$this->synced = false;
    }

	/*
	*	Gets a property by name
	*
	*	@param string $name
	*
	*	@return mixed
	*/
    public function __get(string $name)
    {
        if (!isset($this->properties[$name])) {
            return;
        }

        return $this->properties[$name];
    }
}
