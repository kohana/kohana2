<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * MemcacheD-based Cache driver.
 *
 * $Id: Memcache.php 4603 2009-09-12 20:42:34Z kiall $
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Memcached_Driver extends Cache_Driver {
	protected $config;
	protected $backend;

	public function __construct($config)
	{
		if ( ! extension_loaded('memcached'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'memcached');
		
		$this->config = $config;
		$this->backend = new Memcached;
		foreach ($this->config['options'] as $name => $value)
		{
			$this->backend->setOption($name, $value);
		}

		$this->backend->addServers($this->config['servers']);
	}
	
	public function set($items, $tags = NULL, $lifetime = NULL)
	{
		if ($lifetime !== 0)
		{
			// Memcache driver expects unix timestamp
			$lifetime += time();
		}
		
		if ($tags !== NULL)
		{
			Kohana_Log::add('debug', __('Cache: Memcache driver does not support tags'));
		}

		return $this->backend->setMulti($items, $lifetime);
	}
	
	public function get($keys, $single = FALSE)
	{
		$return = $this->backend->getMulti($keys);
		return ($single) ? current($return) : $return;
	}
	
	/**
	 * Get cache items by tag 
	 */
	public function get_tag($tags)
	{
		Kohana_Log::add('debug', __('Cache: Memcache driver does not support tags'));
		return NULL;
	}

	/**
	 * Delete cache item by key 
	 */
	public function delete($keys)
	{
		foreach ($keys as $key)
		{
			if ( ! $this->backend->delete($key))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Delete cache items by tag 
	 */
	public function delete_tag($tags)
	{
		Kohana_Log::add('debug', __('Cache: Memcache driver does not support tags'));
		return NULL;
	}
	
	/**
	 * Empty the cache
	 */
	public function delete_all()
	{
		return $this->backend->flush();
	}
} // End Cache MemcacheD Driver
