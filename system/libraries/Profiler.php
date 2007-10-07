<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana: The small, swift, and secure PHP5 framework
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
 *
 * $Id$
 */

// ----------------------------------------------------------------------------

/**
 * Kohana Profiler Class
 *
 * This class enables you to display benchmark, query, and other data
 * in order to help with debugging and optimization.
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/general/profiling.html
 */
class Profiler_Core {

	public function __construct()
	{
		// Add profiler to page output automatically
		Event::add('system.output', array($this, 'render'));

		Log::add('debug', 'Profiler Library initialized');
	}

	/**
	 * Disables the profiler for this page only, best used when profiler is autoloaded
	 *
	 * @access public
	 * @return void
	 */
	public function disable()
	{
		// Removes itself from the event queue
		Event::clear('system.output', array($this, 'render'));
	}

	/**
	 * Run the Profiler and add to the bottom of the page
	 *
	 * @access public
	 * @return void
	 */
	public function render()
	{
		$data = array
		(
			'benchmarks' => array(),
			'queries'    => FALSE
		);

		// Clean unique id from system benchmark names
		foreach (Benchmark::get_all() as $name => $time)
		{
			$data['benchmarks'][str_replace(SYSTEM_BENCHMARK.'_', '', $name)] = $time;
		}

		// Get database queries
		if (isset(Kohana::instance()->db))
		{
			$data['queries'] = Database::$benchmarks;
		}

		// Load the profiler view
		$view = new View('kohana_profiler', $data);

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