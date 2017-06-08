<?php
namespace SampleORM\Persistence;

use \SampleORM\Persistence\Abstraction\Drivers\DriverInterface;
use \SampleORM\Persistence\Abstraction\Query;

class Persistence
{
	/*
	*	Driver to run queries against
	*
	*	@var DriverInterface
	*/
	protected $driver;
	
	/*
	*	Query object
	*
	*	@var Query
	*/
	protected $query;
	
	/*
	*	Initialize the object
	*
	*	@param DriverInterface $driver
	*	@param Query $query
	*
	*	@return void
	*/
	public function __construct(DriverInterface $driver, Query $query)
	{
		$this->driver = $driver;
		$this->query = $query;
	}
	
	/*
	*	Function to set what field(s) to retrieve in query
	*
	*	@param mixed $fields
	*
	*	@return $this
	*/
	public function select($fields)
	{
		$this->query->setFields($fields);
		return $this;
	}
	
	/*
	*	Function to set what table to retrieve from in query
	*
	*	@param string $table
	*	@param string alias
	*
	*	@return $this
	*/
	public function table($table, $alias = null)
	{
		$this->query->setTable($table, $alias);
		return $this;
	}
	
	/*
	*	Function to set what conditions to narrow the query results on
	*
	*	@return $this
	*/
	public function where(...$args)
	{
		$this->query->setWhere($args);
		return $this;
	}
	
	/*
	*	Function to set how another table should be joined in the query
	*
	*	@param string|array $table
	*	@param array $args
	*
	*	@return $this
	*/
	public function join($table, ...$args)
	{
		$this->query->addJoin($table, $args);
		return $this;
	}
	
	/*
	*	Set number of rows to limit the results to
	*
	*	@param int $limit
	*	@param int $offset
	*
	*	@return $this
	*/
	public function limit($limit, $offset = null)
	{
		$this->query->setLimit($limit, $offset);
		return $this;
	}
	
	/*
	*	Set order query should be matched to
	*
	*	@param array $Order
	*
	*	@return $this
	*/
	public function orderBy(array $Order)
	{
		$this->query->setOrder($Order);
		return $this;
	}
	
	/*
	*	Set any grouping conditions
	*
	*	@param string|array $column
	*
	*	@return $this
	*/
	public function groupBy($column)
	{
		$this->query->groupBy($column);
		return $this;
	}
	
	/*
	*	Set Having clause conditions
	*
	*	@param array $having
	*
	*	@return $this
	*/
	public function having(array $having)
	{
		$this->query->having($having);
		return $this;
	}
	
	/*
	*	Add where condition that uses the IN () syntax
	*
	*	@param string $column
	*	@param array $values
	*	@param bool	$not
	*
	*	@return $this
	*/
	public function in($column, array $values, $not = false)
	{
		$this->query->in($column, $values, $not);
		return $this;
	}
	
	/*
	*	Run the built query against the DB connection
	*
	*	@return array
	*/
	public function get()
	{
		return $this->driver->select($this->query);
	}
	
	/*
	*	insert the given rows
	*
	*	@param array $rows
	*
	*	@return array
	*/
	public function insert(array $rows)
	{
		return $this->driver->insert($rows, $this->query);
	}
	
	/*
	*	update with the given data
	*
	*	@param array $columns
	*
	*	@return boolean
	*/
	public function update(array $columns)
	{
		return $this->driver->update($columns, $this->query);
	}
	
	/*
	*	delete the given rows
	*
	*	@return boolean
	*/
	public function delete()
	{
		return $this->driver->delete($this->query);
	}
	
	/*
	*	truncate the given table
	*	
	*	@return boolean
	*/
	public function truncate()
	{
		return $this->driver->truncate($this->query);
	}
	
	/*
	*	run a raw query against the DB
	*
	*	@param string $query
	*	@param array $data
	*
	*	@return PDOStatement
	*/
	public function raw($query, array $data = [])
	{
		return $this->driver->raw($query, $data);
	}
}