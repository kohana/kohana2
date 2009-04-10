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
		$this->last_where =& $this->where;
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

		foreach ($columns as $name => &$alias)
		{
			if ( ! $alias instanceof Database_Expression)
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
			else
			{
				// Parse Database_Expression
				$alias->parse($this->db);
			}
		}

		$this->select = array_merge($this->select, $columns);

		return $this;
	}

	public function __toString()
	{
		return '('.$this->compile().')';
	}

	public function compile()
	{
		// SELECT columns FROM table
		$sql = 'SELECT '.implode(', ', $this->flatten($this->select))."\n".
		       'FROM '.implode(', ', $this->flatten($this->from));

		if ( ! empty($this->join))
		{
			foreach ($this->join as $join)
			{

			}
		}

		if ( ! empty($this->where))
		{
			$sql .= "\n".'WHERE '.$this->compile_clauses($this->where);
		}

		if ( ! empty($this->having))
		{
			$sql .= "\n".'HAVING '.$this->compile_clauses($this->having);
		}

		if ( ! empty($this->group_by))
		{
			$sql .= "\n".'GROUP BY '.implode(', ', $this->flatten($this->group_by));
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

	public function join($table, $keys, $value = NULL, $type = NULL)
	{
		$table = $this->db->escape_table($table);

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

		foreach ($tables as $name => &$alias)
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

		foreach ($columns as & $column)
		{
			if ($column instanceof Database_Expression)
			{
				$column = $column->parse($this->db);
			}
			else
			{
				$column = $this->db->escape_table($column);
			}
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
		$this->in_clause = 'HAVING';

		$this->having[] = array('AND', $this->clause($columns, $op, $value));
		return $this;
	}

	public function or_having($columns, $op = '=', $value = NULL)
	{
		$this->in_clause = 'HAVING';

		$this->having[] = array('OR', $this->clause($columns, $op, $value));
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

	public function open($clause = NULL)
	{
		return $this->and_open($clause);
	}

	public function and_open($clause = NULL)
	{
		$clause = ($clause === NULL) ? $this->in_clause : strtoupper($clause);

		if ($clause === 'WHERE')
		{
			$this->where[] = array('AND', '(');
		}
		elseif ($clause === 'HAVING')
		{
			$this->having[] = array('AND', '(');
		}

		return $this;
	}

	public function or_open($clause = NULL)
	{
		$clause = ($clause === NULL) ? $this->in_clause : strtoupper($clause);

		if ($clause === 'WHERE')
		{
			$this->where[] = array('OR', '(');
		}
		elseif ($clause === 'HAVING')
		{
			$this->having[] = array('OR', '(');
		}

		return $this;
	}

	public function close($clause = NULL)
	{
		$clause = ($clause === NULL) ? $this->in_clause : strtoupper($clause);

		if ($clause === 'WHERE')
		{
			$this->where[] = array(NULL, ')');
		}
		elseif ($clause === 'HAVING')
		{
			$this->having[] = array(NULL, ')');
		}

		return $this;
	}

	public function where($columns, $op = '=', $value = NULL)
	{
		return $this->and_where($columns, $op, $value);
	}

	public function and_where($columns, $op = '=', $value = NULL)
	{
		$this->where[] = array('AND', $this->clause($columns, $op, $value));
		return $this;
	}

	public function or_where($columns, $op = '=', $value = NULL)
	{
		$this->where[] = array('OR', $this->clause($columns, $op, $value));
		return $this;
	}

	protected function clause($columns, $op = '=', $value = NULL)
	{
		if ($columns instanceof Database_Expression)
		{
			// Parse Database_Expression
			return $columns->parse($this->db);
		}

		if ( ! is_array($columns))
		{
			$columns = array($columns => $value);
		}

		$op = strtoupper($op);

		$sql = '';
		foreach ($columns as $column => $value)
		{
			if (is_array($value))
			{
				if ($op === 'BETWEEN')
				{
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

			$sql .= $this->db->escape_table($column).' '.$op.' '.$value;
		}

		return $sql;
	}


	protected function flatten(array $values)
	{
		foreach ($values as & $val)
		{
			$val = (string) $val;
		}

		return $values;
	}

	protected function compile_clauses($clauses)
	{
		$last_clause = NULL;

		$sql = '';
		foreach ($clauses as $i => $clause)
		{
			if ($i > 0)
			{
				if ($clause[1] !== ')' AND $last_clause !== '(')
				{
					// Add the proper operator
					$sql .= ' '.$clause[0].' ';
				}
			}

			// Column = "value"
			$sql .= (string) $clause[1];

			$last_clause = $clause[1];
		}

		return $sql;
	}

} // End Database_Builder
