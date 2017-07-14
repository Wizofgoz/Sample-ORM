<?php

namespace SampleORM\Persistence\Connections;

use PDO as BasePDO;
use SampleORM\Config\ConfigManager;
use SampleORM\Persistence\SqlContainer as SQL;
use SampleORM\Helpers\Collection;

class PDO extends BasePDO implements ConnectionInterface
{
    public static function fromConfiguration(ConfigManager $config)
    {
        return new self($config->__CLASS__->dsn, $config->__CLASS__->username, $config->__CLASS__->password, $config->__CLASS__->options);
    }
	
	/*
    *	Run a select query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return SampleORM\Helpers\Collection
    */
	public function select(SQL $sql)
	{
		return $this->returnCollection($sql);
	}
	
	/*
    *	Run an insert query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return int
    */
	public function insert(SQL $sql)
	{
		return $this->returnAffected($sql);
	}
	
	/*
    *	Run an update query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return int
    */
	public function update(SQL $sql)
	{
		return $this->returnAffected($sql);
	}
	
	/*
    *	Run a delete query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return int
    */
	public function delete(SQL $sql)
	{
		return $this->returnAffected($sql);
	}
	
	/*
    *	Run a truncate query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return bool
    */
	public function truncate(SQL $sql)
	{
		$stmt = $this->prepare($sql->getSQL());
		$result = $stmt->execute($sql->getData());
		$stmt->close();
		return $result;
	}
	
	/*
    *	Run a raw query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return SampleORM\Helpers\Collection
    */
	public function raw(SQL $sql)
	{
		return $this->returnCollection($sql);
	}
	
	/*
	*	Run a query that needs to return number of rows affected
	*
	*	@param SampleORM\Persistence\SqlContainer $sql
	*
	*	@return int
	*/
	protected function returnAffected(SQL $sql)
	{
		$stmt = $this->run($sql);
		$result = $stmt->rowCount();
		$stmt->close();
		return $result;
	}
	
	/*
	*	Run a query that need to return a collection
	*
	*	@param SampleORM\Persistence\SqlContainer $sql
	*
	*	@return SampleORM\Helpers\Collection
	*/
	protected function returnCollection(SQL $sql)
	{
		$stmt = $this->run($sql);
		$result = new Collection($stmt->fetchAll());
		$stmt->close();
		return $result;
	}
	
	/*
	*	Run the query and return the ran statement
	*
	*	@param SampleORM\Persistence\SqlContainer $sql
	*
	*	@return PDOStatement
	*/
	protected function run(SQL $sql)
	{
		$stmt = $this->prepare($sql->getSQL());
		$stmt->execute($sql->getData());
		return $stmt;
	}
}
