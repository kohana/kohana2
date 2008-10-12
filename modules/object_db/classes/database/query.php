<?php

abstract class Database_Query_Core {

	protected $db;

	final public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function __toString()
	{
		return $this->compile();
	}

	abstract public function compile();

} // End Database_Query
