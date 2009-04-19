<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Log API driver.
 *
 * @package    Kohana_Log
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Log_Database_Driver extends Log_Driver {

	public function save(array $messages)
	{
		do
		{
			// Load the next message
			$to_insert = array_combine(array('date', 'level', 'message'), array_shift($messages));

			// Add a new message line
			Database::instance($this->config['group'])->insert($this->config['table'], $to_insert);
		}
		while ( ! empty($messages));
	}
}