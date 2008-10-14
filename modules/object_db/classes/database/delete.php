<?php

class Database_Delete_Core extends Database_Builder {

	protected $from = array();

	public function compile()
	{
		$sql = 'DELETE FROM '.implode(', ', $this->flatten($this->from));
		
		if ($where = parent::compile())
		{
			$sql .= "\n".$where;
		}
		else
		{
			$sql .= "\nWHERE 1";
		}

		return $sql;
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

} // End Database_Delete
