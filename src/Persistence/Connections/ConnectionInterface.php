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
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return SampleORM\Helpers\Collection
    */
    public function select(SqlContainer $sql);

    /*
    *	Run an insert query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return int
    */
    public function insert(SqlContainer $sql);

    /*
    *	Run an update query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return int
    */
    public function update(SqlContainer $sql);

    /*
    *	Run a delete query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return int
    */
    public function delete(SqlContainer $sql);

    /*
    *	Run a truncate query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return bool
    */
    public function truncate(SqlContainer $sql);

    /*
    *	Run a raw query against the database
    *
    *	@param SampleORM\Persistence\SqlContainer $sql
    *
    *	@return SampleORM\Helpers\Collection
    */
    public function raw(string $sql);
}
