<?php

class Database_Core extends PDO {

	public static $benchmarks = array();

	protected static $instances = array();

	public static function instance($name = 'default')
	{
		return Database::$instances[$name] = new Database(Kohana::config('database.'.$name));
	}

	protected static function build($class, Database $db)
	{
		$class = 'Database_'.ucfirst($class);

		return new $class($db);
	}

	public function __construct(array $config)
	{
		if ( ! isset($config['type']) OR ! isset($config['dsn']))
			throw new Kohana_Exception('invalid_config');

		// Set all errors to throw exceptions
		$attr[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

		// Override default statement class
		$attr[PDO::ATTR_STATEMENT_CLASS] = array('Database_Statement', array($this));

		if (isset($config['persistent']) AND $config['persistent'] === TRUE)
		{
			// Enable persistent connections
			$attr[PDO::ATTR_PERSISTENT] = TRUE;
		}

		parent::__construct($config['type'].':'.$config['dsn'], $config['user'], $config['pass'], $attr);

		// Set the connection character set
		$this->exec('SET NAMES '.$this->escape($config['charset']));
	}

	public function select($columns = NULL)
	{
		if ($columns === NULL)
		{
			$columns = array('*');
		}
		elseif ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		// Create a SELECT query
		$query = new Database_Select($this);

		return $query->select($columns);
	}

	public function insert($table, $columns = NULL)
	{
		// Create an INSERT query
		$query = new Database_Insert($this);

		// Set the table name
		$query->table($table);

		if (is_array($columns))
		{
			// Set the column names
			$query->columns($columns);
		}

		return $query;
	}

	public function update($table)
	{
		// Create a new UPDATE statement
		$query =  new Database_Update($this);

		// Set the table name
		$query->table($table);

		return $query;
	}

	public function delete($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = func_get_args();
		}

		// Create a DELETE query
		$query = new Database_Delete($this);

		// Set the 
		$query->from($tables);

		return $query;
	}

	public function expression($exp)
	{
		return new Database_Expression($exp);
	}

	public function escape($value)
	{
		switch (gettype($value))
		{
			case 'array':
				// Recursively escape all values in the array
				$value = implode(', ', array_map(array($this, 'escape'), $value));
			break;
			case 'object':
				$value = (string) $value;
			break;
			case 'NULL':
				$value = 'NULL';
			break;
			case 'string':
				$value = parent::quote($value, PDO::PARAM_STR);
			break;
			case 'bool':
				$value = parent::quote($value, PDO::PARAM_BOOL);
			break;
		}

		return $value;
	}

} // End Database
