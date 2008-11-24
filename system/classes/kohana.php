<?php

final class Kohana  {

	private static $init = FALSE;
	
	private static $include_paths = array(APPPATH, SYSPATH);

	private static $cache = array();

	public static function init()
	{
		if (self::$init === TRUE)
			return;

		spl_autoload_register(array(__CLASS__, 'auto_load'));
	}

	public static function find_file($dir, $file, $ext = NULL)
	{
		if ($ext === NULL)
		{
			$ext = EXT;
		}
		else
		{
			$ext = '.'.EXT;
		}

		$file = $dir.'/'.$file.$ext;

		if (isset(self::$cache[__FUNCTION__][$file]))
		{
			return self::$cache[__FUNCTION__][$file];
		}

		foreach (self::$include_paths as $path)
		{
			if (file_exists($path.$file))
			{
				return self::$cache[__FUNCTION__][$file] = $path.$file;
			}
		}

		return FALSE;
	}

	public static function auto_load($class)
	{
		if (class_exists($class, FALSE))
			return TRUE;

		$file = str_replace('_', '/', strtolower($class));

		if ($path = self::find_file('classes', $file))
		{
			require $path;
		}
		else
		{
			return FALSE;
		}

		if (class_exists('Kohana_'.$class, FALSE))
		{
			if ($path = self::find_file('extensions', $file))
			{
				require $path;
			}
			else
			{
				eval('class '.$class.' extends Kohana_'.$class.' {}');
			}
		}

		return class_exists($class, FALSE);
	}

	/**
	 * Quick debugging of any variable. Any number of parameters can be set.
	 *
	 * @return  string
	 */
	public static function debug()
	{
		if (func_num_args() === 0)
			return;

		// Get params
		$params = func_get_args();
		$output = array();

		foreach ($params as $var)
		{
			$output[] = '<pre>('.gettype($var).') '.htmlspecialchars(print_r($var, TRUE)).'</pre>';
		}

		return implode("\n", $output);
	}

} // End Kohana
