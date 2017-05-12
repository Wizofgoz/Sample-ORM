<?php
namespace SampleORM;
use \SampleORM\Persistence\Abstraction\Drivers\DriverInterface;
use \SampleORM\Models\Base;

class ModelMapper
{
	private $adapter;
	
	public function __construct(DriverInterface $adapter)
	{
		$this->adapter = $adapter;
	}
	
	public function findById(int $id, string $model): Base
	{
		
		
		return $this->mapRowToModel($result, $model);
	}
	
	public function mapRowToModel(array $row, string $model): Base
	{
		return $model::fromState($row);
	}
}