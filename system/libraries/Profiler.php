<?php

namespace Library;

defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Adds useful information to the bottom of the current page for debugging and optimization purposes.
 *
 * Benchmarks   - The times and memory usage of benchmarks run by the Benchmark library.
 * Database     - The raw SQL and number of affected rows of Database queries.
 * Session Data - Data stored in the current session if using the Session library.
 * POST Data    - The name and values of any POST data submitted to the current page.
 * Cookie Data  - All cookies sent for the current request.
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Profiler {

	protected static $profiles = array();
	protected static $show;

	/**
	 * Enable the profiler.
	 *
	 * @return  void
	 */
	public static function enable()
	{
		// Add all built in profiles to event
		\Kernel\Event::add('profiler.run', array('Profiler', 'benchmarks'));
		\Kernel\Event::add('profiler.run', array('Profiler', 'database'));
		\Kernel\Event::add('profiler.run', array('Profiler', 'session'));
		\Kernel\Event::add('profiler.run', array('Profiler', 'post'));
		\Kernel\Event::add('profiler.run', array('Profiler', 'cookies'));

		// Add profiler to page output automatically
		\Kernel\Event::add('system.display', array('Profiler', 'render'));

		Kohana_Log::add('debug', 'Profiler library enabled');

	}

	/**
	 * Disables the profiler for this page only.
	 * Best used when profiler is autoloaded.
	 *
	 * @return  void
	 */
	public static function disable()
	{
		// Removes itself from the event queue
		\Kernel\Event::clear('system.display', array('Profiler', 'render'));
	}

	/**
	 * Return whether a profile should be shown.
	 * Determined by the config setting or GET parameter.
	 *
	 * @param   string  profile name
	 * @return  boolean
	 */
	public static function show($name)
	{
		return (Profiler::$show === TRUE OR (is_array(Profiler::$show) AND in_array($name, Profiler::$show))) ? TRUE : FALSE;
	}

	/**
	 * Add a new profile.
	 *
	 * @param   object   profile object
	 * @return  boolean
	 * @throws  Kohana_Exception
	 */
	public static function add($profile)
	{
		if (is_object($profile))
		{
			Profiler::$profiles[] = $profile;
			return TRUE;
		}

		throw new \Kernel\Kohana_Exception('The profile must be an object');
	}

	/**
	 * Render the profiler.
	 *
	 * @param   boolean  return the output instead of adding it to bottom of page
	 * @return  void|string
	 */
	public static function render($return = FALSE)
	{
		$start = microtime(TRUE);

		// Determine the profiles that should be shown
		$get = isset($_GET['profiler']) ? explode(',', $_GET['profiler']) : array();
		Profiler::$show = empty($get) ? \Kernel\Kohana::config('profiler.show') : $get;

		\Kernel\Event::run('profiler.run');

		// Don't display if there's no profiles
		if (empty(Profiler::$profiles))
			return \Kernel\Kohana::$output;

		$styles = '';
		foreach (Profiler::$profiles as $profile)
		{
			$styles .= $profile->styles();
		}

		// Load the profiler view
		$data = array
		(
			'profiles'       => Profiler::$profiles,
			'styles'         => $styles,
			'execution_time' => microtime(TRUE) - $start
		);
		$view = new View('profiler/profiler', $data);

		// Return rendered view if $return is TRUE
		if ($return === TRUE)
			return $view->render();

		// Add profiler data to the output
		if (stripos(\Kernel\Kohana::$output, '</body>') !== FALSE)
		{
			// Closing body tag was found, insert the profiler data before it
			\Kernel\Kohana::$output = str_ireplace('</body>', $view->render().'</body>', \Kernel\Kohana::$output);
		}
		else
		{
			// Append the profiler data to the output
			\Kernel\Kohana::$output .= $view->render();
		}
	}

	/**
	 * Benchmark times and memory usage from the Benchmark library.
	 *
	 * @return  void
	 */
	public static function benchmarks()
	{
		if ( ! Profiler::show('benchmarks'))
			return;

		$table = new \Library\Profiler_Table();
		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array(__('Benchmarks'), __('Count'), __('Time'), __('Memory')), 'kp-title', 'background-color: #FFE0E0');

		$benchmarks = \Kernel\Benchmark::get(TRUE);

		// Moves the first benchmark (total execution time) to the end of the array
		$benchmarks = array_slice($benchmarks, 1) + array_slice($benchmarks, 0, 1);

		\Helper\text::alternate();
		foreach ($benchmarks as $name => $benchmark)
		{
			// Clean unique id from system benchmark names
			$name = ucwords(str_replace(array('_', '-'), ' ', str_replace(SYSTEM_BENCHMARK.'_', '', $name)));

			$data = array(__($name), $benchmark['count'], number_format($benchmark['time'], \Kernel\Kohana::config('profiler.time_decimals')), number_format($benchmark['memory'] / 1024 / 1024, \Kernel\Kohana::config('profiler.memory_decimals')).'MB');
			$class = \Helper\text::alternate('', 'kp-altrow');

			if ($name == 'Total Execution')
			{
				// Clear the count column
				$data[1] = '';
				$class = 'kp-totalrow';
			}

			$table->add_row($data, $class);
		}

		Profiler::add($table);
	}

	/**
	 * Database query benchmarks.
	 *
	 * @return  void
	 */
	public static function database()
	{
		if ( ! Profiler::show('database'))
			return;

		$queries = \Library\Database::$benchmarks;

		// Don't show if there are no queries
		if (empty($queries)) return;

		$table = new \Library\Profiler_Table();
		$table->add_column();
		$table->add_column('kp-column kp-data');
		$table->add_column('kp-column kp-data');
		$table->add_row(array(__('Queries'), __('Time'), __('Rows')), 'kp-title', 'background-color: #E0FFE0');

		\Helper\text::alternate();
		$total_time = $total_rows = 0;
		foreach ($queries as $query)
		{
			$data = array($query['query'], number_format($query['time'], \Kernel\Kohana::config('profiler.time_decimals')), $query['rows']);
			$class = \Helper\text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
			$total_time += $query['time'];
			$total_rows += $query['rows'];
		}

		$data = array(__('Total: ') . count($queries), number_format($total_time, \Kernel\Kohana::config('profiler.time_decimals')), $total_rows);
		$table->add_row($data, 'kp-totalrow');

		Profiler::add($table);
	}

	/**
	 * Session data.
	 *
	 * @return  void
	 */
	public static function session()
	{
		if (empty($_SESSION)) return;

		if ( ! Profiler::show('session'))
			return;

		$table = new \Library\Profiler_Table();
		$table->add_column('kp-name');
		$table->add_column();
		$table->add_row(array(__('Session'), __('Value')), 'kp-title', 'background-color: #CCE8FB');

		\Helper\text::alternate();
		foreach($_SESSION as $name => $value)
		{
			if (is_object($value))
			{
				$value = get_class($value).' [object]';
			}

			$data = array($name, $value);
			$class = \Helper\text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}

		Profiler::add($table);
	}

	/**
	 * POST data.
	 *
	 * @return  void
	 */
	public static function post()
	{
		if (empty($_POST)) return;

		if ( ! Profiler::show('post'))
			return;

		$table = new \Library\Profiler_Table();
		$table->add_column('kp-name');
		$table->add_column();
		$table->add_row(array(__('POST'), __('Value')), 'kp-title', 'background-color: #E0E0FF');

		\Helper\text::alternate();
		foreach($_POST as $name => $value)
		{
			$data = array($name, $value);
			$class = \Helper\text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}

		Profiler::add($table);
	}

	/**
	 * Cookie data.
	 *
	 * @return  void
	 */
	public static function cookies()
	{
		if (empty($_COOKIE)) return;

		if ( ! Profiler::show('cookies'))
			return;

		$table = new \Library\Profiler_Table();
		$table->add_column('kp-name');
		$table->add_column();
		$table->add_row(array(__('Cookies'), __('Value')), 'kp-title', 'background-color: #FFF4D7');

		\Helper\text::alternate();
		foreach($_COOKIE as $name => $value)
		{
			$data = array($name, $value);
			$class = \Helper\text::alternate('', 'kp-altrow');
			$table->add_row($data, $class);
		}

		Profiler::add($table);
	}
}
