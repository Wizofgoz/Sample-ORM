<?php

namespace SampleORM\Persistence\Abstraction\Components;

class Limit
{
	/*
	*	Row limit to be imposed on the query
	*
	*	@var int
	*/
	protected $limit;
	
	/*
	*	Offset from first row to impose on the query
	*
	*	@var int
	*/
	protected $offset;
	
	/*
	*	Initialize the object
	*
	*	@param int $limit
	*	@param int $offset
	*
	*	@return void
	*/
	public function __construct(int $limit, int $offset = null)
	{
		$this->limit = $limit;
		$this->offset = $offset;
	}
	
	/*
	*	Return the limit
	*
	*	@return int
	*/
	public function getLimit()
	{
		return $this->limit;
	}
	
	/*
	*	Return the offset
	*
	*	@return int
	*/
	public function getOffset()
	{
		return $this->offset;
	}
}