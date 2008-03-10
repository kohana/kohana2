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
	 * Sets the test path(s), runs the tests inside and stores the results.
	 *
	 * @param   string|array  test path(s)
	 * @return  void
	 */
	public function __construct($paths = NULL)
	{
		// Normalize all given test paths
		foreach ((array) $paths as $path)
		{
			$this->paths[] = str_replace('\\', '/', realpath($path)).'/';
		}

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

				// Reverse-engineer Test class
				$reflector = new ReflectionClass($class);

				// Test classes must extend Unit_Test_Case
				if ( ! $reflector->isSubclassOf(new ReflectionClass('Unit_Test_Case')))
					break;

				// Loop through all the class methods
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
					catch (Kohana_Unit_Test_Exception $e)
					{
						$this->results[$class][$method_name] = $e;
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
	}

	/**
	 * Generates nice test results.
	 *
	 * @return  string  rendered test results html
	 */
	public function report()
	{
		return empty($this->results) ? '' : View::factory('kohana_unit_test')->set('results', $this->results)->render();
	}

	/**
	 * Magically convert this object to a string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->report();
	}

	/**
	 * Magically gets a Unit_Test property.
	 *
	 * @param   string  property name
	 * @return  mixed   variable value if the property is found
	 * @return  void    if the property is not found
	 */
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;
	}

} // End Unit_Test_Core


abstract class Unit_Test_Case {

	public function assert_true($value, $debug = NULL)
	{
		if ($value != TRUE)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_true', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_true_strict($value, $debug = NULL)
	{
		if ($value !== TRUE)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_true_strict', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_false($value, $debug = NULL)
	{
		if ($value != FALSE)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_false', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_false_strict($value, $debug = NULL)
	{
		if ($value !== FALSE)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_false_strict', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_equal($value1, $value2, $debug = NULL)
	{
		if ($value1 != $value2)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_equal', gettype($value1), var_export($value1, TRUE), gettype($value2), var_export($value2, TRUE)), $debug);

		return $this;
	}

	public function assert_not_equal($value1, $value2, $debug = NULL)
	{
		if ($value1 == $value2)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_equal', gettype($value1), var_export($value1, TRUE), gettype($value2), var_export($value2, TRUE)), $debug);

		return $this;
	}

	public function assert_identical($value1, $value2, $debug = NULL)
	{
		if ($value1 !== $value2)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_identical', gettype($value1), var_export($value1, TRUE), gettype($value2), var_export($value2, TRUE)), $debug);

		return $this;
	}

	public function assert_not_identical($value1, $value2, $debug = NULL)
	{
		if ($value1 === $value2)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_identical', gettype($value1), var_export($value1, TRUE), gettype($value2), var_export($value2, TRUE)), $debug);

		return $this;
	}

	public function assert_boolean($value, $debug = NULL)
	{
		if ( ! is_bool($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_boolean', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_boolean($value, $debug = NULL)
	{
		if (is_bool($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_boolean', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_integer($value, $debug = NULL)
	{
		if ( ! is_int($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_integer', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_integer($value, $debug = NULL)
	{
		if (is_int($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_integer', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_float($value, $debug = NULL)
	{
		if ( ! is_float($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_float', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_float($value, $debug = NULL)
	{
		if (is_float($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_float', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_array($value, $debug = NULL)
	{
		if ( ! is_array($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_array', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_array($value, $debug = NULL)
	{
		if (is_array($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_array', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_object($value, $debug = NULL)
	{
		if ( ! is_object($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_object', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_object($value, $debug = NULL)
	{
		if (is_object($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_object', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_null($value, $debug = NULL)
	{
		if ($value !== NULL)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_null', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_null($value, $debug = NULL)
	{
		if ($value === NULL)
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_null', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_empty($value, $debug = NULL)
	{
		if ( ! empty($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_empty', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_not_empty($value, $debug = NULL)
	{
		if (empty($value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_empty', gettype($value), var_export($value, TRUE)), $debug);

		return $this;
	}

	public function assert_pattern($value, $regex, $debug = NULL)
	{
		if ( ! is_string($value) OR ! is_string($regex) OR ! preg_match($regex, $value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_pattern', var_export($value, TRUE), var_export($regex, TRUE)), $debug);

		return $this;
	}

	public function assert_not_pattern($value, $regex, $debug = NULL)
	{
		if ( ! is_string($value) OR ! is_string($regex) OR preg_match($regex, $value))
			throw new Kohana_Unit_Test_Exception(Kohana::lang('unit_test.assert_not_pattern', var_export($value, TRUE), var_export($regex, TRUE)), $debug);

		return $this;
	}

} // End Unit_Test_Case


class Kohana_Unit_Test_Exception extends Exception {

	protected $message = '';
	protected $debug = NULL;
	protected $file = '';
	protected $line = '';

	/**
	 * Set exception message and debug
	 *
	 * @param   string  message
	 * @param   mixed   debug info
	 * @return  void
	 */
	public function __construct($message, $debug = NULL)
	{
		// Failure message
		$this->message = (string) $message;

		// Extra user-defined debug info
		$this->debug = $debug;

		// Retrieve failure location
		$trace = $this->getTrace();
		$this->file = $trace[0]['file'];
		$this->line = $trace[0]['line'];
	}

	/**
	 * Magically gets an object property.
	 *
	 * @param   string  property key
	 * @return  mixed   variable value if the key is found
	 * @return  void    if the key is not found
	 */
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;
	}

} // End Kohana_Unit_Test_Exception