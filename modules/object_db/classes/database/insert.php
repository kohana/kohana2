<?php

class Database_Insert_Core extends Database_Query {

	protected $table;
	protected $columns = array();
	protected $values  = array();

	/**
	 * Magic object-to-string method. This method is repeated in Database_Builder,
	 * but Database_Insert does not extend Database_Builder.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return '('.$this->compile().')';
	}

	public function compile()
	{
		// INSERT INTO table (c1, c2, c3)
		$sql = 'INSERT INTO '.$this->table."\n"
		     . '('.implode(', ', $this->columns).')';

		$values = array();
		foreach ($this->values as $set)
		{
			// (val1, val2, val3)
			$values[] = '('.$this->db->escape($set).')';
		}
		// VALUES ...
		$sql .= "\nVALUES\n".implode(",\n", $values);

		return $sql;
	}

	public function table($table)
	{
		// Set the table name
		$this->table = $table;

		return $this;
	}

	public function columns($columns)
	{
		if ( ! is_array($columns))
		{
			$columns = func_get_args();
		}

		// Set the column names
		$this->columns = $columns;

		// Update the column count
		$this->column_count = count($columns);

		return $this;
	}

	public function values(array $values)
	{
		// Get all values
		$values = func_get_args();

		foreach ($values as $set)
		{
			if ( ! is_array($set) OR count($set) !== $this->column_count)
			{
				// Valid sets must be arrays and have the same number of columns
				continue;
			}

			$this->values[] = $set;
		}

		return $this;
	}

} // End Database_Update
