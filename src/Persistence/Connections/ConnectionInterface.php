<?php

namespace SampleORM\Persistence\Connections;

use SampleORM\Config\ConfigManager;

interface ConnectionInterface
{
	/*
	*	Create a new instance from configuration
	*
	*	@param SampleORM\Config\ConfigManager $config
	*
	*	@return SampleORM\Persistence\Connections\ConnectionInterface
	*/
	public static function fromConfiguration(ConfigManager $config);
	
	/*
	*	Run a select query against the database
	*
	*	@param string $sql
	*	@param array $data
	*
	*	@return SampleORM\Collection\Collection
	*/
	public function select(string $sql, array $data);
	
	/*
	*	Run an insert query against the database
	*
	*	@param string $sql
	*	@param array $data
	*
	*	@return bool
	*/
	public function insert(string $sql, array $data);
	
	/*
	*	Run a select query against the database
	*
	*	@param string $sql
	*	@param array $data
	*
	*	@return int
	*/
	public function update(string $sql, array $data);
	
	/*
	*	Run a delete query against the database
	*
	*	@param string $sql
	*	@param array $data
	*
	*	@return int
	*/
	public function delete(string $sql, array $data);
}