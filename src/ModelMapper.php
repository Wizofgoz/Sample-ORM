<?php
namespace SampleORM;
use \SampleORM\Persistance\DriverInterface;
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