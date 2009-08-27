<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database connection.
 * 
 * $Id: Database_Mysqli.php 4366 2009-05-27 21:12:17Z samsoir $
 * 
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */

define('RUNS_MYSQLND', function_exists('mysqli_fetch_all'));

class Database_Mysqli_Core extends Database {

	// Quote character to use for identifiers (tables/columns/aliases)
	protected $quote = '`';

	public function connect()
	{
		if (is_object($this->connection))
			return;

		extract($this->config['connection']);

		// Persistent connections are supported as of PHP 5.3
		if (RUNS_MYSQLND AND $this->config['persistent'] === TRUE)
		{
			$host = 'p:'.$host;
		}

		$host = isset($host) ? $host : $socket;

		if($this->connection = new mysqli($host, $user, $pass, $database, $port)) {
			
			if (isset($this->config['character_set']))
			{
				// Set the character set
				$this->set_charset($this->config['character_set']);
			}
			
			// Clear password after successful connect
			$this->db_config['connection']['pass'] = NULL;
			
			return $this->connection;
		}

		// Unable to connect to the database
			throw new Database_Exception('#:errno: :error',
				array(':error' => $this->connection->connect_error,
				':errno' => $this->connection->connect_errno));
	}

	public function disconnect()
	{
		return is_object($this->connection) and $this->connection->close();
	}

	public function set_charset($charset)
	{
		// Make sure the database is connected
		is_object($this->connection) or $this->connect();

		if ( ! $this->connection->set_charset($charset))
		{
			// Unable to set charset
			throw new Database_Exception('#:errno: :error',
				array(':error' => $this->connection->connect_error,
				':errno' => $this->connection->connect_errno));
		}
	}

	public function query_execute($sql)
	{
		// Make sure the database is connected
		is_object($this->connection) or $this->connect();

		$result = $this->connection->query($sql);

		// Set the last query
		$this->last_query = $sql;

		return new Database_Mysqli_Result($result, $sql, $this->connection, $this->config['object']);
	}

	public function escape($value)
	{
		// Make sure the database is connected
		is_object($this->connection) or $this->connect();

		return $this->connection->real_escape_string($value);
	}

	public function list_fields($table)
	{
		$tables =& $this->fields_cache;

		if (empty($tables[$table]))
		{
			foreach ($this->field_data($table) as $row)
			{
				// Make an associative array
				$tables[$table][$row['Field']] = $this->sql_type($row['Type']);

				if ($row['Key'] === 'PRI' AND $row['Extra'] === 'auto_increment')
				{
					// For sequenced (AUTO_INCREMENT) tables
					$tables[$table][$row['Field']]['sequenced'] = TRUE;
				}

				if ($row['Null'] === 'YES')
				{
					// Set NULL status
					$tables[$table][$row['Field']]['null'] = TRUE;
				}
			}
		}

		if ( ! isset($tables[$table]))
			throw new Database_Exception('Table :table does not exist in your database.', array(':table' => $table));

		return $tables[$table];
	}

	public function field_data($table)
	{
		return $this->query('SHOW COLUMNS FROM '.$this->quote_table($table), $this->connection)->as_array(TRUE);
	}

	public function list_tables()
	{
		$tables = array();

		foreach ($this->query('SHOW TABLES FROM '.$this->escape($this->config['connection']['database']).' LIKE '.$this->quote($this->table_prefix().'%'))->as_array() as $row)
		{
			// The value is the table name
			$tables[] = current($row);
		}

		return $tables;
	}

} // End Database_MySQLi