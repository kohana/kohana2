<?php

class Database_Statement_Core extends PDOStatement {

	protected $db;

	protected function __construct(PDO $db)
	{
		$this->db = $db;
		$this->setFetchMode(PDO::FETCH_ASSOC);
	}

	public function bind($key, & $value)
	{
		$this->bindParam($key, $value);

		return $this;
	}

} // End Database_Statement
