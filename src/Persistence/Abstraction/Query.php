<?php
namespace SampleORM\Persistence\Abstraction;

class Query
{
	/*
	*	Array of fields to retrieve in the query
	*
	*	@var string[]
	*/
	protected $fields = array();
	
	/*
	*	Table name that the query is to be run against
	*
	*	@var string
	*/
	protected $table;
	
	/*
	*	Array of where conditions to apply to the query
	*
	*	@var mixed[]
	*/
	protected $where = array();
	
	/*
	*	Array of join conditions to apply to the query
	*
	*	@var mixed[]
	*/
	protected $joins = array();
	
	/*
	*	Array of ordering conditions to apply to the query
	*
	*	@var mixed[]
	*/
	protected $order = array();
	
	/*
	*	Array of grouping conditions to apply to the query
	*
	*	@var mixed[]
	*/
	protected $group = array();
	
	/*
	*	Array of having conditions to apply to the query
	*
	*	@var mixed[]
	*/
	protected $having = array();
	
	/*
	*	Limit clause to apply to the query
	*
	*	@var string
	*/
	protected $limit;
	
	/*
	*	Set what fields to retreive in the query
	*
	*	@param string|string[] $fields
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function setFields($fields)
	{
		if(is_array($fields) || is_string($fields)) {
			if(is_array($fields)) {
				$this->fields = $fields;
				return;
			}
			$this->fields[] = $fields;
			
			return;
		}
		throw new \Exception('Expected either a string or array');
	}
	
	/*
	*	Set the table the query will be run against
	*
	*	@param string $table
	*	@param string $alias
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function setTable($table, $alias = null)
	{
		if(is_string($table)) {
			$this->table = $table.($alias === null ? '' : ' AS '.$alias);
			return;
		}
		
		throw new \Exception('Expected a string');
	}
	
	/*
	*	Set where conditions for the query
	*
	*	@param array $args
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function setWhere(...$args)
	{
		//	if only 1 argument in the array
		if(count($args[0]) === 1) {
			//	the argument has to be an array
			if(is_array($args[0])) {
				foreach ($args[0] as $condition) {
					//	each condition must be an array
					if(is_array($condition)) {
						$this->where_condition($condition);
						continue;
					}
					
					throw new \Exception('Expected an array');
				}
				
				return;
			}
			
			throw new \Exception('Expected an array');
		}
		
		$this->where_condition($args[0]);
	}
	
	/*
	*	Adds individual condition to Where array
	*
	*	@param array $args
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	protected function where_condition(array $args)
	{
		//	if 2 arguments for a condition, shortcut for =
		if(count($args) === 2) {
			/*	
			*	ex.		id = 12
			*	ex.		name = 'test'
			*/
			$this->where[] = [$args[0].' = ?' => $args[1]];
		}
		//	if 3 arguments, 2nd is operator, 3rd is value
		elseif(count($args) === 3) {
			/*	
			*	ex.		id = 12
			*	ex.		name = 'test'
			*/
			$this->where[] = [$args[0].' '.$args[1].' ?'=>$args[2]];
		}
		
		//	condition can't have just 1 argument
		throw new \Exception('Unexpected number of arguments in condition');
	}
	
	/*
	*	Add a join to the query
	*
	*	@param string|string[] $table
	*	@param array $args
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function addJoin($table, ...$args)
	{
		//	if $table is an array, the user is setting an alias
		if(is_array($table)) {
			$table = $table[0].' AS '.$table[1];
		}
		//	if only 1 argument in the array
		if(count($args[0]) === 1) {
			//	the argument has to be an array
			if(is_array($args[0])) {
				foreach ($args[0][0] as $condition) {
					//	each condition must be an array
					if(is_array($condition)) {
						$this->join_condition($table, $condition);
						continue;
					}
					
					throw new \Exception('Expected an array');
				}
				
				return;
			}
			
			throw new \Exception('Expected an array');
		}
		
		$this->join_condition($table, $args[0]);
	}
	
	/*
	*	Adds condition to On array
	*
	*	@param string $table
	*	@param array $args
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	protected function join_condition($table, array $args)
	{
		switch(count($args))
		{
			//	if 2 arguments for a condition, shortcut for =
			case 2:
				/*	
				*	ex.		id = 12
				*	ex.		name = 'test'
				*/
				$this->joins[$table][] = $args[0].' = '.$args[1];
				break;
			//	if 3 arguments, 2nd is operator, 3rd is value unless 3rd is boolean
			case 3:
				/*	
				*	ex.		id = 12
				*	ex.		name = 'test'
				*/
				$this->joins[$table][] = $args[0].' '.(is_bool($args[2])  ? ($args[2] === true ? ' = '.$args[1] : " = '".$args[1]."'") : $args[1]." ".$args[2]);
				break;
			//	if 4 arguments, 4th tells us whether to treat 2nd value (arg 3) as a column name (no 's)
			case 4:
				/*	
				*	ex.		id = user_id		4th = true
				*	ex.		name = 'test'	4th = false
				*/
				$this->joins[$table][] = $args[0].' '.$args[1].' '.((is_bool($args[3]) && $args[3] === true) ? $args[2] : "'".$args[2]."'");
				break;
			//	condition can't have just 1 argument
			default:
				throw new \Exception('Unexpected number of arguments in condition');
		}
	}
	
	/*
	*	Sets a limit for the query
	*
	*	@param int $limit
	*	@param int $offset
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function setLimit($limit, $offset = null)
	{
		if(is_int($limit)) {
			$this->limit = ($offset !== null ? $offset.',' : '').$limit;
			return;
		}
		
		throw new \Exception('Expected an integer');
	}
	
	/*
	*	Sets ordering conditions for the query
	*
	*	@param string|string[] $order
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function setOrder($order)
	{
		if(is_array($order)) {
			//	if it's a multi-dimensional array, there are multiple ordering constraints
			if(is_array($order[0])) {
				foreach ($order as $constraint) {
					$this->orderConstraint($constraint);
				}
				return;
			}
			
			$this->orderConstraint($constraint);
			return;
		}
		elseif(is_string($order)) {
			$this->orderConstraint([$order]);
			return;
		}
		
		throw new \Exception('Expected either a string or an array');
	}
	
	/*
	*	Sets individual order condition
	*
	*	@param array $constraint
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	protected function orderConstraint(array $constraint)
	{
		switch(count($constraint))
		{
			//	only 1 item in array, default ordering on that column
			case 1:
				$this->order[] = $constraint[0];
				break;
			case 2:
				$this->order[] = $constraint[0].' '.$constraint[1];
				break;
			default:
				throw new \Exception('Unexpected number of arguments. Can only have up to 2');
		}
	}
	
	/*
	*	Add group by constraint(s) to the query
	*
	*	@param string|string[] $column
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function groupBy($column)
	{
		if(is_array($column)) {
			foreach ($column as $constraint) {
				$this->groupConstraint($constraint);
			}
			
			return;
		}
		elseif(is_string($column)) {
			$this->groupConstraint($column);
			return;
		}
		
		throw new \Exception('Expected either a string or an array');
	}
	
	/*
	*	Add individual group by constraint
	*
	*	@param string $constraint
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	protected function groupConstraint($constraint)
	{
		if(is_string($constraint)) {
			$this->group[] = $constraint;
			return;
		}
		
		throw new \Exception('Expected a string');
	}
	
	/*
	*	Add having constraints to the query
	*
	*	@param array[] $having
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function having(array $having)
	{
		// if just single-dimensional array
		if(!is_array($having[0])) {
			$this->havingConstraint($having);
			return;
		}
		foreach($having as $constraint) {
			if(is_array($constraint)) {
				$this->havingConstraint($constraint);
				continue;
			}
			
			throw new \Exception('Expected an array');
		}
	}
	
	/*
	*	Add individual having constraint
	*
	*	@param array $constraint
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	protected function havingConstraint(array $constraint)
	{
		switch(count($constraint))
		{
			//	implicit = operator
			case 2:
				$this->having[] = [$constraint[0].' = ?' => $constraint[1]];
				break;
			case 3:
				$this->having[] = [$constraint[0].' '.$constraint[1].' ?' => $constraint[2]];
				break;
			default:
				throw new \Exception('Unexpected number of arguments. Can only have up to 3');
		}
	}
	
	/*
	*	Adds condition to Where array using the IN syntax
	*
	*	@param string $column
	*	@param array $values
	*	@param bool $not
	*
	*	@throws \Exception
	*
	*	@return void
	*/
	public function in($column, array $values, $not = false)
	{
		if(is_string($column)) {
			$placeholders = '';
			foreach($values as $value) {
				$placeholders .= ($placeholders == '' ? '?' : ', ?');
			}
			$this->where[] = [$column.' '.($not === false ? 'IN (' : 'NOT IN(').$placeholders.')' => $values];
			
			return;
		}
		
		throw new \Exception('Expected a string');
	}
	
	/*
	*	Returns the table for the query
	*
	*	@return string
	*/
	public function getTable()
	{
		return $this->table;
	}
	
	/*
	*	Returns the set fields for the query
	*
	*	@return array
	*/
	public function getFields()
	{
		return $this->fields;
	}
	
	/*
	*	Returns the set joins for the query
	*
	*	@return array
	*/
	public function getJoins()
	{
		return $this->joins;
	}
	
	/*
	*	Returns the set where constraints for the query
	*
	*	@return array
	*/
	public function getWheres()
	{
		return $this->where;
	}
	
	/*
	*	Returns the set ordering constraints for the query
	*
	*	@return array
	*/
	public function getOrder()
	{
		return $this->order;
	}
	
	/*
	*	Returns the set grouping constraints for the query
	*
	*	@return array
	*/
	public function getGroup()
	{
		return $this->group;
	}
	
	/*
	*	Returns the set having constraints for the query
	*
	*	@return array
	*/
	public function getHaving()
	{
		return $this->having;
	}
	
	/*
	*	Returns the set limit constraint for the query
	*
	*	@return string
	*/
	public function getLimit()
	{
		return $this->limit;
	}
}