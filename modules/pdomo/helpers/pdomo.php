<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana PDO Model helper. This manages database instances and acts as a
 * factory for PDO models.
 *
 * $Id$
 *
 * @package    pdomo
 * @author     Woody Gilk
 * @copyright  (c) 2007 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class pdomo_Core {

	// Default database instance
	protected static $instance;

	// Database registry
	protected static $registry = array();

	/**
	 * Returns the default database instance.
	 *
	 * @return  object
	 */
	public static function instance()
	{
		return self::$instance;
	}

	/**
	 * Gets and sets database instances from the registry.
	 *
	 * @param   string  database name
	 * @param   object  PDO instance
	 * @return  object
	 */
	public static function registry($name, $db = NULL)
	{
		if (is_object($db) AND ($db instanceof PDO))
		{
			// Set a new db in the registry
			self::$registry[$name] = $db;

			// Make all PDO databases throw exceptions for errors
			self::$registry[$name]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// Set the instance if none has been created
			empty(self::$instance) and self::$instance = self::$registry[$name];
		}

		return isset(self::$registry[$name]) ? self::$registry[$name] : NULL;
	}

	/**
	 * Acts as a factory for PDO models. By default, all models will be created
	 * with the default database instance. To use a different database instance,
	 * pass the instance name as the second parameter.
	 *
	 * @throws  Kohana_Exception
	 * @param   string   model name
	 * @param   string   database name
	 * @return  object
	 */
	public static function factory($name, $db = NULL)
	{
		static $objects = array();

		if ($db === NULL)
		{
			$db = self::$instance;
		}
		elseif (isset(self::$registry[$db]))
		{
			$db = self::$registry[$db];
		}
		else
		{
			throw new Kohana_Exception('pdo.no_database_instance');
		}

		// Get the object hash of the database
		$hash = spl_object_hash($db);

		if (empty($objects[$hash][$name]))
		{
			// Set the class name of the model
			$class = ucfirst($name).'_Model';

			// Create and cache the model
			$objects[$hash][$name] = new $class($db);
		}

		// Return a clone of the empty model
		return (clone $objects[$hash][$name]);
	}

} // End pdomo