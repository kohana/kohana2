<?php
/**
 * Log API driver.
 *
 * $Id: Log.php 4679 2009-11-10 01:45:52Z isaiah $
 *
 * @package    Kohana_Log
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

namespace Driver;

defined('SYSPATH') or die('No direct script access.');

abstract class Log {

	protected $config = array();

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	abstract public function save(array $messages);
}