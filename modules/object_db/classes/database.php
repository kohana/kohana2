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

		return Database::build('select', $this)->select($columns);
	}

	public function insert($table, $columns = NULL, $values = NULL)
	{
		return new Database_Insert($this, $table, $columns, $values);
	}

	public function update($table, $columns = NULL)
	{
		return new Database_Update($this, $table, $columns);
	}

	public function delete($table)
	{
		return new Database_Delete($this, $table);
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
			case 'string':
				$value = parent::quote($value);
			break;
			case 'NULL':
				$value = 'NULL';
			break;
		}

		return $value;
	}

} // End Database
