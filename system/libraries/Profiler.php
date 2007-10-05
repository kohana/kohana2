<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana: The small, swift, and secure PHP5 framework
 *
 * $Id$
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/license.html
 * @since            Version 2.0
 * @filesource
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

class Profiler_Core
{

	private $core;

 	public function __construct()
 	{
 		// Add profiler to page output automatically
 		Event::add('system.output', array($this, 'render'));

 		$this->core = Kohana::instance();
 		
 		Log::add('debug', 'Profiler initialized');
 	}

	/**
	 * Run the Profiler and add to the bottom of the page
	 *
	 * @access public
	 * @return void
	 */
	public function render()
	{
		// Get list of benchmarks
		$benchmarks = Benchmark::get_all();

		// Clean unique id from system benchmark names
		$renamed_bm = array();
		foreach ($benchmarks as $name => $time)
		{
			$name              = str_replace(SYSTEM_BENCHMARK.'_', '', $name);
			$renamed_bm[$name] = $time;
		}

		$data['benchmarks'] = $renamed_bm;

		// Get database queries
		$data['db_exists'] = FALSE;
		$data['queries']   = array();
		if (isset($this->core->db))
		{
			$data['db_exists'] = TRUE;
			$data['queries']   = $this->core->db->benchmark;
		}

		// Get profiler view ready to add to output
		$view = new View('kohana_profiler', $data);

		// Get page output
		$output = Kohana::$output;

		if (preg_match("|</body>.*?</html>|is", $output))
		{
			// Output has closing body and html tags, put profiler before them
			$output  = preg_replace("|</body>.*?</html>|is", '', $output);
			$output .= $view;
			$output .= '</body></html>';
		}
		else
		{
			// No closing tags, just add profiler to the end
			$output .= $view;
		}

		// Set new page output
		Kohana::$output = $output;
	}

} // End Profiler Class