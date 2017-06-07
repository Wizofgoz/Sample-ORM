<?php
namespace SampleORM;

use \SampleORM\Persistance\Persistance;
use \SampleORM\Models\Base;

class ModelMapper
{
	/*
	*	Database connection
	*
	*	@var Persistance $persistance
	*/
	private $persistance;
	
	/*
	*	Initialize the object
	*
	*	@param Persistance $persistance
	*
	*	@return void
	*/
	public function __construct(Persistance $persistance)
	{
		$this->persistance = $persistance;
	}
	
	/*
	*	Find row in Database by ID and map it to a model
	*
	*	@param int $id
	*	@param string $model
	*
	*	@return Base
	*/
	public function findById(int $id, string $model)
	{
		
		
		return $this->mapRowToModel($result, $model);
	}
	
	/*
	*	Map a given row to a model
	*
	*	@param array $row
	*	@param string $model
	*
	*	@return Base
	*/
	public function mapRowToModel(array $row, string $model)
	{
		return $model::fromState($row);
	}
}