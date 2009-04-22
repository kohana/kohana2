<?php

class Database_Expression_Core {

	protected $_expression;
	protected $_db;
	protected $_params;
	protected $_as;

	public function __construct($expression)
	{
		if (is_array($expression))
		{
			// If key => val form is used, only do escaping/parsing on the key (it becomes 'key AS val')
			$this->_expression = key($expression);
			$this->_as = current($expression);
		}
		else
		{
			// Do parsing on the entire expression
			$this->_expression = $expression;
		}
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
			$this->_db = Database::instance($db);
		}

		$this->_db = $db;

		$expression = $this->_expression;

		if ( ! empty($this->_params))
		{
			// Quote all of the values
			$params = array_map(array($this->_db, 'quote'), $this->_params);

			// Replace the values in the SQL
			$expression = strtr($this->_expression, $params);
		}

		// Escape table names in the expression
		$expression = preg_replace_callback('/`(.*?)`/', array($this, '_escape_table_callback'), $expression);

		if (isset($this->_as))
		{
			// Using an AS, don't do any escaping/parsing on that portion
			$expression .= ' AS `'.$this->_as.'`';
		}

		return $expression;
	}

	protected function _escape_table_callback($matches)
	{
		return $this->_db->escape_table($matches[1]);
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