<?php

namespace SampleORM\Persistence\Abstraction\Components;

class Condition
{
    /*
    *	Name of column the condition is to be applied to
    *
    *	@var string
    */
    protected $column;

    /*
    *	Operator to use for comparison
    *
    *	@var string
    */
    protected $operator;

    /*
    *	Value(s) to compare against
    *
    *	@var mixed
    */
    protected $value;

    /*
    *	Indicates whether the value should be treated as a column name
    *
    *	@var bool
    */
    protected $valueIsColumn = false;

    /*
    *	Initialize the object
    *
    *	@param array $args
    *
    *	@throws \Exception
    *
    *	@return void
    */
    public function __construct(...$args)
    {
        //	if 2 arguments for a condition, shortcut for =
        if (count($args) === 2) {
            $this->column = $args[0];
            $this->operator = '=';
            $this->value = $args[1];
        }
        //	if 3 arguments, 2nd is operator, 3rd is value
        elseif (count($args) === 3) {
            $this->column = $args[0];
            $this->operator = $args[1];
            $this->value = $args[2];
        }
        //	if 4 arguments, 2nd is operator, 3rd is value, 4th indicates if the value is a column name
        elseif (count($args) === 3) {
            $this->column = $args[0];
            $this->operator = $args[1];
            $this->value = $args[2];
            if (!is_bool($args[3])) {
                throw new \Exception('Expected a boolean for 4th argument');
            }
            $this->valueIsColumn = $args[3];
        }

        //	condition can't have less than 2 or more than 4 arguments
        throw new \Exception('Unexpected number of arguments in condition');
    }

    /*
    *	Returns column name of the condition
    *
    *	@return string
    */
    public function getColumn()
    {
        return $this->column;
    }

    /*
    *	Returns operator of the condition
    *
    *	@return string
    */
    public function getOperator()
    {
        return $this->operator;
    }

    /*
    *	Returns value of the condition
    *
    *	@return mixed
    */
    public function getValue()
    {
        return $this->value;
    }

    /*
    *	Returns whether the value is a column name
    *
    *	@return bool
    */
    public function valueIsColumn()
    {
        return $this->valueIsColumn;
    }
}
