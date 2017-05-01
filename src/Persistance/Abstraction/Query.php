<?php
namespace SampleORM\Persistance\Abstraction;

class Query
{
	protected $Fields = array();
	
	protected $Table;
	
	protected $Where = array();
	
	protected $Joins = array();
	
	protected $Order = array();
	
	protected $Group = array();
	
	protected $Having = array();
	
	protected $Limit;
	
	public function setFields($fields)
	{
		if(is_array($fields) || is_string($fields))
		{
			if(is_array($fields))
			{
				$this->Fields = $fields;
			}
			else
			{
				$this->Fields[] = $fields;
			}
		}
		else
		{
			throw new \Exception('Expected either a string or array');
		}
	}
	
	public function setTable($table, $alias = NULL)
	{
		if(is_string($table))
		{
			$this->Table = $table.($alias === NULL ? '' : ' AS '.$alias);
			return $this;
		}
		else
		{
			throw new \Exception('Expected a string');
		}
	}
	
	public function setWhere(...$args)
	{
		//	if only 1 argument in the array
		if(count($args[0]) === 1)
		{
			//	the argument has to be an array
			if(is_array($args[0]))
			{
				foreach($args[0] as $Condition)
				{
					//	each condition must be an array
					if(is_array($Condition))
					{
						$this->where_condition($Condition);
					}
					else
					{
						throw new \Exception('Expected an array');
					}
				}
			}
			else
			{
				throw new \Exception('Expected an array');
			}
		}
		else
		{
			$this->where_condition($args[0]);
		}
	}
	
	/*
	*	Adds condition to Where array
	*
	*	@param array $args
	*/
	protected function where_condition(array $args)
	{
		//	if 2 arguments for a condition, shortcut for =
		if(count($args) === 2)
		{
			/*	
			*	ex.		id = 12
			*	ex.		name = 'test'
			*/
			$this->Where[] = [$args[0].' = ?' => $args[1]];
		}
		//	if 3 arguments, 2nd is operator, 3rd is value
		elseif(count($args) === 3)
		{
			/*	
			*	ex.		id = 12
			*	ex.		name = 'test'
			*/
			$this->Where[] = [$args[0].' '.$args[1].' ?'=>$args[2]];
		}
		//	condition can't have just 1 argument
		else
		{
			throw new \Exception('Unexpected number of arguments in condition');
		}
	}
	
	public function addJoin($table, ...$args)
	{
		//	if $table is an array, the user is setting an alias
		if(is_array($table))
		{
			$table = $table[0].' AS '.$table[1];
		}
		//	if only 1 argument in the array
		if(count($args[0]) === 1)
		{
			//	the argument has to be an array
			if(is_array($args[0]))
			{
				foreach($args[0][0] as $Condition)
				{
					//	each condition must be an array
					if(is_array($Condition))
					{
						$this->join_condition($table, $Condition);
					}
					else
					{
						throw new \Exception('Expected an array');
					}
				}
			}
			else
			{
				throw new \Exception('Expected an array');
			}
		}
		else
		{
			$this->join_condition($table, $args[0]);
		}
	}
	
	/*
	*	Adds condition to On array
	*
	*	@param string $table
	*	@param array $args
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
				$this->Joins[$table][] = $args[0].' = '.$args[1];
				break;
			//	if 3 arguments, 2nd is operator, 3rd is value unless 3rd is boolean
			case 3:
				/*	
				*	ex.		id = 12
				*	ex.		name = 'test'
				*/
				$this->Joins[$table][] = $args[0].' '.(is_bool($args[2])  ? ($args[2] === true ? ' = '.$args[1] : " = '".$args[1]."'") : $args[1]." ".$args[2]);
				break;
			//	if 4 arguments, 4th tells us whether to treat 2nd value (arg 3) as a column name (no 's)
			case 4:
				/*	
				*	ex.		id = user_id		4th = true
				*	ex.		name = 'test'	4th = false
				*/
				$this->Joins[$table][] = $args[0].' '.$args[1].' '.((is_bool($args[3]) && $args[3] === true) ? $args[2] : "'".$args[2]."'");
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
	*/
	public function setLimit($limit, $offset = NULL)
	{
		if(is_int($limit))
		{
			$this->Limit = ($offset !== NULL ? $offset.',' : '').$limit;
		}
		else
		{
			throw new \Exception('Expected an integer');
		}
	}
	
	/*
	*	Sets ordering conditions for the query
	*
	*	@param string/array $order
	*/
	public function setOrder($Order)
	{
		if(is_array($Order))
		{
			//	if it's a multi-dimensional array, there are multiple ordering constraints
			if(is_array($Order[0]))
			{
				foreach($Order as $Constraint)
				{
					$this->orderConstraint($Constraint);
				}
			}
			else
			{
				$this->orderConstraint($Constraint);
			}
		}
		elseif(is_string($Order))
		{
			$this->orderConstraint([$Order]);
		}
		else
		{
			throw new \Exception('Expected either a string or an array');
		}
	}
	
	/*
	*	Sets individual order condition
	*
	*	@param array $constraint
	*/
	protected function orderConstraint(array $constraint)
	{
		switch(count($constraint))
		{
			//	only 1 item in array, default ordering on that column
			case 1:
				$this->Order[] = $constraint[0];
				break;
			case 2:
				$this->Order[] = $constraint[0].' '.$constraint[1];
				break;
			default:
				throw new \Exception('Unexpected number of arguments. Can only have up to 2');
		}
	}
	
	public function groupBy($column)
	{
		if(is_array($column))
		{
			foreach($column as $Constraint)
			{
				$this->groupConstraint($Constraint);
			}
		}
		elseif(is_string($column))
		{
			$this->groupConstraint($column);
		}
		else
		{
			throw new \Exception('Expected either a string or an array');
		}
	}
	
	protected function groupConstraint($constraint)
	{
		if(is_string($constraint))
		{
			$this->Group[] = $constraint;
		}
		else
		{
			throw new \Exception('Expected a string');
		}
	}
	//	[	['column', 'value'], ['column2', '>', 'value2']	]
	public function having(array $having)
	{
		foreach($having as $constraint)
		{
			if(is_array($constraint))
			{
				$this->havingConstraint($constraint);
			}
			else
			{
				throw new \Exception('Expected an array');
			}
		}
	}
	
	protected  function havingConstraint(array $constraint)
	{
		switch(count($constraint))
		{
			//	implicit = operator
			case 2:
				$this->Having[] = [$constraint[0].' = ?' => $constraint[1]];
				break;
			case 3:
				$this->Having[] = [$constraint[0].' '.$constraint[1].' ?' => $constraint[2]];
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
	*/
	public function in($column, array $values, $not = false)
	{
		if(is_string($column))
		{
			$placeholders = '';
			foreach($values as $value)
			{
				$placeholders .= ($placeholders == '' ? '?' : ', ?');
			}
			$this->Where[] = [$column.' '.($not === false ? 'IN (' : 'NOT IN(').$placeholders.')'=>$values];
		}
		else
		{
			throw new \Exception('Expected a string');
		}
	}
	
	public function getTable()
	{
		return $this->Table;
	}
	
	public function getFields()
	{
		return $this->Fields;
	}
	
	public function getJoins()
	{
		return $this->Joins;
	}
	
	public function getWheres()
	{
		return $this->Where;
	}
	
	public function getOrder()
	{
		return $this->Order;
	}
	
	public function getGroup()
	{
		return $this->Group;
	}
	
	public function getHaving()
	{
		return $this->Having;
	}
	
	public function getLimit()
	{
		return $this->Limit;
	}
}