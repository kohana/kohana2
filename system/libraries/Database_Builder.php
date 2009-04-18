<?php

class Database_Builder_Core {

	protected $join_types = array('LEFT', 'RIGHT', 'INNER', 'OUTER', 'RIGHT OUTER', 'LEFT OUTER', 'FULL');

	protected $db;

	protected $select   = array();
	protected $from     = array();
	protected $join     = array();
	protected $where    = array();
	protected $group_by = array();
	protected $having   = array();
	protected $order_by = array();
	protected $limit    = NULL;
	protected $offset   = NULL;

	protected $set      = array();

	protected $type;

	// The current section of clause we are in (HAVING, WHERE)
	protected $in_clause = 'WHERE';

	protected $order_directions = array('ASC', 'DESC', 'RAND()');

	public function __construct($db = 'default')
	{
		if ( ! is_object($db))
		{
			// Get the database instance
			$db = Database::instance($db);
		}

		$this->db = $db;
	}

	public function select($columns = NULL)
	{
		$this->type = Database::SELECT;

		if ($columns === NULL)
		{
			$columns = array('*');
		}
		elseif ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->select = array_merge($this->select, $columns);

		return $this;
	}

	public function __toString()
	{
		return $this->compile();
	}

	public function compile()
	{
		if ($this->type === Database::SELECT)
		{
			// SELECT columns FROM table
			$sql = 'SELECT '.$this->compile_select()."\n".'FROM '.$this->compile_from();
		}
		elseif ($this->type === Database::UPDATE)
		{
			$vals = array();
			foreach ($this->set as $key => $val)
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

			$sql = 'UPDATE '.$this->compile_from()."\n".'SET ('.implode(', ', $this->flatten($vals)).')';
		}
		elseif ($this->type === Database::INSERT)
		{
			$sql = 'INSERT INTO '.$this->from[0]."\n".
				   '('.implode(', ', $this->flatten(array_keys($this->set))).')'."\n".
				   'VALUES ('.implode(', ', $this->flatten(array_values($this->set))).')';
		}

		if ( ! empty($this->join))
		{
			$sql .= $this->compile_join();
		}

		if ( ! empty($this->values))
		{
			$sql .= "\n".'SET ('.implode(', ', $this->flatten($this->values)).')';
		}

		if ( ! empty($this->where))
		{
			$sql .= "\n".'WHERE '.$this->compile_conditions($this->where);
		}

		if ( ! empty($this->having))
		{
			$sql .= "\n".'HAVING '.$this->compile_conditions($this->having);
		}

		if ( ! empty($this->group_by))
		{
			$sql .= "\n".'GROUP BY '.$this->compile_group_by();
		}

		if ( ! empty($this->order_by))
		{
			$ordering = array();
			foreach ($this->order_by as $column => $direction)
			{
				if ($direction !== NULL)
				{
					$direction = ' '.$direction;
				}

				$ordering[] = $column.$direction;
			}

			// ORDER BY column DIRECTION
			$sql .= "\nORDER BY ".implode(', ', $ordering);
		}

		if (is_int($this->limit))
		{
			$sql .= "\nLIMIT ".$this->limit;
		}

		if (is_int($this->offset))
		{
			$sql .= "\nOFFSET ".$this->offset;
		}

		return $sql;
	}

	protected function compile_select()
	{
		foreach ($this->select as $name => $alias)
		{
			if ($alias instanceof Database_Expression)
			{
				// Parse Database_Expression
				$alias->parse($this->db);
			}
			else
			{
				if (is_string($name))
				{
					// Using AS format so escape both
					$alias = $this->db->escape_table(array($name => $alias));
				}
				else
				{
					// Just using the table name itself
					$alias = $this->db->escape_table($alias);
				}

				// Unquote all asterisks
				$alias = preg_replace('/`[^\.]*\*`/', '*', $alias);
			}

			$vals[] = $alias;
		}

		return implode(', ', $vals);
	}

	protected function compile_from()
	{
		foreach ($this->from as $name => $alias)
		{
			if (is_string($name))
			{
				// Using AS format so escape both
				$alias = $this->db->escape_table(array($name => $alias));
			}
			else
			{
				// Just using the table name itself
				$alias = $this->db->escape_table($alias);
			}

			$vals[] = $alias;
		}

		return implode(', ', $vals);
	}

	protected function compile_join()
	{
		$sql = '';
		foreach ($this->join as $join)
		{
			list($table, $keys, $type) = $join;

			$table = $this->db->escape_table($table);

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
				$condition = $keys->parse($this->db);
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

					$condition .= $this->db->escape_table($key).' = '.$this->db->escape_table($value);
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

	public function compile_group_by()
	{
		foreach ($this->group_by as $column)
		{
			if ($column instanceof Database_Expression)
			{
				$column = $column->parse($this->db);
			}
			else
			{
				$column = $this->db->escape_table($column);
			}

			$vals[] = $column;
		}

		return implode(', ', $vals);
	}

	public function join($table, $keys, $value = NULL, $type = NULL)
	{
		if (is_string($keys))
		{
			$keys = array($keys => $value);
		}

		if ($type !== NULL)
		{
			$type = strtoupper($type);

			if ( ! in_array($type, $this->join_types))
			{
				// This join type is not supported
				$type = NULL;
			}
		}

		$this->join[] = array($table, $keys, $type);

		return $this;
	}

	public function from($tables)
	{
		if ( ! is_array($tables))
		{
			$tables = func_get_args();
		}

		$this->from = array_merge($this->from, $tables);

		return $this;
	}

	public function group_by($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		$this->group_by = array_merge($this->group_by, $columns);

		return $this;
	}

	public function having($columns, $op = '=', $value = NULL)
	{
		return $this->and_having($columns, $op, $value);
	}

	public function and_having($columns, $op = '=', $value = NULL)
	{
		$this->having[] = array('AND' => array($columns, $op, $value));
		return $this;
	}

	public function or_having($columns, $op = '=', $value = NULL)
	{
		$this->having[] = array('OR' => array($columns, $op, $value));
		return $this;
	}

	public function order_by($column, $direction = NULL)
	{
		if ($direction !== NULL)
		{
			$direction = strtoupper($direction);

			if ( ! in_array($direction, $this->order_directions))
			{
				// Direction is invalid
				$direction = NULL;
			}
		}

		if ($column instanceof Database_Expression)
		{
			$column = $column->parse($this->db);
		}

		$this->order_by[$column] = $direction;

		return $this;
	}

	public function limit($number)
	{
		$this->limit = (int) $number;

		return $this;
	}

	public function offset($number)
	{
		$this->offset = (int) $number;

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
			$this->where[] = array('AND' => '(');
		}
		else
		{
			$this->having[] = array('AND' => '(');
		}

		return $this;
	}

	public function or_open($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->where[] = array('OR' => '(');
		}
		else
		{
			$this->having[] = array('OR' => '(');
		}

		return $this;
	}

	public function close($clause = 'WHERE')
	{
		if ($clause === 'WHERE')
		{
			$this->where[] = array(')');
		}
		else
		{
			$this->having[] = array(')');
		}

		return $this;
	}

	public function where($columns, $op = '=', $value = NULL)
	{
		return $this->and_where($columns, $op, $value);
	}

	public function and_where($columns, $op = '=', $value = NULL)
	{
		$this->where[] = array('AND' => array($columns, $op, $value));
		return $this;
	}

	public function or_where($columns, $op = '=', $value = NULL)
	{
		$this->where[] = array('OR' => array($columns, $op, $value));
		return $this;
	}

	protected function flatten(array $values)
	{
		foreach ($values as & $val)
		{
			$val = (string) $val;
		}

		return $values;
	}

	protected function compile_conditions($groups)
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
						$vals[] = $columns->parse($this->db);
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
							if (is_array($value))
							{
								if ($op === 'BETWEEN')
								{
									// Falls between two values
									$value = $this->db->quote($value[0]).' AND '.$this->db->quote($value[1]);
								}
								else
								{
									// Return as list
									$value = array_map(array($this->db, 'escape'), $value);
									$value = '('.implode(', ', $value).')';
								}
							}
							else
							{
								$value = $this->db->quote($value);
							}

							// Add to condition list
							$vals[] = $this->db->escape_table($column).' '.$op.' '.$value;
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

	public function set($keys, $value = NULL)
	{
		if ($keys instanceof Database_Expression)
		{
			$this->set[] = $keys->parse($this->db);
			return $this;
		}

		if (is_string($keys))
		{
			$keys = array($keys => $value);
		}

		foreach ($keys as $key => $value)
		{
			$key = $this->db->escape_table($key);

			$this->set[$key] = $this->db->quote($value);
		}

		return $this;
	}

	public function update($table = NULL, $set = NULL, $where = NULL)
	{
		$this->type = Database::UPDATE;

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

	public function insert($table = NULL, $set = NULL)
	{
		$this->type = Database::INSERT;

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

	public function execute()
	{
		return $this->db->query($this->type, (string) $this);
	}

} // End Database_Builder
