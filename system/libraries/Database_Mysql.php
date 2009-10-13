<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database connection.
 *
 * $Id$
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Mysql_Core extends Database {

	// Quote character to use for identifiers (tables/columns/aliases)
	protected $quote = '`';

	public function connect()
	{
		if ($this->connection)
			return;

		extract($this->config['connection']);

		// Set the connection type
		$connect = ($this->config['persistent'] === TRUE) ? 'mysql_pconnect' : 'mysql_connect';

		$host = isset($host) ? $host : $socket;
		$port = isset($port) ? ':'.$port : '';

		try
		{
			// Connect to the database
			$this->connection = $connect($host.$port, $user, $pass, TRUE);
		}
		catch (Kohana_PHP_Exception $e)
		{
			// No connection exists
			$this->connection = NULL;

			// Unable to connect to the database
			throw new Database_Exception('#:errno: :error',
				array(':error' => mysql_error(),
				':errno' => mysql_errno()));
		}

		if ( ! mysql_select_db($database, $this->connection))
		{
			// Unable to select database
			throw new Database_Exception('#:errno: :error',
				array(':error' => mysql_error($this->connection),
				':errno' => mysql_errno($this->connection)));
		}

		if (isset($this->config['character_set']))
		{
			// Set the character set
			$this->set_charset($this->config['character_set']);
		}
	}

	public function disconnect()
	{
		try
		{
			// Database is assumed disconnected
			$status = TRUE;

			if (is_resource($this->connection))
			{
				$status = mysql_close($this->connection);
			}
		}
		catch (Exception $e)
		{
			// Database is probably not disconnected
			$status = is_resource($this->connection);
		}

		return $status;
	}

	public function set_charset($charset)
	{
		// Make sure the database is connected
		$this->connection or $this->connect();

		if ( ! mysql_set_charset($charset, $this->connection))
		{
			// Unable to set charset
			throw new Database_Exception('#:errno: :error',
				array(':error' => mysql_error($this->connection),
				':errno' => mysql_errno($this->connection)));
		}
	}

	public function query_execute($sql)
	{
		// Make sure the database is connected
		$this->connection or $this->connect();

		$result = mysql_query($sql, $this->connection);

		// Set the last query
		$this->last_query = $sql;

		return new Database_Mysql_Result($result, $sql, $this->connection, $this->config['object']);
	}

	public function escape($value)
	{
		// Make sure the database is connected
		$this->connection or $this->connect();

		if (($value = mysql_real_escape_string($value, $this->connection)) === FALSE)
		{
			throw new Database_Exception('#:errno: :error',
				array(':error' => mysql_error($this->connection),
				':errno' => mysql_errno($this->connection)));
		}

		return $value;
	}

	public function list_fields($table)
	{
		$result = array();

		foreach ($this->field_data($table) as $row)
		{
			$column = $this->sql_type($row['Type']);

			$column['default'] = $row['Default'];
			$column['nullable'] = $row['Null'] === 'YES';
			$column['sequenced'] = $row['Extra'] === 'auto_increment';

			if (isset($column['length']) AND $column['type'] === 'float')
			{
				list($column['precision'], $column['scale']) = explode(',', $column['length']);
			}

			if ($row['Key'] === 'PRI')
			{
				$column['constraints'][] = 'PRIMARY KEY';
			}
			elseif ($row['Key'] === 'UNI')
			{
				$column['constraints'][] = 'UNIQUE';
			}

			$result[$row['Field']] = $column;
		}

		return $result;
	}

	public function field_data($table)
	{
		return $this->query('SHOW COLUMNS FROM '.$this->quote_table($table))->as_array(TRUE);
	}

	public function list_tables()
	{
		$prefix = strlen($this->table_prefix());
		$tables = array();

		foreach ($this->query('SHOW TABLES FROM '.$this->escape($this->config['connection']['database']).' LIKE '.$this->quote($this->table_prefix().'%'))->as_array() as $row)
		{
			// The value is the table name
			$tables[] = substr(current($row), $prefix);
		}

		return $tables;
	}

} // End Database_MySQL
