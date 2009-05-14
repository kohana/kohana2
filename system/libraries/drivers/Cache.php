<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Cache driver abstract class.
 *
 * $Id$
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Cache_Driver {

	/**
	 * Set a cache item.
	 */
	abstract public function set($id, $data, array $tags = NULL, $lifetime);

	/**
	 * Find all of the cache ids for a given tag.
	 */
	abstract public function find($tag);

	/**
	 * Get a cache item.
	 * Return NULL if the cache item is not found.
	 */
	abstract public function get($id);

	/**
	 * Delete cache items by id or tag.
	 */
	abstract public function delete($id, $tag = FALSE);

	/**
	 * Deletes all expired cache items.
	 */
	abstract public function delete_expired();

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

} // End Cache Driver