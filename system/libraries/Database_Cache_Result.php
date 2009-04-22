<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cached database result.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Cache_Result_Core extends Database_Result {

	/**
	 * Result data (array of rows)
	 * @var array
	 */
	protected $_data;

	public function __construct($data, $sql, $return_objects)
	{
		$this->_data           = $data;
		$this->_sql            = $sql;
		$this->_total_rows     = count($data);
		$this->_return_objects = $return_objects;
	}

	public function __destruct()
	{
		// Not used
	}

	public function __get($name)
	{
		// Fetch the given field from the current row
		return $this->_data[$name];
	}

	public function as_array($return = FALSE)
	{
		// Return arrays rather than objects
		$this->_return_objects = FALSE;

		if ( ! $return )
		{
			// Return this result object
			return $this;
		}

		// Return the entire array of rows
		return $this->_data;
	}

	public function as_object($class = NULL)
	{
		if ($class !== NULL)
			throw new Database_Exception('Database cache results do not support object casting');

		// Return objects of type $class (or stdClass if none given)
		$this->_return_objects = TRUE;

		return $this;
	}

	public function seek($offset)
	{
		if ( ! $this->offsetExists($offset))
			return FALSE;

		$this->_current_row = $offset;

		return TRUE;
	}

	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return FALSE;

		if ($this->_return_objects)
		{
			// Return a new object with the current row of data
			return new Database_Cache_Result($this->_data[$this->_current_row], $sql, $this->_return_objects);
		}
		else
		{
			// Return an array of the row
			return $this->_data[$this->_current_row];
		}
	}

} // End Database_Cache_Result