<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Unittest library.
 *
 * $Id$
 *
 * @package    Unittest
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unittest_Core {

	protected $paths = array();
	protected $results = array();

	/**
	 * Sets the test path(s).
	 *
	 * @param   string|array  test path(s)
	 * @return  void
	 */
	public function __construct($paths = NULL)
	{
		// Use default test path: application/tests/
		if ($paths === NULL)
		{
			$this->paths = array(APPPATH.'tests/');
		}

		// Normalize all given test paths
		else
		{
			foreach ((array) $paths as $path)
			{
				$this->paths[] = str_replace('\\', '/', realpath($path)).'/';
			}
		}

		Log::add('debug', 'Unittest Library initialized');
	}

	/**
	 * Runs the tests.
	 *
	 * @return  array  raw test results
	 */
	public function run()
	{
		// Recursively iterate over all test paths
		foreach ($this->paths as $test_path)
		{
			foreach
			(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($test_path, RecursiveDirectoryIterator::KEY_AS_PATHNAME))
				as $path => $file
			)
			{
				// Skip files without "_Test" suffix
				if ( ! $file->isFile() OR substr($path, -9) !== '_Test'.EXT)
					continue;

				// The class name should be the same as the file name
				$class = substr($path, strrpos($path, '/') + 1, -(strlen(EXT)));

				// Include the test class
				include_once $path;

				// Validate the class name
				if ( ! class_exists($class, FALSE))
					continue;

				// Loop through all the class methods
				$reflector = new ReflectionClass($class);
				foreach ($reflector->getMethods() as $method)
				{
					// Skip invalid test methods
					if ( ! $method->isPublic() OR $method->isStatic() OR $method->getNumberOfParameters() != 0)
						continue;

					// Test methods should be suffixed with "_test"
					if (substr($method_name = $method->getName(), -5) != '_test')
						continue;

					// Instantiate class
					$object = new $class;

					// Run setup method
					if ($reflector->hasMethod('setup'))
					{
						$object->setup();
					}

					// Run the actual test
					try
					{
						$object->$method_name();
						$this->results[$class][$method_name] = TRUE;
					}
					catch (Exception $e)
					{
						$this->results[$class][$method_name] = FALSE;
					}

					// Run teardown method
					if ($reflector->hasMethod('teardown'))
					{
						$object->teardown();
					}
				}
			}
		}

		return $this->results;
	}

	/**
	 * Generates nice test results.
	 *
	 * @param   boolean  set to TRUE to echo the output instead of returning it
	 * @return  string   if print is FALSE
	 * @return  void     if print is TRUE
	 */
	public function results($print = FALSE)
	{
		foreach ($this->results as $class => $methods)
		{
			echo '<p style="font-weight:bold">', html::specialchars($class), ':</p>';
			echo '<ul>';

			foreach ($methods as $method => $result)
			{
				echo '<li style="color:', ($result === TRUE) ? 'green' : 'red', '">';
				echo html::specialchars($method), ': ', ($result === TRUE) ? 'passed' : 'failed';
				echo '</li>';
			}

			echo '</ul>';
			echo '<hr />';
		}
	}


	public function assert_true($value, $strict = TRUE)
	{
		if ($strict === TRUE AND $value !== TRUE)
			throw new Exception;

		if ($value != TRUE)
			throw new Exception;
	}

	public function assert_false($value, $strict = TRUE)
	{
		if ($strict === TRUE AND $value !== FALSE)
			throw new Exception;

		if ($value != FALSE)
			throw new Exception;
	}

	public function assert_equal($value1, $value2, $strict = TRUE)
	{
		if ($strict === TRUE AND $value1 !== $value2)
			throw new Exception;

		if ($value1 != $value2)
			throw new Exception;
	}

	public function assert_not_equal($value1, $value2, $strict = TRUE)
	{
		if ($strict === TRUE AND $value1 === $value2)
			throw new Exception;

		if ($value1 == $value2)
			throw new Exception;
	}

	public function assert_boolean($value)
	{
		if ( ! is_bool($value))
			throw new Exception;
	}

	public function assert_not_boolean($value)
	{
		if (is_bool($value))
			throw new Exception;
	}

	public function assert_integer($value)
	{
		if ( ! is_int($value))
			throw new Exception;
	}

	public function assert_not_integer($value)
	{
		if (is_int($value))
			throw new Exception;
	}

	public function assert_float($value)
	{
		if ( ! is_float($value))
			throw new Exception;
	}

	public function assert_not_float($value)
	{
		if (is_float($value))
			throw new Exception;
	}

	public function assert_array($value)
	{
		if ( ! is_array($value))
			throw new Exception;
	}

	public function assert_not_array($value)
	{
		if (is_array($value))
			throw new Exception;
	}

	public function assert_object($value)
	{
		if ( ! is_object($value))
			throw new Exception;
	}

	public function assert_not_object($value)
	{
		if (is_object($value))
			throw new Exception;
	}

	public function assert_null($value)
	{
		if ($value !== NULL)
			throw new Exception;
	}

	public function assert_not_null($value)
	{
		if ($value === NULL)
			throw new Exception;
	}

} // End Unittest