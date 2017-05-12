<?php
namespace SampleORM\Persistence\Abstraction\Drivers;
interface DriverInterface
{
	public function persist(\SampleORM\Models\Base $model);
	
	public function retrieve($id);
	
	public function delete($id);
}