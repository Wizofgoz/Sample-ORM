<?php

namespace SampleORM\Persistence\Abstraction\Grammars;

use SampleORM\Persistence\Abstraction\Query;
use SampleORM\Persistence\Abstraction\Components\Condition;
use SampleORM\Persistence\SqlContainer;

class MySql implements GrammarInterface
{
	/*
	*	Array of data to be bound in the query execution
	*
	*	@var array
	*/
	protected $dataBindings = [];
	
    /*
    *	build query portion concerning table joins
    *
    *	@param Query $query
    *
    *	@return string
    */
    protected function buildJoins(Query $query)
    {
        $joins = '';
        $queryJoins = $query->getJoins();
		foreach ($queryJoins as $join) {
			$onConditions = $join->getConditions();
			$joins .= " {$join->getType()} {$join->getTable()}";
			if (count($onConditions) > 0) {
				$joins .= " ON ";
				$first = true;
				foreach ($onConditions as $condition) {
					$joins .= ($first ? '' : ' AND ')."{$condition->getColumn()} {$condition->getOperator()} {$this->resolveConditionValue($condition)}";
					$first = false;
				}
			}
		}

        return $joins;
    }
	
	/*
	*	Resolve a condition into it's SQL equivalent
	*
	*	@param SampleORM\Persistence\Abstraction\Components\Condition $condition
	*
	*	@return mixed
	*/
	protected function resolveConditionValue(Condition $condition)
	{
		// resolve subquery
		if ($condition->getValue() instanceof Query) {
			return $this->select($condition->getValue());
		} 
		// simply return the value if it's meant to specify a column
		elseif ($condition->valueIsColumn()) {
			return $condition->getValue();
		}
		// bind the value and return a placeholder
		// value could be an array for IN types so just encapsulate all non-arrays
		$valueArr = (is_array($condition->getValue()) ? $condition->getValue() : [$condition->getValue()]);
		$this->dataBindings[] = array_merge($valueArr);
		
		return '?';
	}

    /*
    *	build query portion concerning where clauses
    *
    *	@param Query $query
    *
    *	@return array
    */
    protected function buildWheres(Query $query)
    {
        $wheres = '';
		if (count($query->getWheres()) > 0) {
			$first = true;
			$wheres .= " WHERE ";
			foreach ($query->getWheres() as $condition) {
				$wheres .= ($first ? '' : ' AND ')."{$condition->getColumn()} {$condition->getOperator} {$this->resolveConditionValue($condition)}";
				$first = false;
			}
		}

        return $wheres;
    }

    /*
    *	build query portion concerning requested fields
    *
    *	@param Query $query
    *
    *	@return string
    */
    protected function buildFields(Query $query)
    {
		if (count($query->getFields()) == 0) {
			return '*';
		}
		$fields = [];
        foreach ($query->getFields() as $field) {
			// resolve subqueries
			if ($field instanceof Query) {
				$fields[] = $this->select($field);
				continue;
			}
			$fields[] = $this->columnize($field);
		}
		
		return implode(', ', $fields);
    }
	
	/*
	*	Wrap the value in backticks
	*
	*	@param string $value
	*
	*	@return string
	*/
	protected function columnize(string $value)
	{
		return '`'.$value.'`';
	}

    /*
    *	build query portion concerning order by clause
    *
    *	@param Query $query
    *
    *	@return string
    */
    protected function buildOrders(Query $query)
    {
		$orders = '';
		if (count($query->getOrder()) > 0) {
			$orders .= ' ORDER BY ';
			$first = true;
			foreach ($query->getOrder() as $order) {
				$orders .= ($first ? '' : ', ')."{$order->getColumn()} {$order->getDirection()}";
				$first = false;
			}
		}
		
		return $orders;
    }

    /*
    *	build query portion concerning group by clause
    *
    *	@param Query $query
    *
    *	@return string
    */
    protected function buildGroups(Query $query)
    {
        $groups = '';
		if (count($query->getGroup()) > 0) {
			$groups .= ' ORDER BY ';
			$first = true;
			foreach ($query->getGroup() as $group) {
				$groups .= ($first ? '' : ', ')."{$group->getColumn()}";
				$first = false;
			}
		}
		
		return $groups;
    }

    /*
    *	build query portion concerning having clauses
    *
    *	@param Query $query
    *
    *	@return array
    */
    protected function buildHaving(Query $query)
    {
        $havings = '';
		if (count($query->getHaving()) > 0) {
			$first = true;
			$havings .= " HAVING ";
			foreach ($query->getHaving() as $condition) {
				$havings .= ($first ? '' : ' AND ')."{$condition->getColumn()} {$condition->getOperator} {$this->resolveConditionValue($condition)}";
				$first = false;
			}
		}

        return $havings;
    }

    /*
    *	build query portion concerning limit clause
    *
    *	@param Query $query
    *
    *	@return string
    */
    protected function buildLimits(Query $query)
    {
		$limit = '';
		if ($query->getLimit() instanceof Limit) {
			$limitObj = $query->getLimit();
			$limit .= " LIMIT {$limitObj->getLimit()}, {$limitObj->getOffset()}";
		}
		
        return $limit;
    }

    /*
    *	Run the built query against the DB connection as a select
    *
    *	@param Query $query
    *
    *	@throws \Exception
    *
    *	@return SampleORM\Persistence\SqlContainer
    */
    public function select(Query $query)
    {
        if ($query->getTable() != '') {
            $sql = "SELECT {$this->buildFields($query)} FROM {$this->columnize($query->getTable())}".
				"{$this->buildJoins($query)}{$this->buildWheres($query)}{$this->buildOrders($query)}".
				"{$this->buildGroups($query)}{$this->buildHaving($query)}{$this->buildLimits($query)}";
			
			return new SqlContainer($sql, $this->dataBindings);
        }

        throw new \Exception('A table must be selected first');
    }

    /*
    *	Insert the given rows
    *
    *	@param array $rows
    *	@param Query $query
    *
    *	@throws \Exception
    *
    *	@return SampleORM\Persistence\SqlContainer
    */
    public function insert(array $rows, Query $query)
    {
        if ($query->getTable() != '') {
            $columns = [];
            $data = [];
            //	cycle through all new rows
            for ($i = 0; $i < count($rows); $i++) {
                if (is_array($rows[$i])) {
                    //	get array keys as column names on first round
                    if (empty($columns)) {
                        $columns = array_map(
							function($element)
							{ 
								return $this->columnize($element); 
							}, 
							array_keys($rows[$i])
						);
                    }
                    //	get array datas as values
                    $count = 0;
                    foreach ($columns as $column) {
                        $data[$i][] = $rows[$i][$column];
                        $count++;
                    }

                    continue;
                }

                throw new \Exception('Expected an array for each row');
            }
            $placeholders = '';
            for ($i = 0; $i < count($columns); $i++) {
                if ($placeholders == '') {
                    $placeholders .= '(?';
                    continue;
                }

                $placeholders .= ', ?';
            }
			$placeholders .= ')';
			$placeholdersArr = [];
			$placeholders = implode(', ', array_pad($placeholders, count($rows), $placeholders));
            $sql = "INSERT INTO {$this->columnize($query->getTable())} (".implode(', ', $columns).") VALUES {$placeholders}";
			
            return new SqlContainer($sql, $data);
        }

        throw new \Exception('A table must be selected first');
    }

    /*
    *	Update with the given data
    *
    *	@param array $columns
    *	@param Query $query
    *
    *	@throws \Exception
    *
    *	@return SampleORM\Persistence\SqlContainer
    */
    public function update(array $columns, Query $query)
    {
        if ($query->getTable() != '') {
            $columnNames = array_keys($columns);
            $update = [];
            $data = [];
            foreach ($columnNames as $name) {
                $update[] = $name.' = ?';
                $data[] = $columns[$name];
            }
            $sql = "UPDATE {$this->columnize($query->getTable())} SET ".implode(', ', $update).
				"{$this->buildWheres($query)}{$this->buildOrders($query)}{$this->buildGroups($query)}".
				"{$this->buildHaving($query)}{$this->buildLimits($query)}";
            
			return new SqlContainer($sql, array_merge($data, $this->dataBindings));
        }

        throw new \Exception('A table must be selected first');
    }

    /*
    *	Delete the given rows
    *
    *	@param Query $query
    *
    *	@throws \Exception
    *
    *	@return SampleORM\Persistence\SqlContainer
    */
    public function delete(Query $query)
    {
        if ($query->getTable() != '') {
			$sql = "DELETE FROM {$this->columnize($query->getTable())}{$this->buildWheres($query)}".
				"{$this->buildOrders($query)}{$this->buildGroups($query)}".
				"{$this->buildHaving($query)}{$this->buildLimits($query)}";
			
			return new SqlContainer($sql, array_merge($data, $this->dataBindings));
        }

        throw new \Exception('A table must be selected first');
    }

    /*
    *	Truncate the given table
    *
    *	@param Query $query
    *
    *	@throws \Exception
    *
    *	@return SampleORM\Persistence\SqlContainer
    */
    public function truncate(Query $query)
    {
        if ($query->getTable() != '') {
            $sql = "TRUNCATE TABLE {$this->columnize($query->getTable())}";
			
			return new SqlContainer($sql);
        }

        throw new \Exception('A table must be selected first');
    }
}
