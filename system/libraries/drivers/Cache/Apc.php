<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * APC-based Cache driver.
 *
 * $Id$
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Apc_Driver implements Cache_Driver {

	public function __construct()
	{
		if ( ! extension_loaded('apc'))
			throw new Kohana_Exception('cache.extension_not_loaded', 'apc');
	}

	public function get($id)
	{
		return (($return = apc_fetch($id)) === FALSE) ? NULL : $return;
	}

	public function set($id, $data, array $tags = NULL, $lifetime)
	{
		if ( ! empty($tags))
		{
			Kohana_Log::add('error', 'Cache: tags are unsupported by the APC driver');
		}

		return apc_store($id, $data, $lifetime);
	}

	public function find($tag)
	{
		Kohana_Log::add('error', 'Cache: tags are unsupported by the APC driver');

		return array();
	}

	public function delete($id, $tag = FALSE)
	{
		if ($tag === TRUE)
		{
			Kohana_Log::add('error', 'Cache: tags are unsupported by the APC driver');
			return FALSE;
		}
		elseif ($id === TRUE)
		{
			return apc_clear_cache('user');
		}
		else
		{
			return apc_delete($id);
		}
	}

	public function delete_expired()
	{
		return TRUE;
	}

	/**
	 * Sanitize cache keys
	 * Replaces troublesome characters
	 *
	 * @param   string   cache id
	 * @return  string
	 */
	public function sanitize_id($id)
	{
		// Change slashes and spaces to underscores
		return str_replace(array('/', '\\', ' '), '_', $id);
	}

} // End Cache APC Driver