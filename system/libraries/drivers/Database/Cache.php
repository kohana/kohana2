<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Database cached result driver, uses Cache library for cross-request caching
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Result extends Database_Result
{
	/**
	 * Array of result data
	 * @var array
	 */
	protected $data;

	/**
	 * Constructs a new cache result object
	 *
	 * @param string $data  Data result array
	 * @return void
	 */
	public function __construct($data = NULL)
	{
		$this->data        = $data;
		$this->current_row = 0;
		$this->total_rows  = count($data);
		$this->fetch_type  = array($this, 'offsetGet_object');
	}

	public function result($object = TRUE, $type = FALSE)
	{
		if ($object)
		{
			// Iterated rows should be returned as objects
			$this->fetch_type = array($this, 'offsetGet_object');
		}
		else
		{
			// Iterated rows returned as arrays
			$this->fetch_type = array($this, 'offsetGet_array');
		}

		return $this;
	}

	public function result_array($object = NULL, $type = FALSE)
	{
		if ($object)
		{
			// Create an array of result objects

			$result = array();
			foreach ($this->data as $row)
			{
				$row = new Cache_Result($row);
				$result[] = $row;
			}

			return $result;
		}
		else
		{
			// Return the complete result array
			return $this->data;
		}
	}

	public function list_fields()
	{
		if ( ! empty($this->data))
		{
			// Return the keys from the first result in the array
			return array_keys($this->data[0]);
		}
	}

	public function seek($offset)
	{
		if ( ! $this->offsetExists($offset))
			return FALSE;

		$this->current_row = $offset;

		return TRUE;
	}

	public function offsetGet_object($result, $return_type)
	{
		// Return a new result object with the given row
		return (object) $this->data[$this->current_row];
	}

	public function offsetGet_array($result, $return_type)
	{
		// Return the given row as an array
		return $this->data[$this->current_row];
	}
}