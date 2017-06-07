<?php
namespace SampleORM\Persistence\Abstraction\Drivers;
use SampleORM\Persistence\Abstraction\Query;
use SampleORM\Collection\Collection;
use SampleORM\Persistance\Connections\PDO as Connection;

class PDODriver implements DriverInterface
{
	/*
	*	Connection to the database
	*
	*	@var \SampleORM\Persistance\Connections\PDO
	*/
	protected $connection;
	
	/*
	*	Initialize the object
	*
	*	@param \SampleORM\Persistance\Connections\PDO
	*
	*	@return void
	*/
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}
	
	/*
	*	build query portion concerning table joins
	*
	*	@param Query $query
	*	
	*	@return string
	*/
	protected function buildJoins(Query $query)
	{
		$joins = '';
		$query_joins = $query->getJoins();
		if(count($query_joins) > 0)
		{
			$tables = array_keys($query_joins);
			for($i=0; $i < count($tables); $i++)
			{
				$joins .= ' JOIN '.$tables[$i].' ON '.$query_joins[$tables[$i]][0].(count($query_joins[$tables[$i]]) > 1 ? ' AND ' : '').implode(' AND ', array_slice($query_joins[$tables[$i]], 1));
			}
		}
		return $joins;
	}
	
	/*
	*	build query portion concerning where clauses
	*
	*	@param Query $query
	*	
	*	@return array
	*/
	protected function buildWheres(Query $query)
	{
		$wheres = '';
		$data = [];
		$wheresarr = $query->getWheres();
		foreach(end($wheresarr) as $key => $value)
		{
			//	add where on first loop only, AND on all others
			$wheres .= ($wheres == '' ? ' WHERE ' : ' AND ').$key;
			if(is_array($value))
			{
				$data = array_merge($data, $value);
			}
			else
			{
				$data[] = $value;
			}
		}
		return ['placeholders' => $wheres, 'data' => $data];
	}
	
	/*
	*	build query portion concerning requested fields
	*
	*	@param Query $query
	*	
	*	@return string
	*/
	protected function buildFields(Query $query)
	{
		return (count($query->getFields()) > 0 ? implode(", ", $query->getFields()) : '*');
	}
	
	/*
	*	build query portion concerning order by clause
	*
	*	@param Query $query
	*	
	*	@return string
	*/
	protected function buildOrders(Query $query)
	{
		return (count($query->getOrder()) > 0 ? ' ORDER BY '.implode(",", $query->getOrder()) : '');
	}
	
	/*
	*	build query portion concerning group by clause
	*
	*	@param Query $query
	*	
	*	@return string
	*/
	protected function buildGroups(Query $query)
	{
		return (count($query->getGroup()) > 0 ? ' GROUP BY '.implode(",", $query->getGroup()) : '');
	}
	
	/*
	*	build query portion concerning having clauses
	*
	*	@param Query $query
	*	
	*	@return array
	*/
	protected function buildHaving(Query $query)
	{
		$having = '';
		$data = [];
		foreach($query->getHaving() as $key => $value)
		{
			//	add where on first loop only, AND on all others
			$wheres .= ($wheres == '' ? ' HAVING ' : ' AND ').$key;
			if(is_array($value))
			{
				$data = array_merge($data, $value);
			}
			else
			{
				$data[] = $value;
			}
		}
		return ['placeholders' => $having, 'data' => $data];
	}
	
	/*
	*	build query portion concerning limit clause
	*
	*	@param Query $query
	*	
	*	@return string
	*/
	protected function buildLimits(Query $query)
	{
		return ($query->getLimit() !== NULL ? ' LIMIT '.$query->getLimit() : '');
	}
	
	/*
	*	Run the built query against the DB connection as a select
	*
	*	@param Query $query
	*
	*	@throws \Exception
	*
	*	@return array
	*/
	public function select(Query $query)
	{
		if($query->getTable() != '')
		{
			$data = [];
			//	no prepared allowed
			$Fields = $this->buildFields($query);
			//	prepared allowed
			$Joins = $this->buildJoins($query);
			//$data += $Joins['data'];
			//	prepared allowed
			$Wheres = $this->buildWheres($query);
			$data = array_merge($data, $Wheres['data']);
			//	no prepared allowed
			$Orders = $this->buildOrders($query);
			//	no prepared allowed
			$Groups = $this->buildGroups($query);
			//	prepared allowed
			$Having = $this->buildHaving($query);
			$data = array_merge($data, $Having['data']);
			//	no prepared allowed
			$Limit = $this->buildLimits($query);
			$sql = "SELECT ".$Fields." FROM ".$query->getTable().$Joins.$Wheres['placeholders'].$Orders.$Groups.$Having['placeholders'].$Limit;
			$stmt = $this->connection->prepare($sql);
			$stmt->execute($data);
			return Collection::collect($stmt->fetchAll());
		}
		else
		{
			throw new \Exception('A table must be selected first');
		}
	}
	
	/*
	*	Insert the given rows
	*
	*	@param array $rows
	*	@param Query $query
	*
	*	@throws \Exception
	*
	*	@return array
	*/
	public function insert(array $rows, Query $query)
	{
		if($query->getTable() != '')
		{
			$columns = [];
			$data = [];
			//	cycle through all new rows
			for($i=0; $i < count($rows); $i++)
			{
				if(is_array($rows[$i]))
				{
					//	get array keys as column names on first round
					if(empty($columns))
					{
						$columns = array_keys($rows[$i]);
					}
					//	get array datas as values
					$count = 0;
					foreach($columns as $column)
					{
						$data[$i][] = $rows[$i][$column];
						$count++;
					}
				}
				else
				{
					throw new \Exception('Expected an array for each row');
				}
			}
			$placeholders = "";
			for($i=0; $i < count($columns); $i++)
			{
				if($placeholders == "")
				{
					$placeholders .= '?';
				}
				else
				{
					$placeholders .= ', ?';
				}
			}
			$sql = "INSERT INTO ".$query->getTable().' ('.implode(", ", $columns).') VALUES ('.$placeholders.')';
			$this->connection->beginTransaction();
			$stmt = $this->connection->prepare($sql);
			$return = [];
			foreach($data as $row)
			{
				if($stmt->execute($row))
				{
					$return[] = $this->connection->lastInsertId();
				}
				else
				{
					$return = false;
					break;
				}
			}
			if($return === false)
			{
				$this->connection->rollBack();
			}
			else
			{
				$this->connection->commit();
			}
			return $return;
		}
		else
		{
			throw new \Exception('A table must be selected first');
		}
	}
	
	/*
	*	Update with the given data
	*
	*	@param array $columns
	*	@param Query $query
	*
	*	@throws \Exception
	*
	*	@return bool
	*/
	public function update(array $columns, Query $query)
	{
		if($query->getTable() != '')
		{
			$columnNames = array_keys($columns);
			$update = [];
			$data = [];
			foreach($columnNames as $name)
			{
				$update[] = $name.' = ?';
				$data[] = $columns[$name];
			}
			$Where = $this->buildWheres($query);
			$data = array_merge($data, $Where['data']);
			$sql = "UPDATE ".$query->getTable().' SET '.implode(", ", $update).$Where['placeholders'];
			$stmt = $this->connection->prepare($sql);
			return $stmt->execute($data);
		}
		else
		{
			throw new \Exception('A table must be selected first');
		}
	}
	
	/*
	*	Delete the given rows
	*
	*	@param Query $query
	*
	*	@throws \Exception
	*
	*	@return bool
	*/
	public function delete(Query $query)
	{
		if($query->getTable() != '')
		{
			if(count($query->getWheres()) > 0)
			{
				$Where = $this->buildWheres($query);
				$data = array_merge($data, $Where['data']);
				$sql = "DELETE FROM ".$query->getTable().$Where['placeholders'];
				$stmt = $this->connection->prepare($sql);
				return $stmt->execute($data);
			}
			else
			{
				throw new \Exception('Deletes must have where constraints');
			}
		}
		else
		{
			throw new \Exception('A table must be selected first');
		}
	}
	
	/*
	*	Truncate the given table
	*
	*	@param Query $query
	*
	*	@throws \Exception
	*
	*	@return boolean
	*/
	public function truncate(Query $query)
	{
		if($query->getTable() != '')
		{
			$sql = "TRUNCATE TABLE ".$query->getTable();
			$stmt = $this->connection->prepare($sql);
			return $stmt->execute();
		}
		else
		{
			throw new \Exception('A table must be selected first');
		}
	}
	
	/*
	*	run a raw query against the DB. All applicable user-entered data should be shown as placeholders (?) 
	*	and actual data should be in $data array in order that they appear in query
	*
	*	@param string $query
	*	@param array $data
	*	@return PDOStatement
	*/
	public function raw($query, array $data = [])
	{
		$stmt = $this->connection->prepare($query);
		if($stmt->execute($data))
		{
			return $stmt;
		}
	}
}