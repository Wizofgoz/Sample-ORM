<?php
namespace SampleORM\Persistance;
interface DriverInterface
{
	public function persist(\SampleORM\Models\Base $model);
	
	public function retrieve($id);
	
	public function delete($id);
}