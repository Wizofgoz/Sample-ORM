<?php

namespace SampleORM\Persistence;

use Closure;
use SampleORM\Persistence\Connections\ConnectionInterface;
use SampleORM\Persistence\Grammars\GrammarInterface;

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
    *	@var \SampleORM\Persistence\Connections\ConnectionInterface
    */
    protected $connection;

    /*
    *	Grammar for compiling the query to sql
    *
    *	@var \SampleORM\Persistence\Grammars\GrammarInterface
    */
    protected $grammar;

    /*
    *	Initialize the object
    *
    *	@param \SampleORM\Persistence\Connections\ConnectionInterface $connection
    *	@param \SampleORM\Persistence\Grammars\GrammarInterface $grammar
    *
    *	@return void
    */
    public function __construct(ConnectionInterface $connection, GrammarInterface $grammar)
    {
        $this->connection = $connection;
        $this->grammar = $grammar;
    }

    /*
    *	Connection to the database
    *
    *	@var \SampleORM\Persistence\Connections\ConnectionInterface
    */
    protected $connection;

    /*
    *	Set what fields to retreive in the query
    *
    *	@param string|mixed[]|\Closure|Query $fields
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    public function select($fields)
    {
        // handle array
        if (is_array($fields)) {
            foreach ($fields as $field) {
                if (is_string($field) || $field instanceof self) {
                    $this->fields[] = $field;
                    continue;
                } elseif ($field instanceof Closure) {
                    $function = $field;
                    $this->fields[] = $this->handleClosure($function);
                    continue;
                }

                throw new \Exception('Expected a string, Query object or Closure');
            }

            return $this;
        }
        // handle string/Query
        elseif (is_string($fields) || $fields instanceof self) {
            $this->fields[] = $fields;

            return $this;
        }
        // handle Closure
        elseif ($fields instanceof Closure) {
            $function = $fields;
            $this->fields[] = $this->handleClosure($function);

            return $this;
        }

        throw new \Exception('Expected a string, Query object, Closure, or array');
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
    public function table($table, $alias = null)
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
    public function where(...$args)
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
            // or a Query object
            elseif ($args[0] instanceof self) {
                $this->where[] = $args[0];

                return $this;
            }
            // or a Closure
            elseif ($args[0] instanceof Closure) {
                $function = $args[0];
                $this->where[] = $this->handleClosure($function);

                return $this;
            }
            throw new \Exception('Expected an array');
        }

        $this->where[] = new Condition(...$args);

        return $this;
    }

    /*
    *	Return a new Query object
    *
    *	@return \SampleORM\Persistence\Abstraction\Query
    */
    protected function newQuery()
    {
        return new static($this->connection, $this->grammar);
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
    public function join($table, ...$args)
    {
        // if the table is an array, user is setting an alias
        if (is_array($table) || $table instanceof self) {
            // if the first element is a closure, it's a subquery
            if ($table[0] instanceof Closure) {
                $function = $table[0];
                $table = [$this->handleClosure($function), $table[1]];
            }
            $this->joins[] = new Join($table, ...$args);

            return $this;
        }
        // if the table is a Closure, it's a subquery
        elseif ($table instanceof Closure) {
            $function = $table;
            $this->joins[] = new Join($this->handleClosure($function), ...$args);

            return $this;
        }
        // otherwise
        $this->joins[] = new Join($table, ...$args);

        return $this;
    }

    protected function handleClosure($closure)
    {
        return $closure($this->newQuery());
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
    public function limit($limit, $offset = null)
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
    public function orderBy($order)
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
    public function whereIn($column, $values)
    {
        return $this->inCondition($column, 'IN', $values);
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
    public function whereNotIn($column, $values)
    {
        return $this->inCondition($column, 'NOT IN', $values);
    }

    /*
    *	Handles IN and NOT IN conditions
    *
    *	@param string $column
    *	@param string $operator
    *	@param mixed $values
    *
    *	@throws \Exception
    *
    *	@return Query
    */
    protected function inCondition($column, $operator, $values)
    {
        if (is_string($column)) {
            if (is_array($values) || $values instanceof self) {
                $this->where[] = new Condition($column, $operator, $values);

                return $this;
            } elseif ($values instanceof Closure) {
                $function = $values;
                $this->where[] = new Condition($column, $operator, $this->handleClosure($function));

                return $this;
            }

            throw new \Exception('Expected an array, Query object, or Closure for values');
        }

        throw new \Exception('Expected a string for column');
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
        if ($query instanceof Closure) {
            $function = $query;
            $query = $this->handleClosure($function);
        }
        $this->unions[] = $query;

        return $this;
    }

    /*
    *	Run the query as a select
    *
    *	@return \SampleORM\Helpers\Collection
    */
    public function get()
    {
        return $this->connection->select($this->grammar->select($this));
    }

    /*
    *	Run the query as an insert
    *
    *	@return int
    */
    public function insert(array $rows)
    {
        foreach ($values as $key => $value) {
            ksort($value);
            $values[$key] = $value;
        }

        return $this->connection->insert($this->grammar->insert($rows, $this));
    }

    /*
    *	Run the query as an update
    *
    *	@return int
    */
    public function update(array $data)
    {
        return $this->connection->update($this->grammar->update($data, $this));
    }

    /*
    *	Run the query as a delete
    *
    *	@return int
    */
    public function delete()
    {
        return $this->connection->delete($this->grammar->delete($this));
    }

    /*
    *	Run the query as a truncate
    *
    *	@return bool
    */
    public function truncate()
    {
        return $this->connection->truncate($this->grammar->truncate($this));
    }

    /*
    *	Run a raw query
    *
    *	@return \SampleORM\Collection\Collection
    */
    public function raw(string $sql, array $data = [])
    {
        return $this->connection->raw(new SqlContainer($sql, $data));
    }

    /*
    *	Add a union to the query (not finished yet)
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
    *	@return Limit
    */
    public function getLimit()
    {
        return $this->limit;
    }

    /*
    *	Returns the set unions for the query
    *
    *	@return array
    */
    public function getUnions()
    {
        return $this->unions;
    }
}
