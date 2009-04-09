<?php

class Database_Builder_Core {

	protected $join_types = array('LEFT', 'RIGHT', 'INNER', 'OUTER', 'RIGHT OUTER', 'LEFT OUTER', 'FULL');

	// Contains the last WHERE, LIKE, IN, or BETWEEN builder
	protected $last_build;

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

	public function clause()
	{
		return new Database_Clause($this->db);
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
		return '('.$this->compile().')';
	}

	public function compile()
	{
		// SELECT columns FROM table
		$sql = 'SELECT '.implode(', ', $this->flatten($this->select))."\n".
		       'FROM '.implode(', ', $this->flatten($this->from));

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
		// JOINs cannot be reused
		$this->last_build = NULL;

		if (is_string($table) AND stristr($table, ' AS ') !== FALSE)
		{
			// @todo: This should be in escape_table
			$table = str_ireplace(' AS ', ' AS ', $table);
		}

		if ( ! is_array($keys))
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
		$columns = func_get_args();

		$this->group_by = array_merge($this->group_by, $columns);

		return $this;
	}

	public function having($columns, $op = '=', $value = NULL)
	{
		return $this->and_having($columns, $op, $value);
	}

	public function and_having($columns, $op = '=', $value = NULL)
	{
		// Create a new HAVING statement and make it the last build
		$this->last_build = new Database_Having($this->db);

		$this->last_build->_and($columns, $op, $value);

		$this->having[] = array('AND', $this->last_build);

		return $this;
	}

	public function or_having($columns, $op = '=', $value = NULL)
	{
		// Create a new HAVING statement and make it the last build
		$this->last_build = new Database_Having($this->db);

		$this->last_build->_or($columns, $op, $value);

		$this->having[] = array('OR', $this->last_build);

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

	public function _and($column, $op = '=', $value = NULL)
	{
		if (is_object($this->last_build))
		{
			$this->last_build->_and($column, $op, $value);
		}
		else
		{
			$this->where($column, $op, $value);
		}

		return $this;
	}

	public function _or($column, $op = '=', $value = NULL)
	{
		if (is_object($this->last_build))
		{
			$this->last_build->_or($column, $op, $value);
		}
		else
		{
			$this->where($column, $op, $value);
		}

		return $this;
	}

	public function where($columns, $op = '=', $value = NULL)
	{
		return $this->and_where($columns, $op, $value);
	}

	public function and_where($columns, $op = '=', $value = NULL)
	{
		// Create a new WHERE statement and make it the last build
		$this->last_build = new Database_Clause($this->db);

		$this->last_build->_and($columns, $op, $value);

		$this->where[] = array('AND', $this->last_build);

		return $this;
	}

	public function or_where($columns, $op = '=', $value = NULL)
	{
		// Create a new WHERE statement and make it the last build
		$this->last_build = new Database_Clause($this->db);

		$this->last_build->_or($columns, $op, $value);

		$this->where[] = array('OR', $this->last_build);

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

	protected function compile_clauses($clauses)
	{
		$sql = '';
		foreach ($clauses as $i => $clause)
		{
			if ($i > 0)
			{
				// Add the proper operator
				$sql .= "\n".$clause[0].' ';
			}

			// Column = "value"
			$sql .= (string) $clause[1];
		}

		return $sql;
	}

} // End Database_Builder
