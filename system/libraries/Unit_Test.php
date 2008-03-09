<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Unit_Test library.
 *
 * $Id$
 *
 * @package    Unit_Test
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Unit_Test_Core {

	protected $paths = array();
	protected $results = array();

	/**
	 * Sets the test path(s).
	 *
	 * @param   string|array  test path(s)
	 * @return  void
	 */
	public function __construct($paths)
	{
		// Normalize all given test paths
		foreach ((array) $paths as $path)
		{
			$this->paths[] = str_replace('\\', '/', realpath($path)).'/';
		}

		Log::add('debug', 'Unit_Test Library initialized');

		// Automatically run tests
		$this->run();
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

					// Instantiate Test class
					$object = new $class;

					// Test classes must extend Unit_Test_Case
					if ( ! $object instanceof Unit_Test_Case)
						break;

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

					// Cleanup
					unset($object);
				}
			}
		}

		// Return raw results array
		return $this->results;
	}

	/**
	 * Generates nice test results.
	 *
	 * @param   boolean      set to TRUE to echo the output instead of returning it
	 * @return  string|void  rendered test results html
	 */
	public function render($print = FALSE)
	{
		$view = new View('kohana_unit_test');
		$view->results = $this->results;
		return $view->render($print);
	}

	/**
	 * Magically convert this object to a string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}

} // End Unit_Test_Core


class Unit_Test_Case {

	public function assert_true($value, $strict = FALSE)
	{
		if ($strict === TRUE AND $value !== TRUE)
			throw new Exception;

		if ($value != TRUE)
			throw new Exception;
	}

	public function assert_false($value, $strict = FALSE)
	{
		if ($strict === TRUE AND $value !== FALSE)
			throw new Exception;

		if ($value != FALSE)
			throw new Exception;
	}

	public function assert_equal($value1, $value2, $strict = FALSE)
	{
		if ($strict === TRUE AND $value1 !== $value2)
			throw new Exception;

		if ($value1 != $value2)
			throw new Exception;
	}

	public function assert_not_equal($value1, $value2, $strict = FALSE)
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

	public function assert_empty($value)
	{
		if ( ! empty($value))
			throw new Exception;
	}

	public function assert_not_empty($value)
	{
		if (empty($value))
			throw new Exception;
	}

	public function assert_pattern($value, $regex)
	{
		if ( ! preg_match($regex, $value))
			throw new Exception;
	}

	public function assert_not_pattern($value, $regex)
	{
		if (preg_match($regex, $value))
			throw new Exception;
	}

} // End Unit_Test_Case