<?php

class Database_Expression_Core {

	protected $expression;

	public function __construct($expression)
	{
		$this->expression = $expression;
	}

	public function __toString()
	{
		return $this->expression;
	}
}