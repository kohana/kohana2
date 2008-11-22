<?php

class Database_Update_Core extends Database_Builder {

	protected $table;
	protected $set = array();

	public function table($table)
	{
		// Set the table name
		$this->table = $table;

		return $this;
	}

	public function set($columns, $value = NULL)
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns => $value);
		}

		$this->set = array_merge($this->set, $columns);

		return $this;
	}

	public function compile()
	{
		// UPDATE table SET
		$sql = 'UPDATE  '.$this->table."\n";

		$columns = array();
		foreach ($this->set as $column => $value)
		{
			// column = 'value'
			$columns[] = $column.' = '.$this->db->escape($value);
		}
		$sql .= 'SET '.implode(",\n\t", $columns);

		if ($where = parent::compile())
		{
			// WHERE ... JOIN ...
			$sql .= "\n".$where;
		}

		return $sql;
	}

} // End Database_Update
