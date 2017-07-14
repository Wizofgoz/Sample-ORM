<?php

namespace SampleORM\Persistence\Grammars;

use SampleORM\Persistence\Query;

interface GrammarInterface
{
    /*
    *	Compile a select query
    *
    *	@param SampleORM\Persistence\Query $query
    *
    *	@return array
    */
    public function select(Query $query);

    /*
    *	Compile an insert query
    *
    *	@param array $rows
    *	@param SampleORM\Persistence\Query $query
    *
    *	@return array
    */
    public function insert(array $rows, Query $query);

    /*
    *	Compile an update query
    *
    *	@param array $columns
    *	@param SampleORM\Persistence\Query $query
    *
    *	@return array
    */
    public function update(array $columns, Query $query);

    /*
    *	Compile a delete query
    *
    *	@param SampleORM\Persistence\Query $query
    *
    *	@return array
    */
    public function delete(Query $query);

    /*
    *	Compile a truncate query
    *
    *	@param SampleORM\Persistence\Query $query
    *
    *	@return array
    */
    public function truncate(Query $query);
}
