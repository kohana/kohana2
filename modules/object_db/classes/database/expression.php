<?php

class Database_Expression_Core {

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function __toString()
	{
		return $this->value;
	}

} // End Database_Expression
