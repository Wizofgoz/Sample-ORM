<?php

namespace SampleORM\Persistence\Components;

class Order
{
    /*
    *	Column to order by
    *
    *	@var string
    */
    protected $column;

    /*
    *	Direction to order in (ASC, DESC)
    *
    *	@var string
    */
    protected $direction;

    /*
    *	Initialize the object
    *
    *	@param string $column
    *	@param string $direction
    *
    *	@return void
    */
    public function __construct(string $column, string $direction = 'ASC')
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /*
    *	Return the column
    *
    *	@return string
    */
    public function getColumn()
    {
        return $this->column;
    }

    /*
    *	Return the direction
    *
    *	@return string
    */
    public function getDirection()
    {
        return $this->direction;
    }
}
