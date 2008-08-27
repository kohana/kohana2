<?php
/**
 * Cache driver interface.
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
	abstract public function set($id, $data, $tags, $lifetime);

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

} // End Cache Driver