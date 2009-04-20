<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database result.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_MySQL_Result_Core extends Database_Result {

	public function __construct($result, $sql, $link, $return_objects)
	{
		if (is_resource($result))
		{
			// True to return objects, false for arrays
			$this->_return_objects = $return_objects;

			$this->_total_rows = mysql_num_rows($result);
		}
		elseif (is_bool($result))
		{
			if ($result == FALSE)
			{
				throw new Database_Exception('There was an SQL error: :error', array(':error' => mysql_error($link).' - '.$sql));
			}
			else
			{
				// It's an DELETE, INSERT, REPLACE, or UPDATE query
				$this->_insert_id = mysql_insert_id($link);
				$this->_total_rows = mysql_affected_rows($link);
			}
		}

		// Store the result locally
		$this->_result = $result;

		$this->_sql = $sql;
	}

	public function __destruct()
	{
		if (is_resource($this->_result))
		{
			mysql_free_result($this->_result);
		}
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

		// Return a nested array of all results
		$array = array();

		if ($this->_total_rows > 0)
		{
			// Seek to the beginning of the result
			mysql_data_seek($this->_result, 0);

			while ($row = mysql_fetch_assoc($this->_result))
			{
				// Add each row to the array
				$array[] = $row;
			}
		}

		return $array;
	}

	public function as_object($class = NULL)
	{
		// Return objects of type $class (or stdClass if none given)
		$this->_return_objects = ($class !== NULL) ? $class : TRUE;

		return $this;
	}

	public function seek($offset)
	{
		if ($this->offsetExists($offset) AND mysql_data_seek($this->_result, $offset))
		{
			// Set the current row to the offset
			$this->_current_row = $offset;

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return FALSE;

		if ($this->_return_objects)
		{
			if (is_string($this->_return_objects))
			{
				return mysql_fetch_object($this->_result, $this->_return_objects);
			}
			else
			{
				return mysql_fetch_object($this->_result);
			}
		}
		else
		{
			// Return an array of the row
			return mysql_fetch_assoc($this->_result);
		}
	}

} // End Database_MySQL_Result_Select