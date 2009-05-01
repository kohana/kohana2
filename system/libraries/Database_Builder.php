<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database builder
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Builder_Core {

	// Valid JOIN types
	protected $_join_types = array('LEFT', 'RIGHT', 'INNER', 'OUTER', 'RIGHT OUTER', 'LEFT OUTER', 'FULL');

	// Valid ORDER BY directions
	protected $order_directions = array('ASC', 'DESC', 'RAND()');

	// Database object
	protected $_db;

	// Builder members
	protected $_select   = array();
	protected $_from     = array();
	protected $_join     = array();
	protected $_where    = array();
	protected $_group_by = array();
	protected $_having   = array();
	protected $_order_by = array();
	protected $_limit    = NULL;
	protected $_offset   = NULL;
	protected $_set      = array();
	protected $_type;

	// TTL for caching (using Cache library)
	protected $_ttl      = FALSE;

	public function __construct($db = 'default')
	{
		$this->_db = $db;
	}

	public function __toString()
	{
		return $this->_compile();
	}

	/**
	 * Compiles the builder object into a SQL query
	 *
	 * @return string  Compiled query
	 */
	protected function _compile()
	{
		if ( ! is_object($this->_db))
		{
			// Use default database for compiling to string if none is given
			$this->_db = Database::instance($this->_db);
		}

		if ($this->_type === Database::SELECT)
		{
			// SELECT columns FROM table
			$sql = 'SELECT '.$this->_compile_select()."\n".'FROM '.$this->_compile_from();
		}
		elseif ($this->_type === Database::UPDATE)
		{
			$vals = array();
			foreach ($this->_set as $key => $val)
			{
				if (is_string($key))
				{
					// Column = Value
					$vals[] = $key.' = '.$val;
				}
				else
				{
					// Database_Expression
					$vals[] = $val;
				}
			}

			$sql = 'UPDATE '.$this->_compile_from()."\n".'SET '.implode(', ', $this->_compile_set(Database::UPDATE));
		}
		elseif ($this->_type === Database::INSERT)
		{
			$vals = $this->_compile_set(Database::INSERT);

			$sql = 'INSERT INTO '.$this->_compile_from()."\n".
				   '('.implode(', ', array_keys($vals)).')'."\n".
				   'VALUES ('.implode(', ', array_values($vals)).')';
		}
		elseif ($this->_type === Database::DELETE)
		{
			$sql = 'DELETE FROM '.$this->_compile_from();
		}

		if ( ! empty($this->_join))
		{
			$sql .= $this->_compile_join();
		}

		if ( ! empty($this->_where))
		{
			$sql .= "\n".'WHERE '.$this->_compile_conditions($this->_where);
		}

		if ( ! empty($this->_having))
		{
			$sql .= "\n".'HAVING '.$this->_compile_conditions($this->_having);
		}

		if ( ! empty($this->_group_by))
		{
			$sql .= "\n".'GROUP BY '.$this->_compile_group_by();
		}

		if ( ! empty($this->_order_by))
		{
			$sql .= "\nORDER BY ".$this->_compile_order_by();
		}

		if (is_int($this->_limit))
		{
			$sql .= "\nLIMIT ".$this->_limit;
		}

		if (is_int($this->_offset))
		{
			$sql .= "\nOFFSET ".$this->_offset;
		}

		return $sql;
	}

	/**
	 * Compiles the SELECT portion of the query
	 *
	 * @return string
	 */
	protected function _compile_select()
	{
		$vals = array();

		foreach ($this->_select as $name => $alias)
		{
			if (is_string($name))
			{
				$vals[] = $this->_db->quote_column(array($name => $alias));
			}
			else
			{
				$vals[] = $this->_db->quote_column($alias);
			}
		}

		return implode(', ', $vals);
	}

	/**
	 * Compiles the FROM portion of the query
	 *
	 * @return string
	 */
	protected function _compile_from()
	{
		$vals = array();

		foreach ($this->_from as $name => $alias)
		{
			if (is_string($name))
			{
				// Using AS format so escape both
				$vals[] = $this->_db->quote_table(array($name => $alias));
			}
			else
			{
				// Just using the table name itself
				$vals[] = $this->_db->quote_table($alias);
			}
		}

		return implode(', ', $vals);
	}

	/**
	 * Compiles the JOIN portion of the query
	 *
	 * @return string
	 */
	protected function _compile_join()
	{
		$sql = '';
		foreach ($this->_join as $join)
		{
			list($table, $keys, $type) = $join;

			if ( ! $table instanceof Database_Expression)
			{
				// Escape the table name (Database_Expressions here are unaltered AND are not parsed)
				$table = $this->_db->quote_table($table);
			}

			if ($type !== NULL)
			{
				// Join type
				$sql .= ' '.$type;
			}

			$sql .= ' JOIN '.$table;

			$condition = '';
			if ($keys instanceof Database_Expression)
			{
				// ON conditions are a Database_Expression, so parse it
				$condition = $keys->parse($this->_db);
			}
			elseif (is_array($keys))
			{
				// ON condition is an array of table column matches
				foreach ($keys as $key => $value)
				{
					if ( ! empty($condition))
					{
						$condition .= ' AND ';
					}

					$condition .= $this->_db->quote_column($key).' = '.$this->_db->quote_column($value);
				}
			}

			if ( ! empty($condition))
			{
				// Add ON condition
				$sql .= ' ON ('.$condition.')';
			}
		}

		return $sql;
	}

	/**
	 * Compiles the GROUP BY portion of the query
	 *
	 * @return string
	 */
	protected function _compile_group_by()
	{
		$vals = array();

		foreach ($this->_group_by as $column)
		{
			// Escape the column
			$vals[] = $this->_db->quote_column($column);
		}

		return implode(', ', $vals);
	}

	/**
	 * Compiles the ORDER BY portion of the query
	 *
	 * @return string
	 */
	public function _compile_order_by()
	{
		$ordering = array();

		foreach ($this->_order_by as $column => $order_by)
		{
			list($column, $direction) = each($order_by);

			$column = $this->_db->quote_column($column);

			if ($direction !== NULL)
			{
				$direction = ' '.$direction;
			}

			$ordering[] = $column.$direction;
		}

		return implode(', ', $ordering);
	}

	/**
	 * Compiles the SET portion of the query (UPDATEs and INSERTs)
	 *
	 * @return string
	 */
	public function _compile_set($type)
	{
		$vals = array();
		foreach ($this->_set as $set)
		{
			if ($set instanceof Database_Expression)
			{
				// Parse any Database_Expressions
				$vals[] = $set->parse($this->_db);
			}
			else
			{
				list($key, $value) = each($set);

				$key = $this->_db->quote_column($key);
				$value = $this->_db->quote($value);

				if ($type === Database::UPDATE)
				{
					$vals[] = $key.' = '.$value;
				}
				else
				{
					$vals[$key] = $value;
				}
			}
		}

		return $vals;
	}

	/**
	 * Join tables to the builder
	 *
	 * @param  mixed   Table name or Database_Expression, or an array of them
	 * @param  mixed   Key, or an array of key => value pair, for join condition (can be a Database_Expression)
	 * @param  mixed   Value if $keys is not an array or Database_Expression
	 * @param  string  Join type (LEFT, RIGHT, INNER, etc.)
	 * @return Database_Builder
	 */
	public function join($table, $keys, $value = NULL, $type = NULL)
	{
		if (is_string($keys))
		{
			$keys = array($keys => $value);
		}

		if ($type !== NULL)
		{
			$type = strtoupper($type);

			if ( ! in_array($type, $this->_join_types))
			{
				// This join type is not supported
				$type = NULL;
			}
		}

		$this->_join[] = array($table, $keys, $type);

		return $this;
	}

	/**
	 * Add tables to the FROM portion of the builder
	 *
	 * @param  mixed  Table name or an array of tables (Key => Val results in 'Key AS Val')
	 * @return Database_Builder
	 */
	public function from($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = func_get_args();
		}

		$this->_from = array_merge($this->_from, $tables);

		return $this;
	}

	/**
	 * Add fields to the GROUP BY portion
	 *
	 * @param  mixed  Field names or an array of fields
	 * @return Database_Builder
	 */
	public function group_by($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->_group_by = array_merge($this->_group_by, $columns);

		return $this;
	}

	/**
	 * Add conditions to the HAVING clause (AND)
	 *
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function having($columns, $op = '=', $value = NULL)
	{
		return $this->and_having($columns, $op, $value);
	}

	/**
	 * Add conditions to the HAVING clause (AND)
	 *
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function and_having($columns, $op = '=', $value = NULL)
	{
		$this->_having[] = array('AND' => array($columns, $op, $value));
		return $this;
	}

	/**
	 * Add conditions to the HAVING clause (OR)
	 *
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function or_having($columns, $op = '=', $value = NULL)
	{
		$this->_having[] = array('OR' => array($columns, $op, $value));
		return $this;
	}

	/**
	 * Add fields to the ORDER BY portion
	 *
	 * @param  mixed   Field names or an array of fields (field => direction)
	 * @param  string  Direction or NULL for ascending
	 * @return Database_Builder
	 */
	public function order_by($columns, $direction = NULL)
	{
		if (is_string($columns))
		{
			$columns = array($columns => $direction);
		}

		$this->_order_by[] = $columns;

		return $this;
	}

	/**
	 * Limit rows returned
	 *
	 * @param  int  Number of rows
	 * @return Database_Builder
	 */
	public function limit($number)
	{
		$this->_limit = (int) $number;

		return $this;
	}

	/**
	 * Offset into result set
	 *
	 * @param  int  Offset
	 * @return Database_Builder
	 */
	public function offset($number)
	{
		$this->_offset = (int) $number;

		return $this;
	}

	public function left_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'LEFT');
	}

	public function right_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'RIGHT');
	}

	public function inner_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'INNER');
	}

	public function outer_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'OUTER');
	}

	public function full_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'FULL');
	}

	public function left_inner_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'LEFT INNER');
	}

	public function right_inner_join($table, $keys, $value = NULL)
	{
		return $this->join($table, $keys, $value, 'RIGHT INNER');
	}

	public function open($clause = 'WHERE')
	{
		return $this->and_open($clause);
	}

	public function and_open($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->_where[] = array('AND' => '(');
		}
		else
		{
			$this->_having[] = array('AND' => '(');
		}

		return $this;
	}

	public function or_open($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->_where[] = array('OR' => '(');
		}
		else
		{
			$this->_having[] = array('OR' => '(');
		}

		return $this;
	}

	public function close($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->_where[] = array(')');
		}
		else
		{
			$this->_having[] = array(')');
		}

		return $this;
	}

	/**
	 * Add conditions to the WHERE clause (AND)
	 *
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function where($columns, $op = '=', $value = NULL)
	{
		return $this->and_where($columns, $op, $value);
	}

	/**
	 * Add conditions to the WHERE clause (AND)
	 *
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function and_where($columns, $op = '=', $value = NULL)
	{
		$this->_where[] = array('AND' => array($columns, $op, $value));
		return $this;
	}

	/**
	 * Add conditions to the WHERE clause (OR)
	 *
	 * @param  mixed   Column name or array of columns => vals
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function or_where($columns, $op = '=', $value = NULL)
	{
		$this->_where[] = array('OR' => array($columns, $op, $value));
		return $this;
	}

	/**
	 * Compiles the given clause's conditions
	 *
	 * @param  array  Clause conditions
	 * @return string
	 */
	protected function _compile_conditions($groups)
	{
		$last_condition = NULL;

		$sql = '';
		foreach ($groups as $group)
		{
			// Process groups of conditions
			foreach ($group as $logic => $condition)
			{
				if ($condition === '(')
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Include logic operator
						$sql .= ' '.$logic.' ';
					}

					$sql .= '(';
				}
				elseif ($condition === ')')
				{
					$sql .= ')';
				}
				else
				{
					list($columns, $op, $value) = $condition;

					// Stores each individual condition
					$vals = array();

					if ($columns instanceof Database_Expression)
					{
						// Parse Database_Expression and add to condition list
						$vals[] = $columns->parse($this->_db);
					}
					else
					{
						$op = strtoupper($op);

						if ( ! is_array($columns))
						{
							$columns = array($columns => $value);
						}

						foreach ($columns as $column => $value)
						{
							if ($value instanceof Database_Expression)
							{
								// Parse Database_Expression for right side of operator
								$value = $value->parse($this->_db);
							}
							elseif (is_array($value))
							{
								if ($op === 'BETWEEN' OR $op === 'NOT BETWEEN')
								{
									// Falls between two values
									$value = $this->_db->quote($value[0]).' AND '.$this->_db->quote($value[1]);
								}
								else
								{
									// Return as list
									$value = array_map(array($this->_db, 'escape'), $value);
									$value = '('.implode(', ', $value).')';
								}
							}
							else
							{
								$value = $this->_db->quote($value);
							}

							// Add to condition list
							$vals[] = $this->_db->quote_column($column).' '.$op.' '.$value;
						}
					}

					if ( ! empty($sql) AND $last_condition !== '(')
					{
						// Add the logic operator
						$sql .= ' '.$logic.' ';
					}

					// Join the condition list items together by the given logic operator
					$sql .= implode(' '.$logic.' ', $vals);
				}

				$last_condition = $condition;
			}
		}

		return $sql;
	}

	/**
	 * Set values for UPDATE or INSERT
	 *
	 * @param  mixed   Column name or array of columns => vals, or a Database_Expression
	 * @param  string  Operation to perform
	 * @param  mixed   Value
	 * @return Database_Builder
	 */
	public function set($keys, $value = NULL)
	{
		if (is_string($keys))
		{
			$keys = array($keys => $value);
		}

		$this->_set[] = $keys;

		return $this;
	}

	/**
	 * Create a SELECT query and specify selected columns
	 *
	 * @param  mixed   Column name or array of columns (can be in form Column => Alias)
	 * @return Database_Builder
	 */
	public function select($columns = NULL)
	{
		$this->_type = Database::SELECT;

		if ($columns === NULL)
		{
			$columns = array('*');
		}
		elseif ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->_select = array_merge($this->_select, $columns);

		return $this;
	}

	/**
	 * Create an UPDATE query
	 *
	 * @param  string  Table name
	 * @param  array   Array of Keys => Values
	 * @param  array   WHERE conditions
	 * @return Database_Builder
	 */
	public function update($table = NULL, $set = NULL, $where = NULL)
	{
		$this->_type = Database::UPDATE;

		if (is_array($set))
		{
			$this->set($set);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		if ($table !== NULL)
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Create an INSERT query
	 *
	 * @param  string  Table name
	 * @param  array   Array of Keys => Values
	 * @return Database_Builder
	 */
	public function insert($table = NULL, $set = NULL)
	{
		$this->_type = Database::INSERT;

		if (is_array($set))
		{
			$this->set($set);
		}

		if ($table !== NULL)
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Create a DELETE query
	 *
	 * @param  string  Table name
	 * @param  array   WHERE conditions
	 * @return Database_Builder
	 */
	public function delete($table, $where = NULL)
	{
		$this->_type = Database::DELETE;

		if ($where !== NULL)
		{
			$this->where($where);
		}

		if ($table !== NULL)
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Count records for a given table
	 *
	 * @param  string  Table name
	 * @param  array   WHERE conditions
	 * @return int
	 */
	public function count_records($table = FALSE, $where = NULL)
	{
		if (count($this->_from) < 1)
		{
			if ($table === FALSE)
				throw new Database_Exception('Database count_records requires a table');

			$this->from($table);
		}

		if ($where !== NULL)
		{
			$this->where($where);
		}

		// Grab the count AS records_found
		$result = $this->select(array('COUNT("*")' => 'records_found'))->execute();

		return $result->current()->records_found;
	}

	/**
	 * Executes the built query
	 *
	 * @param  mixed  Database name or object
	 * @return Database_Result
	 */
	public function execute($db = NULL)
	{
		if ($db !== NULL)
		{
			$this->_db = $db;
		}

		if ( ! is_object($this->_db))
		{
			// Get the database instance
			$this->_db = Database::instance($this->_db);
		}

		if ($this->_ttl !== FALSE AND $this->_type === Database::SELECT)
		{
			// Return result from cache (only allowed with SELECT)
			return $this->_db->query_cache((string) $this, $this->_ttl);
		}
		else
		{
			// Load the result (no caching)
			return $this->_db->query((string) $this);
		}
	}

	/**
	 * Set caching for the query
	 *
	 * @param  mixed  Time-to-live (FALSE to disable, NULL for Cache default, seconds otherwise)
	 * @return Database_Builder
	 */
	public function cache($ttl = NULL)
	{
		$this->_ttl = $ttl;

		return $this;
	}

} // End Database_Builder
