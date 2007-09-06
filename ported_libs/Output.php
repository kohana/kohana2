<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Output Class
 *
 * Responsible for sending final output to browser
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Output
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/libraries/output.html
 */
class Core_Output {

	var $final_output;
	var $cache_expiration = 0;
	var $headers          = array();
	var $enable_profiler  = FALSE;

	public function __construct()
	{
		Log::add('debug', 'Output Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Get Output
	 *
	 * Returns the current output string
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_output()
	{
		return $this->final_output;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Output
	 *
	 * Sets the output string
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function set_output($output)
	{
		$this->final_output = $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Header
	 *
	 * Lets you set a server header which will be outputted with the final display.
	 *
	 * Note:  If a file is cached, headers will not be sent.  We need to figure out
	 * how to permit header data to be saved with the cache data...
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function set_header($header)
	{
		$this->headers[] = $header;
	}

	// --------------------------------------------------------------------

	/**
	 * Enable/disable Profiler
	 *
	 * @access	public
	 * @param	bool
	 * @return	void
	 */
	public function enable_profiler($val = TRUE)
	{
		$this->enable_profiler = (bool) $val;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Cache
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */
	public function cache($time)
	{
		$this->cache_expiration = ( ! is_numeric($time)) ? 0 : $time;
	}

	// --------------------------------------------------------------------

	/**
	 * Display Output
	 *
	 * All "view" data is automatically put into this variable by the controller class:
	 *
	 * $this->final_output
	 *
	 * This function sends the finalized output data to the browser along
	 * with any server headers and profile data.  It also stops the
	 * benchmark timer so the page rendering speed and memory usage can be shown.
	 *
	 * @access	public
	 * @return	mixed
	 */
	public function _display($output = '')
	{
		// Note:  We use globals because we can't use $CORE = Kohana::$instance
		// since this function is sometimes called by the caching mechanism,
		// which happens before the Core super object is available.
		global $BM, $CFG;

		// --------------------------------------------------------------------

		// Set the output data
		if ($output == '')
		{
			$output = $this->final_output;
		}

		// --------------------------------------------------------------------

		// Do we need to write a cache file?
		if ($this->cache_expiration > 0)
		{
			$this->_write_cache($output);
		}

		// --------------------------------------------------------------------

		// Parse out the elapsed time and memory usage,
		// then swap the pseudo-variables with the data

		$elapsed = $BM->elapsed_time('total_execution_time_start', 'total_execution_time_end');
		$output = str_replace('{elapsed_time}', $elapsed, $output);

		$memory	 = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
		$output = str_replace('{memory_usage}', $memory, $output);

		// --------------------------------------------------------------------

		// Is compression requested?
		if ($CFG->item('compress_output') === TRUE)
		{
			if (extension_loaded('zlib'))
			{
				if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
				{
					ob_start('ob_gzhandler');
				}
			}
		}

		// --------------------------------------------------------------------

		// Are there any server headers to send?
		if (count($this->headers) > 0)
		{
			foreach ($this->headers as $header)
			{
				@header($header);
			}
		}

		// --------------------------------------------------------------------

		// Does the Kohana::$instance function exist?
		// If not we know we are dealing with a cache file so we'll
		// simply echo out the data and exit.
		if ( ! function_exists('get_instance'))
		{
			echo $output;
			Log::add('debug', 'Final output sent to browser');
			Log::add('debug', "Total execution time: ".$elapsed);
			return TRUE;
		}

		// --------------------------------------------------------------------

		// Grab the super object.  We'll need it in a moment...
		$CORE = Kohana::$instance;

		// Do we need to generate profile data?
		// If so, load the Profile class and run it.
		if ($this->enable_profiler == TRUE)
		{
			$CORE->load->library('profiler');

			// If the output data contains html,
			// we will insert the profile data right before </body>.
			if (strpos($output, '</body>') !== FALSE)
			{
				$output = str_replace('</body>', $CORE->profiler->run() .'</body>', $output);
			}
			else
			{
				$output .= $CORE->profiler->run();
			}
		}

		// --------------------------------------------------------------------

		// Does the controller contain a function named _output()?
		// If so send the output there.  Otherwise, echo it.
		if (method_exists($CORE, '_output'))
		{
			$CORE->_output($output);
		}
		else
		{
			echo $output;  // Send it to the browser!
		}

		Log::add('debug', 'Final output sent to browser');
		Log::add('debug', "Total execution time: ".$elapsed);
	}

	// --------------------------------------------------------------------

	/**
	 * Write a Cache File
	 *
	 * @access	public
	 * @return	void
	 */
	public function _write_cache($output)
	{
		$CORE = Kohana::$instance;
		$path = $CORE->config->item('cache_path');

		$cache_path = ($path == '') ? APPPATH.'cache/' : $path;

		if ( ! is_dir($cache_path) OR ! is_writable($cache_path))
		{
			return;
		}

		$uri =	$CORE->config->item('base_url').
				$CORE->config->item('index_page').
				$CORE->uri->uri_string();

		$cache_path .= md5($uri);

		if ( ! $fp = @fopen($cache_path, 'wb'))
		{
			Log::add('error', "Unable to write cache file: ".$cache_path);
			return;
		}

		$expire = time() + ($this->cache_expiration * 60);

		flock($fp, LOCK_EX);
		fwrite($fp, $expire.'TS--->'.$output);
		flock($fp, LOCK_UN);
		fclose($fp);
		@chmod($cache_path, 0777);

		Log::add('debug', "Cache file written: ".$cache_path);
	}

	// --------------------------------------------------------------------

	/**
	 * Update/serve a cached file
	 *
	 * @access	public
	 * @return	void
	 */
	public function _display_cache(&$CFG, &$RTR)
	{
		$CFG = load_class('Config');
		$RTR = load_class('Router');

		$cache_path = ($CFG->item('cache_path') == '') ? BASEPATH.'cache/' : $CFG->item('cache_path');

		if ( ! is_dir($cache_path) OR ! is_writable($cache_path))
		{
			return FALSE;
		}

		// Build the file path.  The file name is an MD5 hash of the full URI
		$uri =	$CFG->item('base_url').
				$CFG->item('index_page').
				$RTR->uri_string;

		$filepath = $cache_path.md5($uri);

		if ( ! @file_exists($filepath))
		{
			return FALSE;
		}

		if ( ! $fp = @fopen($filepath, 'rb'))
		{
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$cache = '';
		if (filesize($filepath) > 0)
		{
			$cache = fread($fp, filesize($filepath));
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		// Strip out the embedded timestamp
		if ( ! preg_match('/(\d+TS--->)/', $cache, $match))
		{
			return FALSE;
		}

		// Has the file expired? If so we'll delete it.
		if (time() >= trim(str_replace('TS--->', '', $match[1])))
		{
			@unlink($filepath);
			Log::add('debug', 'Cache file has expired. File deleted');
			return FALSE;
		}

		// Display the cache
		$this->_display(str_replace($match[0], '', $cache));
		Log::add('debug', 'Cache file is current. Sending it to browser.');
		return TRUE;
	}


}
// END Output Class
?>