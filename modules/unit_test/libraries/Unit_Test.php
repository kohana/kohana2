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

		Log::add('debug', 'Unit_Test Library initialized');

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
					catch (Exception $e)
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
	 * @param   boolean      set to TRUE to echo the output instead of returning it
	 * @return  string|void  rendered test results html
	 */
	public function report($print = FALSE)
	{
		return View::factory('kohana_unit_test')->set('results', $this->results)->render($print);
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

} // End Unit_Test