<?php defined('SYSPATH') or die('No direct script access.');
/**
 * MySQL database connection.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_MySQL_Core extends Database {

	protected $_config_required = array('hostname', 'username', 'database');

	public function connect()
	{
		if ($this->_connection)
			return;

		extract($this->_config);

		// Clear the configuration for security
		//$this->_config = array();

		// Set the connection type
		$connect = (isset($persistent) AND $persistent === TRUE) ? 'mysql_pconnect' : 'mysql_connect';

		try
		{
			// Connect to the database
			$this->_connection = $connect($hostname, $username, $password);
		}
		catch (ErrorException $e)
		{
			// No connection exists
			$this->_connection = NULL;

			// Unable to connect to the database
			throw new Database_Exception(mysql_errno(), mysql_error());
		}

		if ( ! mysql_select_db($database, $this->_connection))
		{
			// Unable to select database
			throw new Database_Exception(':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}

		if (isset($charset))
		{
			// Set the character set
			$this->set_charset($charset);
		}
	}

	public function disconnect()
	{
		try
		{
			// Database is assumed disconnected
			$status = TRUE;

			if (is_resource($this->_connection))
			{
				$status = mysql_close($this->_connection);
			}
		}
		catch (Exception $e)
		{
			// Database is probably not disconnected
			$status = is_resource($this->_connection);
		}

		return $status;
	}

	public function set_charset($charset)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		if ( ! mysql_set_charset($charset, $this->_connection))
		{
			// Unable to set charset
			throw new Database_Exception(':error',
				array(':error' => mysql_error($this->_connection)),
				mysql_errno($this->_connection));
		}
	}

	public function query($type, $sql)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		// Execute the query
		if (($result = mysql_query($sql, $this->_connection)) === FALSE)
		{
			// Query failed
			throw new Database_Exception(':error [ :query ]',
				array(':error' => mysql_error($this->_connection), ':query' => $sql),
				mysql_errno($this->_connection));
		}

		// Set the last query
		$this->last_query = $sql;

		if ($type === Database::SELECT)
		{
			// Return an iterator of results
			return new Database_MySQL_Result($result, $this->_config['object']);
		}
		elseif ($type === Database::INSERT)
		{
			// Return the insert id of the row
			return mysql_insert_id($this->_connection);
		}
		else
		{
			// Return the number of rows affected
			return mysql_affected_rows($this->_connection);
		}
	}

	public function escape($value)
	{
		// Make sure the database is connected
		$this->_connection or $this->connect();

		if (($value = mysql_real_escape_string($value, $this->_connection)) === FALSE)
		{
			throw new Database_Exception(':error',
				array(':error' => mysql_errno($this->_connection)),
				mysql_error($this->_connection));
		}

		return $value;
	}

	public function escape_table($table)
	{
		if (is_array($table))
		{
			$table = '`'.$this->_config['table_prefix'].key($table).'` AS `'.$this->_config['table_prefix'].current($table).'`';
		}
		else
		{
			$table = '`'.$this->_config['table_prefix'].$table.'`';
		}

		return str_replace('.', '`.`', $table);
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
			throw new Kohana_Database_Exception('database.table_not_found', $table);

		return $tables[$table];
	}

	public function field_data($table)
	{
		$columns = array();

		foreach($this->query(Database::SELECT, 'SHOW COLUMNS FROM '.$this->escape_table($table), $this->_connection)->as_array() as $row)
		{
			$columns[] = $row;
		}

		return $columns;
	}

} // End Database_Connection_MySQL