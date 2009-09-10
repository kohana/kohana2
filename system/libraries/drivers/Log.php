<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Log API driver.
 *
 * $Id: Kohana_Log.php 4536 2009-09-03 20:10:01Z nodren $
 *
 * @package    Kohana_Log
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Log_Driver {

	protected $config = array();

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	abstract public function save(array $messages);
}