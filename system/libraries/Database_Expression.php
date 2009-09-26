<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database expression.
 * 
 * $Id$
 * 
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Expression_Core {

	protected $expression;
	protected $db;
	protected $params;
	protected $as;

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
			$db = Database::instance($db);
		}

		$this->db = $db;

		$expression = $this->expression;

		// Quote columns in the expression
		$expression = $this->db->quote_column($expression);

		// Substitute any values
		if ( ! empty($this->params))
		{
			// Quote all of the values
			$params = array_map(array($this->db, 'quote'), $this->params);

			// Replace the values in the SQL
			$expression = strtr($this->expression, $params);
		}

		return $expression;
	}

	public function value($key, $value)
	{
		$this->params[$key] = $value;

		return $this;
	}

	public function bind($key, & $value)
	{
		$this->params[$key] =& $value;

		return $this;
	}
}
