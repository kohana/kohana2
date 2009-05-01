<?php

class Database_Expression_Core {

	protected $_expression;
	protected $_db;
	protected $_params;
	protected $_as;

	public function __construct($expression)
	{
		$this->_expression = $expression;
	}

	public function __toString()
	{
		return $this->_expression;
	}

	public function parse($db = 'default')
	{
		if ( ! is_object($db))
		{
			// Get the database instance
			$db = Database::instance($db);
		}

		$this->_db = $db;

		$expression = $this->_expression;

		// Quote columns in the expression
		$expression = $this->_db->quote_column($expression);

		// Substitute any values
		if ( ! empty($this->_params))
		{
			// Quote all of the values
			$params = array_map(array($this->_db, 'quote'), $this->_params);

			// Replace the values in the SQL
			$expression = strtr($this->_expression, $params);
		}

		return $expression;
	}

	public function value($key, $value)
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