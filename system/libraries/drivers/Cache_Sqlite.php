<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SQLite-based Cache driver.
 *
 * @package    Cache
 * @author     Kohana Team
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Cache_Sqlite_Driver implements Cache_Driver {

	// SQLite database instance
	protected $db;

	// Database error messages
	protected $error;

	/**
	 * Tests that the storage location is a directory and is writable.
	 */
	public function __construct($filename)
	{
		// Find the real path to the directory
		$filename = str_replace('\\', '/', realpath($filename));

		if ( ! is_file($filename) OR ! is_writable($filename))
			throw new Kohana_Exception('cache.unwritable', $filename);

		// Get the filename from the directory
		$directory = substr($filename, 0, strrpos($filename, '/') + 1);

		// Make sure the cache directory is writable
		if ( ! is_dir($directory) OR ! is_writable($directory))
			throw new Kohana_Exception('cache.unwritable', $directory);

		// Open the database
		$this->db = sqlite_factory($filename, '0666', $error);

		// Throw an exception if there's an error
		if ( ! empty($error))
			throw new Kohana_Exception('cache.driver_error', sqlite_error_string($error));

		// Directory is valid
		$this->directory = $directory;
	}

	/**
	 * Checks if a cache id is already set.
	 *
	 * @param  string   cache id
	 * @return boolean
	 */
	public function exists($id)
	{
		// Find the id that matches
		$query = $this->db->query('SELECT id FROM caches WHERE id = "'.$id.'"', SQLITE_BOTH, $error);

		return ($query->numRows() > 0);
	}

	/**
	 * Sets a cache item to the given data, tags, and expiration.
	 *
	 * @param   string   cache id to set
	 * @param   string   data in the cache
	 * @param   array    cache tags
	 * @param   integer  timestamp
	 * @return  bool
	 */
	public function set($id, $data, $tags, $expiration)
	{
		// Find the data hash
		$hash = sha1($data);

		// Escape the data
		$data = sqlite_escape_string($data);

		// Escape the tags
		$tags = sqlite_escape_string(implode(',', $tags));

		if ($this->exists($id))
		{
			$this->db->unbufferedQuery("UPDATE caches SET hash = '$hash', tags = '$tags', expiration = '$expiration', data = '$data' WHERE id = '$id'", SQLITE_BOTH, $error);
		}
		else
		{
			$this->db->unbufferedQuery("INSERT INTO caches VALUES('$id', '$hash', '$tags', '$expiration', '$data')", SQLITE_BOTH, $error);
		}

		// Log errors
		empty($error) or Log::add('error', 'Cache: unable to write '.$id.' to cache database');

		return empty($error);
	}

	/**
	 * Finds an array of ids for a given tag.
	 *
	 * @param  string  tag name
	 * @return array   of ids that match the tag
	 */
	public function find($tag)
	{
		$query = $this->db->query("SELECT id FROM caches WHERE tags LIKE '%$tag%'", SQLITE_BOTH, $error);

		if (empty($error) AND $query->numRows() > 0)
		{
			$array = array();
			while($row = $query->fetchObject())
			{
				// Add each id to the array
				$array[] = $row->id;
			}
			return $array;
		}

		return FALSE;
	}

	/**
	 * Fetches a cache item. This will delete the item if it is expired or if
	 * the hash does not match the stored hash.
	 *
	 * @param  string  cache id
	 * @return mixed|NULL
	 */
	public function get($id)
	{
		$query = $this->db->unbufferedQuery("SELECT id, hash, expiration, data FROM caches WHERE id = '$id' LIMIT 1", SQLITE_BOTH, $error);

		if (empty($error) AND $cache = $query->fetchObject())
		{
			// Make sure the expiration is valid and that the hash matches
			if (($cache->expiration != 0 AND $cache->expiration <= time()) OR $cache->hash !== sha1($cache->data))
			{
				// Cache is not valid
				$this->del($cache->id);
				return NULL;
			}
		}
		else
		{
			// Nothing found
			return NULL;
		}

		return $cache->data;
	}

	/**
	 * Deletes a cache item by id or tag
	 *
	 * @param  string  cache id or tag, or TRUE for "all items"
	 * @param  bool    use tags
	 * @return bool
	 */
	public function del($id, $tag = FALSE)
	{
		if ($id === TRUE)
		{
			// Delete all caches
			$this->db->unbufferedQuery('DELETE FROM caches WHERE 1', SQLITE_BOTH, $error);
		}
		elseif ($tag == FALSE)
		{
			// Delete by id
			$this->db->unbufferedQuery('DELETE FROM caches WHERE id = "'.$id.'"', SQLITE_BOTH, $error);
		}
		else
		{
			// Delete by tags
			$this->db->unbufferedQuery("DELETE FROM caches WHERE tags LIKE '%$tag%'", SQLITE_BOTH, $error);
		}

		// Log errors
		empty($error) or Log::add('error', 'Cache: Unable to delete cache: '.$id);

		return empty($error);
	}

	/**
	 * Deletes all cache files that are older than the current time.
	 */
	public function delete_expired()
	{
		// Delete all expired caches
		$this->db->unbufferedQuery('DELETE FROM caches WHERE expiration != 0 AND expiration <= '.time(), SQLITE_BOTH, $error);

		return TRUE;
	}

} // End Cache SQLite Driver