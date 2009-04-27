<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database result wrapper.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Database_Result_Core implements Countable, Iterator, SeekableIterator, ArrayAccess {

	protected $_result;

	protected $_total_rows  = 0;
	protected $_current_row = 0;
	protected $_insert_id;

	// Return objects or arrays for each row
	protected $_return_objects;

	/**
	 * Sets the total number of rows and stores the result locally.
	 *
	 * @param   mixed   $result query result
	 * @param   boolean $return_objects True for results as objects, false for arrays
	 * @return  void
	 */
	abstract public function __construct($result, $sql, $link, $return_objects);

	/**
	 * Result destruction cleans up all open result sets.
	 */
	abstract public function __destruct();

	/**
	 * Return arrays for reach result, or the entire set of results
	 *
	 * @param  boolean $return  True to return entire result array
	 * @return Database_Result|array
	 */
	abstract public function as_array($return = FALSE);

	/**
	 * Returns objects for each result
	 *
	 * @param  string $class  Class name to return objects as or NULL for stdClass
	 * @return Database_Result
	 */
	abstract public function as_object($class = NULL);

	/**
	 * Returns the insert id
	 *
	 * @return int
	 */
	public function insert_id()
	{
		return $this->_insert_id;
	}

	/**
	 * Return the named column from the current row.
	 *
	 * @param  string  Column name
	 * @return mixed
	 */
	public function get($name)
	{
		// Get the current row
		$row = $this->current();

		return $row[$name];
	}

	/**
	 * Countable: count
	 */
	public function count()
	{
		return $this->_total_rows;
	}

	/**
	 * ArrayAccess: offsetExists
	 */
	public function offsetExists($offset)
	{
		if ($this->_total_rows > 0)
		{
			$min = 0;
			$max = $this->_total_rows - 1;

			return ! ($offset < $min OR $offset > $max);
		}

		return FALSE;
	}

	/**
	 * ArrayAccess: offsetSet
	 *
	 * @throws  Kohana_Database_Exception
	 */
	final public function offsetSet($offset, $value)
	{
		throw new Kohana_Exception('Database results are read-only');
	}

	/**
	 * ArrayAccess: offsetUnset
	 *
	 * @throws  Kohana_Database_Exception
	 */
	final public function offsetUnset($offset)
	{
		throw new Kohana_Exception('Database results are read-only');
	}

	/**
	 * Iterator: current
	 */
	public function current()
	{
		return $this->offsetGet($this->_current_row);
	}

	/**
	 * Iterator: key
	 */
	public function key()
	{
		return $this->_current_row;
	}

	/**
	 * Iterator: next
	 */
	public function next()
	{
		++$this->_current_row;
		return $this;
	}

	/**
	 * Iterator: prev
	 */
	public function prev()
	{
		--$this->_current_row;
		return $this;
	}

	/**
	 * Iterator: rewind
	 */
	public function rewind()
	{
		$this->_current_row = 0;
		return $this;
	}

	/**
	 * Iterator: valid
	 */
	public function valid()
	{
		return $this->offsetExists($this->_current_row);
	}

} // End Database_Result