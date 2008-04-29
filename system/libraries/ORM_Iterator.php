<?php defined('SYSPATH') or die('No direct script access.');
/**
* Object Relational Mapping (ORM) result iterator.
*
* $Id$
*
* @package    Core
* @author     Kohana Team
* @copyright  (c) 2007-2008 Kohana Team
* @license    http://kohanaphp.com/license.html
*/
class ORM_Iterator_Core implements Iterator, ArrayAccess, Countable {

	// ORM class name
	protected $class;

	// Database result object
	protected $result;

	public function __construct($class, $result)
	{
		// Class name
		$this->class = $class;

		// Database result
		$this->result = $result;
	}

	/**
	 * Returns an array of the results as ORM objects.
	 *
	 * @return  array
	 */
	public function as_array()
	{
		// Import class name
		$class = $this->class;

		$array = array();
		foreach ($this->result->result_array(TRUE) as $obj)
		{
			$array[] = new $class($obj);
		}
		return $array;
	}

	/**
	 * Create a key/value array from the results.
	 *
	 * @param   string  key column
	 * @param   string  value column
	 * @return  array
	 */
	public function select_list($key, $val)
	{
		$array = array();
		foreach ($this->result->result_array(TRUE) as $row)
		{
			$array[$row->$key] = $row->$val;
		}
		return $array;
	}

	/**
	 * Return a range of offsets.
	 *
	 * @param   integer  start
	 * @param   integer  end
	 * @return  array
	 */
	public function range($start, $end)
	{
		// Array of objects
		$array = array();

		if ($this->result->offsetExists($start))
		{
			// Import the class name
			$class = $this->class;

			// Set the end offset
			$end = $this->result->offsetExists($end) ? $end : $this->count();

			for ($i = $start; $i < $end; $i++)
			{
				// Insert each object in the range
				$array[] = new $class($this->result->offsetGet($i));
			}
		}

		return $array;
	}

	/**
	 * Countable: count
	 */
	public function count()
	{
		return $this->result->count();
	}

	/**
	 * Iterator: current
	 */
	public function current()
	{
		// Import class name
		$class = $this->class;

		return ($row = $this->result->current()) ? new $class($row) : FALSE;
	}

	/**
	 * Iterator: key
	 */
	public function key()
	{
		return $this->result->key();
	}

	/**
	 * Iterator: next
	 */
	public function next()
	{
		return $this->result->next();
	}

	/**
	 * Iterator: rewind
	 */
	public function rewind()
	{
		$this->result->rewind();
	}

	/**
	 * Iterator: valid
	 */
	public function valid()
	{
		return $this->result->valid();
	}

	/**
	 * ArrayAccess: offsetExists
	 */
	public function offsetExists($offset)
	{
		return $this->result->offsetExists($offset);
	}

	/**
	 * ArrayAccess: offsetGet
	 */
	public function offsetGet($offset)
	{
		// Import class name
		$class = $this->class;

		if ($this->result->offsetExists($offset))
		{
			return new $class($this->result->offsetGet($offset));
		}
	}

	/**
	 * ArrayAccess: offsetSet
	 *
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetSet($offset, $value)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

	/**
	 * ArrayAccess: offsetUnset
	 *
	 * @throws  Kohana_Database_Exception
	 */
	public function offsetUnset($offset)
	{
		throw new Kohana_Database_Exception('database.result_read_only');
	}

} // End ORM Iterator