<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * Profiler Class
 *
 * @category    Libraries
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/profiler.html
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
			'queries'    => Database::$benchmarks
		);

		// Clean unique id from system benchmark names
		foreach (Benchmark::get_all() as $name => $time)
		{
			$data['benchmarks'][str_replace(SYSTEM_BENCHMARK.'_', '', $name)] = $time;
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