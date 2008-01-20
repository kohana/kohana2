<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Memcache-based Cache driver.
 *
 * @package	   Cache
 * @author	   Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license	   http://kohanaphp.com/license.html
 */
class Cache_Memcache_Driver implements Cache_Driver {

	private $_mc = null;
	private $flag = null;

	public function __construct()
	{
		if ( ! extension_loaded('memcache'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'memcache')

		$this->backend = new Memcache;
		$this->flags = Config::item('cache_memcache.compression') ? MEMCACHE_COMPRESSED : 0;

		foreach (Config::item('cache_memcache.servers') as $server)
		{
			// Make sure all required keys are set
			$server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => FALSE);

			// Add the server to the pool
			$this->backend->addServer($server['host'], $server['port'], (bool) $server['persistent'])
				or Log::add('error', 'Cache: Connection failed: '.$server['host']);
		}
	}

	public function find($tag)
	{
		return FALSE;
	}

	public function get($id)
	{
		return $this->backend->get($id);
	}

	public function set($id, $data, $tags, $expiration)
	{
		count($tags) and Log::add('error', 'Cache: Tags are unsupported by the memcache driver');

		return $this->backend->set($id, $data, $this->flag, $expiration);
	}

	public function delete($id, $tag = FALSE)
	{
		if ($id === TRUE)
		{
			return $this->backend->flush();
		}
		elseif ($tag == FALSE)
		{
			return $this->backend->delete($id);
		}
		else
		{
			return TRUE;
		}
	}

	public function delete_expired()
	{
		return TRUE;
	}

}// End Cache Memcache Driver