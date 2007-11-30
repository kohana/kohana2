<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Provides a driver-based interface for finding, creating, and deleting cached
 * resources. Caches are identified by a unique string. Tagging of caches is
 * also supported, and caches can be found and deleted by id or tag.
 *
 * $Id$
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Core {

	// For garbage collection
	protected static $loaded;

	// Configuration
	protected $config;

	// Driver object
	protected $driver;

	/**
	 * Loads the configured driver and validates it.
	 *
	 * @param  array  custom configuration
	 * @return void
	 */
	public function __construct($config = array())
	{
		// Load configuration
		$this->config = empty($config) ? Config::item('cache') : $config;

		try
		{
			$driver = 'Cache_'.ucfirst($this->config['driver']).'_Driver';

			// Manually autoload so that exceptions can be caught
			Kohana::auto_load($driver);
		}
		catch (Kohana_Exception $e)
		{
			// Driver was not found
			throw new Kohana_Exception('cache.driver_not_supported', $this->config['driver']);
		}

		// Initialize the driver
		$this->driver = new $driver($this->config['params']);

		// Validate the driver
		if ( ! in_array('Cache_Driver', class_implements($this->driver)))
			throw new Kohana_Exception('cache.driver_not_supported', 'Cache drivers must use the Cache_Driver interface.');

		Log::add('debug', 'Cache Library initialized.');

		if (self::$loaded != TRUE)
		{
			if (mt_rand(0, (int) $this->config['requests']) === 1)
			{
				// Do garbage collection
				$this->driver->delete_expired();

				Log::add('debug', 'Cache: Expired caches deleted.');
			}

			// Cache has been loaded once
			self::$loaded = TRUE;
		}
	}

	/**
	 * Fetches a cache by id. Non-string cache items are automatically
	 * unserialized before the cache is returned. NULL is returned when
	 * a cache item is not found.
	 *
	 * @param  string  cache id
	 * @return mixed   cached data or NULL
	 */
	public function get($id)
	{
		// Change slashes to colons
		$id = str_replace(array('/', '\\'), ':', $id);

		if ($data = $this->driver->get($id))
		{
			if (substr($data, 0, 14) === '<{serialized}>')
			{
				// Data has been serialized, unserialize now
				$data = unserialize(substr($data, 14));
			}
		}

		return $data;
	}

	/**
	 * Fetches all of the caches for a given tag. An empty array will be
	 * returned when no matching caches are found.
	 *
	 * @param  string  cache tag
	 * @return array   all cache items matching the tag
	 */
	public function find($tag)
	{
		if ($ids = $this->driver->find($tag))
		{
			$data = array();
			foreach($ids as $id)
			{
				// Load each cache item and add it to the array
				if (($cache = $this->get($id)) !== NULL)
				{
					$data[$id] = $cache;
				}
			}
			return $data;
		}

		return array();
	}

	/**
	 * Set a cache item by id. Tags may also be added and a custom lifetime
	 * can be set. Non-string data is automatically serialized.
	 *
	 * @param  string  unique cache id
	 * @param  mixed   data to cache
	 * @param  array   tags for this item
	 * @param  integer number of seconds until the cache expires
	 * @return bool
	 */
	function set($id, $data, $tags = NULL, $lifetime = NULL)
	{
		if (is_resource($data))
			throw new Kohana_Exception('cache.resources');

		// Change slashes to colons
		$id = str_replace(array('/', '\\'), ':', $id);

		if ( ! is_string($data))
		{
			// Serialize all non-string data, so that types can be preserved
			$data = '<{serialized}>'.serialize($data);
		}

		if (empty($tags))
		{
			$tags = array();
		}
		else
		{
			// Make sure that tags is an array
			$tags = (array) $tags;
		}

		if ($lifetime === NULL)
		{
			// Get the default lifetime
			$lifetime = $this->config['lifetime'];
		}

		if ($lifetime !== 0)
		{
			// Lifetime is the current timestamp + the lifetime in seconds
			$lifetime += time();
		}

		// Remove old cache files
		$this->driver->del($id);

		return $this->driver->set($id, $data, $tags, $lifetime);
	}

	/**
	 * Delete a cache item by id.
	 *
	 * @param  string  cache id
	 * @return bool
	 */
	public function del($id)
	{
		// Change slashes to colons
		$id = str_replace(array('/', '\\'), ':', $id);

		return $this->driver->del($id);
	}

	/**
	 * Delete all cache items with a given tag.
	 *
	 * @param  string  cache tag name
	 * @return bool
	 */
	public function del_tag($tag)
	{
		return $this->driver->del(FALSE, $tag);
	}

	/**
	 * Delete ALL cache items items.
	 *
	 * @return bool
	 */
	public function del_all()
	{
		return $this->driver->del(TRUE);
	}

} // End Cache