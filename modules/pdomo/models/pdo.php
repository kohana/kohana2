<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana PDO Model.
 *
 * $Id$
 *
 * @package    pdomo
 * @author     Woody Gilk
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class PDO_Model {

	// PDO database object
	protected $db;

	// Table name
	protected $table;

	// Use auto-incrementing
	protected $auto_increment = TRUE;

	// Primary key
	protected $primary_key = 'id';

	// Object data status
	protected $loaded = FALSE;
	protected $saved = FALSE;

	// Result data
	protected $data = array();
	protected $changed = array();

	// Field types
	protected $types = array();

	/**
	 * Constructor.
	 *
	 * @param   object  PDO datbase
	 * @return  void
	 */
	public function __construct($db = NULL)
	{
		if ($this->table === NULL)
			throw new Kohana_Exception('pdo.invalid_table', get_class($this));

		if ($this->primary_key === NULL)
			throw new Kohana_Exception('pdo.invalid_primary_key', get_class($this));

		if (empty($this->types))
			throw new Kohana_Exception('pdo.invalid_types', get_class($this));

		// Get a database instance
		($db === NULL) and $db = pdomo::instance();

		// Makes sure the database instance is valid
		if ( ! ($db instanceof PDO))
			throw new Kohana_Exception('pdo.invalid_database', get_class($this));

		// Set the database instance
		$this->db = $db;

		// Empty the data
		$this->__empty_data();

		// Call the on_construct event
		$this->__on_construct();
	}

	/**
	 * Magic __get method.
	 *
	 * @param   string  key name
	 * @return  mixed
	 */
	public function __get($key)
	{
		if ( ! isset($this->data[$key]))
			throw new Kohana_Exception('pdo.invalid_get', $key, get_class($this));

		// Return the key value
		return $this->data[$key];
	}

	/**
	 * Magic __set method.
	 *
	 * @param   string  key name
	 * @param   mixed   value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		if (empty($this->data[$key]) OR $this->data[$key] !== $value)
		{
			// Change the data value
			$this->data[$key] = $value;

			if (isset($this->types[$key]))
			{
				// Data has been changed
				$this->changed[$key] = $key;
			}

			// Object is not saved
			$this->saved = FALSE;
		}
	}

	/**
	 * Unloads the current object by clearing the data.
	 *
	 * @return  void
	 */
	protected function __empty_data()
	{
		foreach ($this->types as $key => $val)
		{
			// Create the initial, empty data
			$this->data[$key] = NULL;
		}

		// Data is no longer loaded
		$this->loaded = $this->saved = FALSE;

		// Execute the post-construct local event
		$this->__set_types();
	}

	/**
	 * Sets correct types on loaded data.
	 *
	 * @return  void
	 */
	protected function __set_types()
	{
		foreach ($this->types as $key => $type)
		{
			// Make sure the value type is correct
			settype($this->data[$key], $type);
		}
	}

	/**
	 * Quotes the value of a column. Integers are type casted, arrays are
	 * made into a string of comma-separated values, strings are quoted
	 * using the PDO quote() method, and NULL is made into a string value.
	 *
	 * @param   mixed   value to quote
	 * @return  mixed
	 */
	protected function __quote_value($value)
	{
		if ($value === NULL)
		{
			// Make the value into a string for SQL
			return 'NULL';
		}
		elseif (is_array($value))
		{
			$array = array();
			foreach ($value as $val)
			{
				// Quote the value
				$array[] = $this->__quote_value($val);
			}
			return '('.implode(', ', $array).')';
		}
		elseif (is_int($value) OR ctype_digit($value))
		{
			// No quoting for integers
			return (int) $value;
		}
		else
		{
			// Quote the value
			return $this->db->quote($value);
		}
	}

	/**
	 * Load data from an SQL query. This will always unload the current object
	 * before executing the query. You can test the success of this method by
	 * checking the return value of $this->loaded().
	 *
	 * @param   object  PDOStatement to be executed
	 * @param   array   optional array of arguments to be passed to execute()
	 * @return  void
	 */
	protected function __query(PDOStatement $query, $params = NULL)
	{
		// Empty the data
		$this->__empty_data();

		// Execute the query
		$status = empty($params) ? $query->execute() : $query->execute($params);

		if ($status > 0 AND $query->rowCount() > 0)
		{
			// Load the data of the object
			$this->data = $query->fetch(PDO::FETCH_ASSOC);

			// No data has been changed
			$this->changed = array();

			// Data has been loaded and is saved
			$this->loaded = $this->saved = TRUE;

			// Reset the types of loaded data
			$this->__set_types();
		}
	}

	/**
	 * Called as the last step of __construct(), before a return.
	 *
	 * @return  void
	 */
	protected function __on_construct()
	{
		// No default action
	}

	/**
	 * Called as the last step of find(), before a return.
	 *
	 * @return  void
	 */
	protected function __on_find()
	{
		// No default action
	}

	/**
	 * Called as the last step of save(), before a return.
	 *
	 * @return  void
	 */
	protected function __on_save()
	{
		// No default action
	}

	/**
	 * Called as the last step of delete(), before a return.
	 *
	 * @return  void
	 */
	protected function __on_delete()
	{
		// No default action
	}

	/**
	 * Validation method, called by save() to determine if the model can be saved.
	 * If validation is successful, TRUE should be returned. If validation fails,
	 * an array(field => error) should be returned.
	 *
	 * @return  boolean  (success) TRUE
	 * @return  array    (failure) array of errors
	 */
	protected function __validate()
	{
		return TRUE;
	}

	/**
	 * Tests if the object is loaded.
	 *
	 * @return  boolean
	 */
	public function loaded()
	{
		return $this->loaded;
	}

	/**
	 * Tests if the object is saved.
	 *
	 * @return  boolean
	 */
	public function saved()
	{
		return $this->saved;
	}

	/**
	 * Load data from an external array into the object.
	 *
	 * @chainable
	 * @param   array   key/value array
	 * @return  object
	 */
	public function load(array $data)
	{
		foreach ($data as $key => $val)
		{
			// Set each value separately
			$this->__set($key, $val);
		}
	}

	/**
	 * Finds a single object matching the criteria. You may call this method
	 * with one, two, or three parameters. Called with one parameter, the param
	 * is used as a primary key value. Called with two parameters, the params
	 * are used for the column and value to find. Called with three parameters,
	 * the column, operation, and value will be used.
	 *
	 * @chainable
	 * @param   string  column to search
	 * @param   string  comparison operation (=, <, >, LIKE, REGEX, NOT, etc)
	 * @param   mixed   column value (arrays will be collapsed for IN)
	 * @return  object
	 */
	public function find($key, $op = '=', $value = NULL)
	{
		if (($num_args = func_num_args()) < 3)
		{
			if ($num_args === 2)
			{
				// Use the operator as the value
				$value = $op;
			}
			else
			{
				// Use the key as the value
				$value = $key;

				// Use the primary key
				$key = $this->primary_key;
			}

			// Use equals for the operator
			$op = '=';
		}
		else
		{
			// Make the operation a string
			$op = (string) $op;
		}

		// Table name is always a string
		$key = (string) $key;

		if ( ! preg_match('/^=|[!<>]=?|(?:NOT\s+)?(LIKE|REGEXP?|IN)$/i', $op))
			throw new Kohana_Exception('pdo.invalid_operation', $op, get_class($this));

		// Quote the value
		$value = $this->__quote_value($value);

		// Find a single row matching the criteria
		$this->__query($this->db->prepare('SELECT '.$this->table.'.* FROM '.$this->table.' WHERE '.$key.' '.$op.' '.$value.' LIMIT 1 OFFSET 0'));

		// Execute the on_find event
		$this->__on_find();

		return $this;
	}

	/**
	 * Saves the current object back into the database.
	 *
	 * @return  boolean
	 */
	public function save()
	{
		if ($this->saved === TRUE)
			return TRUE;

		if (is_array($errors = $this->__validate()))
			return $errors;

		if ($this->loaded === TRUE)
		{
			// Perform an UPDATE
			$insert = FALSE;

			// Create the SQL
			$sql = 'UPDATE '.$this->table.' SET';

			foreach ($this->changed as $key)
			{
				// Add the new data
				$sql .= ' '.$key.' = '.$this->__quote_value($this->data[$key]);
			}

			// Add the WHERE
			$sql .= ' WHERE '.$this->primary_key.' = '.$this->data[$this->primary_key];
		}
		else
		{
			// Perform an INSERT
			$insert = TRUE;

			$data = array();
			foreach ($this->changed as $key)
			{
				// Load the changed data
				$data[$key] = $this->__quote_value($this->data[$key]);
			}

			if ($this->auto_increment === TRUE)
			{
				// Remove the primary key from the insert
				unset($data[$this->primary_key]);
			}

			// Create the SQL statement
			$sql = 'INSERT INTO '.$this->table.' ('.implode(', ', array_keys($data)).') VALUES ('.implode(', ', $data).')';
		}

		if ($count = $this->db->exec($sql))
		{
			if ($insert === TRUE AND $this->auto_increment === TRUE)
			{
				// Get and assign the insert ID
				$this->data[$this->primary_key] = $this->db->lastInsertId();
			}

			// No data has been changed
			$this->changed = array();

			// Object is loaded and saved
			$this->saved = $this->loaded = TRUE;

			// Execute the on_save event
			$this->__on_save();

			// Success!
			return TRUE;
		}

		// Failure!
		return FALSE;
	}

	/**
	 * Deletes the current object from the database.
	 *
	 * @return  boolean  (failure) FALSE
	 * @return  integer  (success) number of rows deleted
	 */
	public function delete()
	{
		// Nothing is deleted by default
		$status = FALSE;

		if ( ! empty($this->data[$this->primary_key]))
		{
			// Primary key
			$pk = $this->primary_key;

			// SQL to delete this object
			$sql = 'DELETE FROM '.$this->table.' WHERE '.$this->primary_key.' = '.$this->__quote_value($this->$pk);

			if ($count = $this->db->exec($sql))
			{
				// Clear the object
				$this->__empty_data();

				// Return the count as the status
				$status = $count;
			}

			// Call the on_delete event
			$this->__on_delete();
		}

		// Return the status
		return $status;
	}

} // End PDO_Model