<?php

class Database_Select_Core extends Database_Builder {

	protected $columns = array();

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

		$this->columns = $columns;

		return $this;
	}

	public function __toString()
	{
		return '('.$this->compile().')';
	}

	public function compile()
	{
		return 'SELECT '.implode(', ', $this->flatten($this->columns))."\n".parent::compile();
	}

} // End Database_Select
