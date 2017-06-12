<?php

namespace SampleORM\Persistence\Abstraction;

class Query
{
    /*
    *	Array of fields to retrieve in the query
    *
    *	@var string[]
    */
    protected $fields = [];

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
    protected $where = [];

    /*
    *	Array of join conditions to apply to the query
    *
    *	@var mixed[]
    */
    protected $joins = [];

    /*
    *	Array of ordering conditions to apply to the query
    *
    *	@var mixed[]
    */
    protected $order = [];

    /*
    *	Array of grouping conditions to apply to the query
    *
    *	@var mixed[]
    */
    protected $group = [];

    /*
    *	Array of having conditions to apply to the query
    *
    *	@var mixed[]
    */
    protected $having = [];

    /*
    *	Array of unions to apply to the query
    *
    *	@var Query[]
    */
    protected $unions = [];

    /*
    *	Limit clause to apply to the query
    *
    *	@var string
    */
    protected $limit;

    /*
    *	Connection to the database
    *
    *	@var \SampleORM\Persistence\Drivers\DriverInterface
    */
    protected $connection;

    /*
    *	Set what fields to retreive in the query
    *
    *	@param string|string[] $fields
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function setFields($fields)
    {
        if (is_array($fields) || is_string($fields)) {
            if (is_array($fields)) {
                $this->fields = $fields;

                return $this;
            }
            $this->fields[] = $fields;

            return $this;
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
    *	@return Query
    */
    public function setTable($table, $alias = null)
    {
        if (is_string($table)) {
            $this->table = $table.($alias === null ? '' : ' AS '.$alias);

            return $this;
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
    *	@return Query
    */
    public function setWhere(...$args)
    {
        //	if only 1 argument in the array
        if (count($args[0]) === 1) {
            //	the argument has to be an array
            if (is_array($args[0])) {
                foreach ($args[0] as $condition) {
                    //	each condition must be an array
                    if (is_array($condition)) {
                        $this->where[] = new Condition(...$condition);
                        continue;
                    }

                    throw new \Exception('Expected an array');
                }

                return $this;
            }

            throw new \Exception('Expected an array');
        }

        $this->where[] = new Condition(...$args);

        return $this;
    }

    /*
    *	Add a join to the query
    *
    *	@param string|string[] $table
    *	@param array $args
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function addJoin($table, ...$args)
    {
        $this->joins[] = new Join($table, ...$args);

        return $this;
    }

    /*
    *	Sets a limit for the query
    *
    *	@param int $limit
    *	@param int $offset
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function setLimit($limit, $offset = null)
    {
        $this->limit = new Limit($limit, $offset);

        return $this;
    }

    /*
    *	Sets ordering conditions for the query
    *
    *	@param string|string[] $order
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function setOrder($order)
    {
        if (is_array($order)) {
            //	if it's a multi-dimensional array, there are multiple ordering constraints
            if (is_array($order[0])) {
                foreach ($order as $constraint) {
                    $this->order[] = new Order(...$constraint);
                }

                return $this;
            }

            $this->order[] = new Order(...$order);

            return $this;
        } elseif (is_string($order)) {
            $this->order[] = new Order($order);

            return $this;
        }

        throw new \Exception('Expected either a string or an array');
    }

    /*
    *	Add group by constraint(s) to the query
    *
    *	@param string|string[] $column
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function groupBy($column)
    {
        if (is_array($column)) {
            foreach ($column as $constraint) {
                $this->group[] = new Group($constraint);
            }

            return $this;
        } elseif (is_string($column)) {
            $this->group[] = new Group($column);

            return $this;
        }

        throw new \Exception('Expected either a string or an array');
    }

    /*
    *	Add having constraints to the query
    *
    *	@param array[] $having
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function having(array $having)
    {
        //	if only 1 argument in the array
        if (count($args[0]) === 1) {
            //	the argument has to be an array
            if (is_array($args[0])) {
                foreach ($args[0] as $condition) {
                    //	each condition must be an array
                    if (is_array($condition)) {
                        $this->having[] = new Condition(...$condition);
                        continue;
                    }

                    throw new \Exception('Expected an array');
                }

                return $this;
            }

            throw new \Exception('Expected an array');
        }

        $this->having[] = new Condition(...$args);

        return $this;
    }

    /*
    *	Adds condition to Where array using the IN syntax
    *
    *	@param string $column
    *	@param mixed $values
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function in($column, $values)
    {
        if (is_string($column)) {
            $this->where[] = Condition::in($column, $values);

            return $this;
        }

        throw new \Exception('Expected a string');
    }

    /*
    *	Adds condition to Where array using the NOT IN syntax
    *
    *	@param string $column
    *	@param array $values
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function notIn($column, array $values)
    {
        if (is_string($column)) {
            $this->where[] = Condition::notIn($column, $values);

            return $this;
        }

        throw new \Exception('Expected a string');
    }

    /*
    *	Add a union to the query
    *
    *	@param Query|\Closure $query
    *
    *	@return Query
    */
    public function union($query)
    {
        if ($query instanceof \Closure) {
            $function = $query;
            $query = $function(new static());
        }
        $this->unions[] = $query;

        return $this;
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
