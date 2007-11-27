<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cache driver interface
 */
interface Cache_Driver
{

	/**
	 * Test if a cache exists by id or tag.
	 */
	public function exists($id, $tag = FALSE);

	/**
	 * Set a cache item.
	 */
	public function set($id, $data, $tags, $expiration);

	/**
	 * Find all of the cache ids for a given tag.
	 */
	public function find($tag);

	/**
	 * Get a cache item.
	 */
	public function get($id);

	/**
	 * Delete cache items by id or tag.
	 */
	public function del($id, $tag = FALSE);

} // End Cache Driver