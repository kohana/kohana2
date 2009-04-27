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

	public function connect()
	{
		if ($this->_connection)
			return;

		extract($this->_config['connection']);

		// Set the connection type
		$connect = ($this->_config['persistent'] === TRUE) ? 'mysql_pconnect' : 'mysql_connect';

		$host = isset($host) ? $host : $socket;
		$port = isset($port) ? ':'.$port : '';

		try
		{
			// Connect to the database
			$this->_connection = $connect($host.$port, $user, $pass, TRUE);
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

		if (isset($this->_config['character_set']))
		{
			// Set the character set
			$this->set_charset($this->_config['character_set']);
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

	public function query_execute($sql)
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

		return new Database_MySQL_Result($result, $sql, $this->_connection, $this->_config['object']);
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

	public function escape_table($table, $prefix_alias = FALSE)
	{
		$as = NULL;

		if (is_array($table))
		{
			// Using Table AS Alias

			// Add table prefix to right side of AS if necessary
			$as = ($prefix_alias === TRUE) ? $this->_config['table_prefix'].current($table) : current($table);

			if ($this->_config['escape'])
			{
				$as = '`'.$as.'`';
			}

			$as = ' AS '.$as;

			$table = key($table);
		}

		if ($table === '*')
			return $table;

		// If the table name contains a `, we assume it has functions within (and the tables names/fields themselves are within backticks)
		if (strpos($table, '`') !== FALSE)
		{
			// Replace `table.col` occurrences with `[table_prefix]table.col` (if no . is found, leave it as is)
			$table = preg_replace('/`(.*?)\.(.*?)`/', '`'.$this->_config['table_prefix'].'$1`.`$2`', $table);
		}
		else
		{
			// Escape the table name and add the prefix
			$table = $this->_config['table_prefix'].$table;

			if ($this->_config['escape'])
			{
				// Escape it
				$table = '`'.$table.'`';
				$table = str_replace('.', '`.`', $table);
			}
		}

		// Unescape any asterisks
		$table = str_replace('`*`', '*', $table);

		return $table.$as;
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

		foreach($this->query_execute('SHOW COLUMNS FROM '.$this->escape_table($table), $this->_connection)->as_array() as $row)
		{
			$columns[] = $row;
		}

		return $columns;
	}

} // End Database_MySQL