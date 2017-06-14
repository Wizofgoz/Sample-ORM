<?php

namespace SampleORM\Persistence;

class SqlContainer
{
    /*
    *	SQL string
    *
    *	@var string
    */
    protected $sql;

    /*
    *	Array of data needing to be bound to the query
    *
    *	@var array
    */
    protected $dataBindings;

    /*
    *	Initialize the object
    *
    *	@param string $sql
    *	@param array $data
    *
    *	@return void
    */
    public function __construct(string $sql, array $data = [])
    {
        $this->sql = $sql;
        $this->dataBindings = $data;
    }

    /*
    *	Return the SQL string
    *
    *	@return string
    */
    public function getSQL()
    {
        return $this->sql;
    }

    /*
    *	Return the data to be bound
    *
    *	@return array
    */
    public function getData()
    {
        return $this->dataBindings;
    }
}
