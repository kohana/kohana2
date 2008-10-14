<?php

class Database_Select_Core extends Database_Builder {

	protected $select   = array();
	protected $group_by = array();
	protected $having   = array();
	protected $order_by = array();
	protected $limit    = NULL;
	protected $offset   = NULL;

	protected $order_directions = array('ASC', 'DESC', 'RAND()');

	public function __toString()
	{
		return '('.$this->compile().')';
	}

	public function compile()
	{
		// SELECT columns
		$sql = 'SELECT '.implode(', ', $this->flatten($this->select))."\n";

		// FROM ... JOIN ... WHERE ...
		$sql .= parent::compile();

		if ( ! empty($this->group_by))
		{
			// GROUP BY column
			$sql .= "\n".'GROUP BY '.implode(', ', $this->flatten($this->group_by));
		}

		if ( ! empty($this->having))
		{
			$sql .= "\n";
			foreach ($this->having as $i => $having)
			{
				if ($i > 0)
				{
					// Add the proper operator
					$sql .= "\n".$having[0].' ';
				}
				else
				{
					$sql .= 'HAVING ';
				}

				// WHERE column = "value"
				$sql .= (string) $having[1];
			}
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
			$sql .= "\nLIMIT {$this->limit}";
		}

		if (is_int($this->offset))
		{
			$sql .= "\nOFFSET {$this->offset}";
		}

		return $sql;
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

		$this->select = array_merge($this->select, $columns);

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

} // End Database_Select
