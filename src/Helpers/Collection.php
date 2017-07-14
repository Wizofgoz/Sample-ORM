<?php

namespace SampleORM\Helpers;

use ArrayAccess;
use Closure;
use Countable;
use Iterator;

class Collection implements Countable, ArrayAccess, Iterator
{
    /*
    *	Current position of the pointer
    *
    *	@var int
    */
    private $position = 0;

    /*
    *	Container for holding data internally
    *
    *	@var array
    */
    private $container = [];

    /*
    *	Initialize the object
    *
    *	@param array $array
    *
    *	@return void
    */
    public function __construct(array $array = [])
    {
        $this->position = 0;
        $this->container = $array;
    }

    /*
    *	Gets the data at the current position
    *
    *	@return mixed
    */
    public function current()
    {
        return $this->container[$this->position];
    }

    /*
    *	Gets the current position index
    *
    *	@return int
    */
    public function key()
    {
        return $this->position;
    }

    /*
    *	Moves the position ahead one
    *
    *	@return void
    */
    public function next()
    {
        ++$this->position;
    }

    /*
    *	Moves the position back one
    *
    *	@return void
    */
    public function rewind()
    {
        $this->position = 0;
    }

    /*
    *	Checks that the current position is valid
    *
    *	@return bool
    */
    public function valid()
    {
        return isset($this->container[$this->position]);
    }

    /*
    *	Checks that a given offset exists
    *
    *	@param mixed $offset
    *
    *	@return bool
    */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /*
    *	Returns data from the given offset
    *
    *	@param mixed $offset
    *
    *	@return mixed|null
    */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /*
    *	Sets the given offset with the given data
    *
    *	@param mixed $offset
    *	@param mixed $value
    *
    *	@return void
    */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;

            return;
        }

        $this->container[$offset] = $value;
    }

    /*
    *	Unsets a given offset
    *
    *	@param mixed $offset
    *
    *	@return void
    */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /*
    *	Returns the number of items in the collection
    *
    *	@return int
    */
    public function count()
    {
        return count($this->container);
    }

    /*
    *	Checks if a value is in the collection
    *
    *	@param mixed $first
    *	@param mixed $second
    *
    *	@return bool
    */
    public function contains($first, $second = null)
    {
        if ($second === null) {
            foreach ($this->container as $value) {
                if ($value == $first) {
                    return true;
                }
            }

            return false;
        }

        return $this->container[$first] == $second;
    }

    /*
    *	Applies a given closure function to all members of the collection
    *
    *	@param Closure $closure
    *
    *	@return void
    */
    public function each(Closure $closure)
    {
        foreach ($this->container as $key => $value) {
            if ($closure($key, $value)) {
                return;
            }
        }
    }
}
