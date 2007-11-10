<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Profiler
 *  Adds useful information to the bottom of the current page for debugging and optimization purposes.
 *
 *  Benchmarks   - The times and memory usage of benchmarks run by the <Benchmark> library
 *  Database     - The raw SQL and number of affected rows of <Database> queries
 *  POST Data    - The name and values of any POST data submitted to the current page
 *  Session Data - Data stored in the current session if using the <Session> library
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Profiler_Core {

	/*
	 * Constructor: __construct
	 *  Adds event for adding the profile output to the page when displayed.
	 */
	public function __construct()
	{
		// Add profiler to page output automatically
		Event::add('system.display', array($this, 'render'));

		Log::add('debug', 'Profiler Library initialized');
	}

	/*
	 * Method: disable
	 *  Disables the profiler for this page only.
	 *  Best used when profiler is autoloaded.
	 */
	public function disable()
	{
		// Removes itself from the event queue
		Event::clear('system.display', array($this, 'render'));
	}

	/*
	 * Method: render
	 *  Render the profiler. Output is added to
	 *  the bottom of the page by default.
	 *
	 * Parameters:
	 *  return - return the output if TRUE
	 *
	 * Returns:
	 *  The profiler output if $return is TRUE
	 */
	public function render($return = FALSE)
	{
		$data = array
		(
			'benchmarks' => array(),
			'queries'    => FALSE
		);

		// Load database benchmarks, if Database has been loaded
		if (class_exists('Database', FALSE))
		{
			$data['queries'] = Database::$benchmarks;
		}

		// Clean unique id from system benchmark names
		foreach (Benchmark::get(TRUE) as $name => $time)
		{
			$data['benchmarks'][str_replace(SYSTEM_BENCHMARK.'_', '', $name)] = $time;
		}

		// Load the profiler view
		$view = new View('kohana_profiler', $data);
		
		// Return rendered view if $return is TRUE
		if ($return == TRUE)
			return $view->render();

		// Add profiler data to the output
		if (stripos(Kohana::$output, '</body>') !== FALSE)
		{
			// Closing body tag was found, insert the profiler data before it
			Kohana::$output = str_replace('</body>', $view.'</body>', Kohana::$output);
		}
		else
		{
			// Append the profiler data to the output
			Kohana::$output .= $view;
		}
	}

} // End Profiler Class