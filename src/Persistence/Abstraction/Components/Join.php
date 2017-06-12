<?php

namespace SampleORM\Persistence\Abstraction\Components;

class Join
{
    /*
    *	Name of table to join to
    *
    *	@var string
    */
    protected $table;

    /*
    *	Type of Join to use
    *
    *	@var string
    */
    protected $type;

    /*
    *	Conditions for the ON clause
    *
    *	@var Condition[]
    */
    protected $conditions = [];

    protected $availableTypes = [
        'JOIN',
        'INNER JOIN',
        'OUTER JOIN',
        'LEFT JOIN',
        'RIGHT JOIN',
        'CROSS JOIN',
        'LEFT INNER JOIN',
        'RIGHT INNER JOIN',
        'LEFT OUTER JOIN',
        'RIGHT OUTER JOIN',
    ];

    /*
    *	Initialize the object
    *
    *	@param string|string[] $table
    *	@param array $conditions
    *	@param string $type
    *
    *	@throws \Exception
    *
    *	@return void
    */
    public function __construct($table, array $conditions, string $type = 'JOIN')
    {
        //	if $table is an array, the user is setting an alias
        if (is_array($table)) {
            $table = $table[0].' AS '.$table[1];
        }
        $this->table = $table;
        if (!in_array($type, $this->availableTypes)) {
            throw new \Exception('Invalid JOIN type given');
        }
        $this->type = $type;
        foreach ($conditions as $condition) {
            //	each condition must be an array or a Condition Object
            if (is_array($condition)) {
                $this->conditions[] = new Condition(...$condition);
                continue;
            } elseif ($condition instanceof Condition) {
                $this->conditions[] = $condition;
            }

            throw new \Exception('Expected an array or object of type '.Condition::class);
        }
    }

    /*
    *	Returns table name of the join
    *
    *	@return string
    */
    public function getTable()
    {
        return $this->table;
    }

    /*
    *	Returns type of the join
    *
    *	@return string
    */
    public function getType()
    {
        return $this->type;
    }

    /*
    *	Returns conditions on the join
    *
    *	@return mixed
    */
    public function getConditions()
    {
        return $this->conditions;
    }
}
