<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The URI library provides convenience methods for
 * handling, manipulating, and creating  URI segments and strings for
 * Kohana's routing mechanisms.
 *
 * If you are looking for functions that return the base url
 * for your site, retrieve the current url, or handle page redirection
 * you are looking for the url helper.
 *
 * @link http://www.iitechs.com/kohana/userguide/api/url
 *
 * [!!] This library is no longer automatically loaded by Kohana, you must do it manually using the following recommendations.
 *
 * [!!] Note that this library works with the URI that comes *after* the `index.php`!
 *
 * ##### Loading the URI library
 *
 *     // This is the idiomatic way of loading the URI library
 *     $uri = URI::instance();
 *
 *     // Using it is simple (assuming this url: http://localhost/kohana/welcome/index)
 *     echo Kohana::debug($uri->segment(2));
 *
 *     // Output:
 *     (string) index
 *
 * $Id$
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class URI_Core extends Router {

	/**
	 * Returns a singleton instance of the URI library, allowing you to chain
	 * methods off of its instance.
	 *
	 * Note: it is *preferred* to use `URI::instance();` over `new URI;`
	 * because it properly sets up a singleton instance of the library.
	 *
	 * ###### Example
	 *
	 *     $uri = URI::instance();
	 *
	 *     // URI object is returned:
	 *     (object) URI Object
	 *     (
	 *     )
	 *
	 * @return  URI
	 */
	public static function instance()
	{
		static $instance;

		if ($instance == NULL)
		{
			// Initialize the URI instance
			$instance = new URI;
		}

		return $instance;
	}

	/**
	 * Retrieve a specific URI segment by its index. The second
	 * function argument provides a default value if the segment index
	 * cannot be found or is not valid.
	 *
	 * If the second function argument is not provided, `(boolean)
	 * false` will be returned if the given or assumed index is
	 * invalid or cannot be found.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->segment(2));
	 *
	 *     // Output:
	 *     (string) index
	 *
	 *     // Using the default value assuming this url: http://localhost/kohana
	 *     echo Kohana::debug($uri->segment(1, 'default_value'));
	 *
	 *     // Output:
	 *     (string) default_value
	 *
	 * @param   mixed  $index   Segment index or label
	 * @param   mixed  $default Default value returned if segment does not exist
	 * @return  string
	 */
	public function segment($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, URI::$segments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(URI::$segments[$index]) ? URI::$segments[$index] : $default;
	}

	/**
	 * Retrieve a specific routed URI segment by its index. The second
	 * function argument provides a default value if the routed segment index
	 * cannot be found or is not valid.
	 *
	 * If the second function argument is not provided, `(boolean)
	 * false` will be returned if the given or assumed index is
	 * invalid or cannot be found.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana
	 *     echo Kohana::debug($uri->rsegment(1));
	 *
	 *     // Output:
	 *     (string) welcome
	 *
	 *     // Using the default value assuming this url: http://localhost/kohana
	 *     echo Kohana::debug($uri->rsegment(3, 'default_value'));
	 *
	 *     // Output:
	 *     (string) default_value
	 *
	 * @param   mixed  $index   Rsegment number or label
	 * @param   mixed  $default Default value returned if segment does not exist
	 * @return  string
	 */
	public function rsegment($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, URI::$rsegments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(URI::$rsegments[$index]) ? URI::$rsegments[$index] : $default;
	}

	/**
	 * Retrieve a specific URI argument. The second function argument 
	 * provides a default value if the routed segment index cannot be 
	 * found or is not valid.
	 *
	 * If the second function argument is not provided, `(boolean)
	 * false` will be returned if the given or assumed index is
	 * invalid or cannot be found.
	 *
	 * *Arguments* are the portion of the URI that succeed the
     * controller/method in the URI.
	 *
	 * @see http://url.to.routing (don't have URL yet as docs aren't published)
	 * 
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index/arg1
	 *     echo Kohana::debug($uri->argument(1));
	 *
	 *     // Output:
	 *     (string) arg1
	 *
	 *     // Using the default value assuming this url: http://localhost/kohana/welcome/index/arg1
	 *     echo Kohana::debug($uri->argument(2, 'default_arg'));
	 *
	 *     // Output:
	 *     (string) default_arg
	 *
	 * @param   mixed  $index   Argument index or label
	 * @param   mixed  $default Default value returned if segment does not exist
	 * @return  string
	 */
	public function argument($index = 1, $default = FALSE)
	{
		if (is_string($index))
		{
			if (($key = array_search($index, URI::$arguments)) === FALSE)
				return $default;

			$index = $key + 2;
		}

		$index = (int) $index - 1;

		return isset(URI::$arguments[$index]) ? URI::$arguments[$index] : $default;
	}

	/**
	 * Returns an array containing all the URI segments with an offset
	 * of zero by default. Providing any value other than zero for the
	 * first function argument will offset the starting index for the
	 * current URI.
	 *
	 * The second function argument is a boolean parameter and toggles
	 * whether the returned array is associative or not.
	 * 
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->segment_array());
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [1] => welcome
	 *         [2] => index
	 *     )
	 *
	 *     // Setting the second function argument to **TRUE**
	 *     echo Kohana::debug($uri->segment_array(0, TRUE));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [welcome] => index
	 *     )
	 *
	 * @param   integer  $offset      Segment offset
	 * @param   boolean  $associative Return an associative array
	 * @return  array
	 */
	public function segment_array($offset = 0, $associative = FALSE)
	{
		return $this->build_array(URI::$segments, $offset, $associative);
	}

	/**
	 * Returns an array containing all the re-routed URI segments with an offset
	 * of zero by default. Providing any value other than zero for the
	 * first function argument will offset the starting index for the
	 * current URI.
	 *
	 * The second function argument is a boolean parameter and toggles
	 * whether the returned array is associative or not.
	 *
	 * This method takes the remapped routes from the `routes.php`
	 * configuration file to produce resulting array of segments.
	 *
	 * @link link to the routes config
	 *
	 * ###### Example Route
	 *     $config['ohai'] = 'welcome/index'; 
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/ohai
	 *     echo Kohana::debug($uri->rsegment_array());
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [1] => welcome
	 *         [2] => index
	 *     )
	 *
	 *     // Setting the second function argument to **TRUE**
	 *     echo Kohana::debug($uri->rsegment_array(0, TRUE));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [welcome] => index
	 *     )
	 *
	 * @param   integer  $offset      Rsegment offset
	 * @param   boolean  $associative Return an associative array
	 * @return  array
	 */
	public function rsegment_array($offset = 0, $associative = FALSE)
	{
		return $this->build_array(URI::$rsegments, $offset, $associative);
	}

	/**
	 * Returns an array containing all the URI arguments with an offset
	 * of zero by default. Providing any value other than zero for the
	 * first function argument will offset the starting index for the
	 * current URI.
	 *
	 * The second function argument is a boolean parameter and toggles
	 * whether the returned array is associative or not.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index/arg1/arg2
	 *     echo Kohana::debug($uri->argument_array());
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [1] => arg1
	 *         [2] => arg2
	 *     )
	 *
	 *     // Setting the second function argument to **TRUE**
	 *     echo Kohana::debug($uri->argument_array(0, TRUE));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [arg1] => arg2
	 *     )
	 *
	 * @param   integer  $offset      Segment offset
	 * @param   boolean  $associative Return an associative array
	 * @return  array
	 */
	public function argument_array($offset = 0, $associative = FALSE)
	{
		return $this->build_array(URI::$arguments, $offset, $associative);
	}

	/**
	 * Creates a naturally indexed or associative array from an array and
	 * offset. The third function argument toggles whether the array
	 * created is associative.
	 *
	 * This method is primarly used as a helper for `(r)segment_array() and `argument_array()`.
	 *
	 * ###### Example
	 *
	 *     $arr = array('snake', 'child', 'hammer', '...');
	 *
	 *     echo Kohana::debug($uri->build_array($arr);
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [1] => snake
	 *         [2] => child
	 *         [3] => hammer
	 *         [4] => ...
	 *     )
	 *
	 *     // Setting the second function argument to **TRUE**
	 *     echo Kohana::debug($uri->build_arr($arr, 0, TRUE));
	 *
	 *     // Output:
	 *     (array) Array
	 *     (
	 *         [snake] => child
	 *         [hammer] => ...
	 *     )
	 *
	 * @param   array    array to rebuild
	 * @param   integer  offset to start from
	 * @param   boolean  create an associative array
	 * @return  array
	 */
	public function build_array($array, $offset = 0, $associative = FALSE)
	{
		// Prevent the keys from being improperly indexed
		array_unshift($array, 0);

		// Slice the array, preserving the keys
		$array = array_slice($array, $offset + 1, count($array) - 1, TRUE);

		if ($associative === FALSE)
			return $array;

		$associative = array();
		$pairs       = array_chunk($array, 2);

		foreach ($pairs as $pair)
		{
			// Add the key/value pair to the associative array
			$associative[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
		}

		return $associative;
	}

	/**
	 * Returns the complete URI as a string.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->string();
	 *
	 *     // Output:
	 *     (string) welcome/index
	 *
	 * @return  string
	 */
	public function string()
	{
		return URI::$current_uri;
	}

	/**
	 * Magic method for converting an object to a string.
	 *
	 * [!!] This is a PHP magic method and converts an object passed as an argument to `echo`, `print()`, or `die()` to a string.
	 *
	 * @link http://www.php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
	 * 
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo $uri; // Or URI::instance();
	 *
	 *     // Output:
	 *     welcome/index
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return URI::$current_uri;
	}

	/**
	 * Returns the total number of URI segments.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->total_segments();
	 *
	 *     // Output:
	 *     (integer) 2
	 *
	 * @return  integer
	 */
	public function total_segments()
	{
		return count(URI::$segments);
	}

	/**
	 * Returns the total number of re-routed URI segments.
	 *
	 * ###### Example Route
	 *     $config['ohai'] = 'welcome/index';
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/ohai
	 *     echo Kohana::debug($uri->total_rsegments());
	 *
	 *     // Output:
	 *     (integer) 2
	 *
	 * @return  integer
	 */
	public function total_rsegments()
	{
		return count(URI::$rsegments);
	}

	/**
	 * Returns the total number of URI arguments succeeding a
	 * controller/method segment pair.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index/arg1/arg2
	 *     echo Kohana::debug($uri->total_arguments());
	 *
	 *     // Output:
	 *     (integer) 2
	 *
	 * @return  integer
	 */
	public function total_arguments()
	{
		return count(URI::$arguments);
	}

	/**
	 * Returns the last URI segment. The second
	 * function argument provides a default value if there is no valid
	 * end segment.
	 *
	 * If the second function argument is not provided, `(boolean)
	 * false` will be returned.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->last_segment());
	 *
	 *     // Output:
	 *     (string) index
	 *
	 *     // With a default value provided, assuming this url: http://localhost/kohana
	 *     echo Kohana::debug($uri->last_segment('default_value'));
	 *
	 *     // Output:
	 *     (string) default_value
	 *
	 * @param   mixed   $default Default value returned if segment does not exist
	 * @return  string
	 */
	public function last_segment($default = FALSE)
	{
		if (($end = $this->total_segments()) < 1)
			return $default;

		return URI::$segments[$end - 1];
	}

	/**
	 * Returns the last re-routed URI segment. The second
	 * function argument provides a default value if there is no valid
	 * end routed segment.
	 *
	 * If the second function argument is not provided, `(boolean)
	 * false` will be returned.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/ohai
	 *     echo Kohana::debug($uri->last_rsegment());
	 *
	 *     // Output:
	 *     (string) welcome
	 *
	 *     // With a default value provided, assuming this url: http://localhost/kohana
	 *     echo Kohana::debug($uri->last_rsegment('default_value'));
	 *
	 *     // Output:
	 *     (string) default_value
	 *
	 * @param   mixed   $default Default value returned if segment does not exist
	 * @return  string
	 */
	public function last_rsegment($default = FALSE)
	{
		if (($end = $this->total_segments()) < 1)
			return $default;

		return URI::$rsegments[$end - 1];
	}

	/**
	 * Returns the path to the current controller (not including the actual
	 * controller), as a web path.
	 *
	 * The second function argument toggles whether a full url path to
	 * the controller will be returned.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome
	 *     echo Kohana::debug($uri->controller_path());
	 *
	 *     // Output:
	 *     (string) /kohana/index.php/Users/ixmatus/Localhost/kohana/application/controllers/welcome.php
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome
	 *     echo Kohana::debug($uri->controller_path(FALSE));
	 *
	 *     // Output:
	 *     (string) /Users/ixmatus/Localhost/kohana/application/controllers/welcome.php
	 *
	 * @param   boolean  $full Return a full url, or only the path specifically
	 * @return  string
	 */
	public function controller_path($full = TRUE)
	{
		return ($full) ? url::site(URI::$controller_path) : URI::$controller_path;
	}

	/**
	 * Returns the current controller, as a web path.
	 *
	 * The second function argument toggles whether a full url path to
	 * the controller will be returned.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome
	 *     echo Kohana::debug($uri->controller());
	 *
	 *     // Output:
	 *     (string) /kohana/index.php/Users/ixmatus/Localhost/opensource/kohana/trunk/application/controllers/welcome.phpwelcome
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome
	 *     echo Kohana::debug($uri->controller(FALSE));
	 *
	 *     // Output:
	 *     (string) welcome
	 *
	 * @param   boolean  $full Return a full url, or only the controller specifically
	 * @return  string
	 */
	public function controller($full = TRUE)
	{
		return ($full) ? url::site(URI::$controller_path.URI::$controller) : URI::$controller;
	}

	/**
	 * Returns the current method succeeding the current segment
	 * controller, as a web path or optionally as a segment path.
	 *
	 * The second function argument toggles whether a full url path to
	 * the controller and method will be returned.
	 *
	 * ###### Example
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->method());
	 *
	 *     // Output:
	 *     (string) /kohana/index.php/Users/ixmatus/Localhost/opensource/kohana/trunk/application/controllers/welcome.phpwelcome/index
	 *
	 *     // Assuming this url: http://localhost/kohana/welcome/index
	 *     echo Kohana::debug($uri->method(FALSE));
	 *
	 *     // Output:
	 *     (string) index
	 *
	 * @param   boolean  $full Return a full url, or only the method specifically
	 * @return  string
	 */
	public function method($full = TRUE)
	{
		return ($full) ? url::site(URI::$controller_path.URI::$controller.'/'.URI::$method) : URI::$method;
	}

} // End URI Class
