<?php

namespace Driver\Session

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Session cache driver.
 *
 * Cache library config goes in the session.storage config entry:
 * $config['storage'] = array(
 *     'driver' => 'apc',
 *     'requests' => 10000
 * );
 * Lifetime does not need to be set as it is
 * overridden by the session expiration setting.
 *
 * $Id: Cache.php 4729 2009-12-29 20:35:19Z isaiah $
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Cache implements \Driver\Session {

	protected $cache;
	protected $encrypt;

	public function __construct()
	{
		// Load Encrypt library
		if (\Kernel\Kohana::config('session.encryption'))
		{
			$this->encrypt = new \Library\Encrypt;
		}

		\Library\Kohana_Log::add('debug', 'Session Cache Driver Initialized');
	}

	public function open($path, $name)
	{
		$config = \Kernel\Kohana::config('session.storage');

		if (empty($config))
		{
			// Load the default group
			$config = \Kernel\Kohana::config('cache.default');
		}
		elseif (is_string($config))
		{
			$name = $config;

			// Test the config group name
			if (($config = \Kernel\Kohana::config('cache.'.$config)) === NULL)
				throw new \Kernel\Kohana_Exception('The :group: group is not defined in your configuration.', array(':group:' => $name));
		}

		$config['lifetime'] = (\Kernel\Kohana::config('session.expiration') == 0) ? 86400 : \Kernel\Kohana::config('session.expiration');
		$this->cache = new \Library\Cache($config);

		return is_object($this->cache);
	}

	public function close()
	{
		return TRUE;
	}

	public function read($id)
	{
		$id = 'session_'.$id;
		if ($data = $this->cache->get($id))
		{
			return \Kernel\Kohana::config('session.encryption') ? $this->encrypt->decode($data) : $data;
		}

		// Return value must be string, NOT a boolean
		return '';
	}

	public function write($id, $data)
	{
		if ( ! Session::$should_save)
			return TRUE;

		$id = 'session_'.$id;
		$data = \Kernel\Kohana::config('session.encryption') ? $this->encrypt->encode($data) : $data;

		return $this->cache->set($id, $data);
	}

	public function destroy($id)
	{
		$id = 'session_'.$id;
		return $this->cache->delete($id);
	}

	public function regenerate()
	{
		session_regenerate_id(TRUE);

		// Return new session id
		return session_id();
	}

	public function gc($maxlifetime)
	{
		// Just return, caches are automatically cleaned up
		return TRUE;
	}

} // End Session Cache Driver
