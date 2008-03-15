<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides database access in a platform agnostic way, using simple query building blocks.
 *
 * $Id: Database.php 2303 2008-03-14 01:00:54Z zombor $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Database_Insert_Core extends Database_Query_Builder {

	/**
	 * Compiles an insert string and runs the query.
	 *
	 * @param   string  table name
	 * @param   array   array of key/value pairs to insert
	 * @return  object  This Database object.
	 */
	public function insert($table = '', $set = NULL)
	{
		if ( ! is_null($set))
		{
			$this->set($set);
		}

		if ($this->set == NULL)
			throw new Kohana_Database_Exception('database.must_use_set');

		if ($table == '')
		{
			if ( ! isset($this->from[0]))
				throw new Kohana_Database_Exception('database.must_use_table');

			$table = $this->from[0];
		}

		$sql = $this->driver->insert($this->config['table_prefix'].$table, array_keys($this->set), array_values($this->set));

		$this->reset_write();
		return $this->query($sql);
	}
}