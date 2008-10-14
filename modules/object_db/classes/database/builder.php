<?php

abstract class Database_Builder_Core extends Database_Query {

	protected $join  = array();
	protected $where = array();

	protected $join_types = array('LEFT', 'RIGHT', 'INNER', 'OUTER', 'RIGHT OUTER', 'LEFT OUTER', 'FULL');

	// Contains the last WHERE, LIKE, IN, or BETWEEN builder
	protected $last_build;

	protected function flatten(array $values)
	{
		foreach ($values as & $val)
		{
			$val = (string) $val;
		}

		return $values;
	}

	public function compile()
	{
		$sql = '';

		if ( ! empty($this->join))
		{
			empty($sql) or $sql .= "\n";
			foreach ($this->join as $i => $table)
			{
				list($table, $keys, $type) = $table;

				if ($i > 0)
				{
					$sql .= "\n";
				}

				if ($type !== NULL)
				{
					$type = $type.' ';
				}

				$sql .= $type.'JOIN ';
				if (is_array($table))
				{
					// (t1, t2, (SQL), t4)
					$sql .= '('.implode(', ', $this->flatten($table)).')';
				}
				else
				{
					// table
					$sql .= $table;
				}
				$sql .= ' ON ';

				if (count($keys) > 1)
				{
					$sql .= '(';
				}
				foreach ($keys as $c1 => $c2)
				{
					$keys[$c1] = $c1.' = '.$c2;
				}
				// column1 = column2
				$sql .= implode(', ', $keys);
				if (count($keys) > 1)
				{
					$sql .= ')';
				}
			}
		}

		if ( ! empty($this->where))
		{
			empty($sql) or $sql .= "\n";
			foreach ($this->where as $i => $where)
			{
				if ($i > 0)
				{
					// Add the proper operator
					$sql .= "\n".$where[0].' ';
				}
				else
				{
					$sql .= 'WHERE ';
				}

				// WHERE column = "value"
				$sql .= (string) $where[1];
			}
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
		$this->last_build = new Database_Where($this->db);

		$this->last_build->_and($columns, $op, $value);

		$this->where[] = array('AND', $this->last_build);

		return $this;
	}

	public function or_where($columns, $op = '=', $value = NULL)
	{
		// Create a new WHERE statement and make it the last build
		$this->last_build = new Database_Where($this->db);

		$this->last_build->_or($columns, $op, $value);

		$this->where[] = array('OR', $this->last_build);

		return $this;
	}

	public function prepare()
	{
		return $this->db->prepare($this->compile());
	}

} // End Database_Builder
