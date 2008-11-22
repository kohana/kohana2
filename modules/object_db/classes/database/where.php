<?php

class Database_Where_Core extends Database_Query {

	protected $where = array();

	public function _and($columns, $op = '=', $value = NULL)
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns => $value);
		}

		$statement = array('columns' => $columns, 'op' => $op, 'combine' => NULL);

		if ( ! empty($this->where))
		{
			$statement['combine'] = 'AND';
		}

		$this->where[] = $statement;

		return $this;
	}

	public function _or($columns, $op = '=', $value = NULL)
	{
		if ( ! is_array($columns))
		{
			$columns = array($columns => $value);
		}

		$statement = array('columns' => $columns, 'op' => $op, 'combine' => NULL);

		if ( ! empty($this->where))
		{
			$statement['combine'] = 'OR';
		}

		$this->where[] = $statement;

		return $this;
	}

	public function compile()
	{
		$sql = '';

		if (count($this->where) > 1)
		{
			$sql .= '(';
		}

		foreach ($this->where as $statement)
		{
			if ($statement['combine'] !== NULL)
			{
				$statement['combine'] = ' '.$statement['combine'].' ';
			}

			$statement['op'] = strtoupper($statement['op']);

			foreach ($statement['columns'] as $column => $value)
			{
				if (is_array($value))
				{
					if ($statement['op'] === 'BETWEEN')
					{
						$value = $this->db->escape($value[0]).' AND '.$this->db->escape($value[1]);
					}
					else
					{
						$value = array_map(array($this->db, 'escape'), $value);
						$value = '('.implode(', ', $value).')';
					}
				}
				else
				{
					$value = $this->db->escape($value);
				}

				$sql .= $statement['combine'].$column.' '.$statement['op'].' '.$value;
			}
		}

		if (count($this->where) > 1)
		{
			$sql .= ')';
		}

		return $sql;
	}

} // End Database_Where
