<?php

namespace SampleORM\Persistence\Components;

class Group
{
    /*
    *	Column to group by
    *
    *	@var string
    */
    protected $column;

    /*
    *	Initialize the object
    *
    *	@param string $column
    *
    *	@return void
    */
    public function __construct(string $column)
    {
        $this->column = $column;
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
}
