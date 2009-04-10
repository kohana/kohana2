<?php

class Database_Expression_Core {

	protected $expression;
	protected $db;
	protected $_params;

	public function __construct($expression)
	{
		$this->expression = $expression;
	}

	public function __toString()
	{
		return $this->expression;
	}

	public function parse($db = 'default')
	{
		if ( ! is_object($db))
		{
			// Get the database instance
			$this->db = Database::instance($db);
		}

		$this->db = $db;

		if ( ! empty($this->_params))
		{
			// Quote all of the values
			$params = array_map(array($this->db, 'quote'), $this->_params);

			// Replace the values in the SQL
			$this->expression = strtr($this->expression, $params);
		}

		// Escape table names
		$this->expression = preg_replace_callback('/{(.*?)}/', array($this, 'escape_table_callback'), $this->expression);

		return $this->expression;
	}

	protected function escape_table_callback($matches)
	{
		return $this->db->escape_table($matches[1]);
	}

	public function set($key, $value)
	{
		$this->_params[$key] = $value;

		return $this;
	}

	public function bind($key, & $value)
	{
		$this->_params[$key] =& $value;

		return $this;
	}
}