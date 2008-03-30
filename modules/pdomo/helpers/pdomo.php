<?php defined('SYSPATH') or die('No direct script access.');

class pdomo_Core {

	protected static $instance;

	protected static $registry = array();

	public static function instance()
	{
		return self::$instance;
	}

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
			throw new Kohana_User_Exception('pdomo Error', 'No database instance found.');
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