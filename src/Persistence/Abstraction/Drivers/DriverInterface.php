<?php
namespace SampleORM\Persistence\Abstraction\Drivers;

use SampleORM\Persistence\Abstraction\Query;

interface DriverInterface
{
	public function select(Query $query);
	
	public function insert(array $rows, Query $query);
	
	public function update(array $columns, Query $query);
	
	public function delete(Query $query);
}