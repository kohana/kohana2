<?php defined('SYSPATH') or die('No direct script access.');
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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * User Agent Class
 *
 * Identifies the platform, browser, robot, or mobile devise of the browsing agent
 *
 * @package     Kohana
 * @subpackage  Libraries
 * @category    User Agent
 * @author      Rick Ellis, Kohana Team
 * @link        http://kohanaphp.com/user_guide/en/libraries/user_agent.html
 */
class User_Agent_Core {

	public static $agent = NULL;

	protected static $referrer  = '';
	protected static $languages = array();
	protected static $charsets  = array();

	protected $platform = '';
	protected $browser  = '';
	protected $version  = '';
	protected $mobile   = '';
	protected $robot    = '';

	/**
	 * Constructor
	 *
	 * Loads user agent data
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
	{
		if (is_null(self::$agent) AND isset($_SERVER['HTTP_USER_AGENT']))
		{
			self::$agent = trim($_SERVER['HTTP_USER_AGENT']);
		}

		if (self::$agent == '')
		{
			Log::add('debug', 'Could not determine user agent type.');
			return FALSE;
		}

		// Set the user agent data
		foreach(Config::item('user_agents') as $type => $data)
		{
			if (isset($this->$type))
			{
				foreach($data as $agent => $name)
				{
					if (stripos(self::$agent, $agent) !== FALSE)
					{
						if ($type == 'browser' AND preg_match('|'.preg_quote($agent).'[^0-9.]*([0-9.]+)|i', self::$agent, $match))
						{
							$this->version = $match[1];
							unset($match);
						}
						$this->$type = $name;
						break;
					}
				}
			}
		}

		// Set the accepted languages
		if (empty(self::$languages) AND ! empty($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			self::$languages = explode(',', preg_replace('/;q=.+/i', '', trim($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			array_map('trim', self::$languages);
		}

		// Set the accepted charsets
		if (empty(self::$charsets) AND ! empty($_SERVER['HTTP_ACCEPT_CHARSET']))
		{
			self::$charsets = explode(',', preg_replace('/;q=.+/i', '', trim($_SERVER['HTTP_ACCEPT_CHARSET'])));
			array_map('trim', self::$languages);
		}

		// Set the referrer
		if (empty(self::$referrer) AND ! empty($_SERVER['HTTP_REFERER']))
		{
			self::$referrer = trim($_SERVER['HTTP_REFERER']);
		}

		Log::add('debug', 'User Agent Library initialized');
	}

	/**
	 * Fetch information about the user agent, examples:
	 *
	 *   is_browser, is_mobile, is_robot
	 *   agent, browser, mobile, version, referrer
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function __get($key)
	{
		if (empty($key))
		{
			return;
		}
		elseif (strpos('is_', $key) === 0)
		{
			$key = substr($key, 3);
			return isset($this->$key) ? (bool) $this->$key : FALSE;
		}
		elseif (isset($this->$key))
		{
			return $this->$key;
		}
		elseif (isset(self::$$key))
		{
			return self::$$key;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * So that users can use $user_agent->is_robot() or $user_agent->is_robot
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function __call($func, $args = FALSE)
	{
		return $this->__get($func);
	}

	/**
	 * Returns the full user agent string when the object is turned into a string.
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString()
	{
		return $this->browser;
	}

	/**
	 * Test for a particular language
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function accept_lang($lang = 'en')
	{
		if (empty($lang) OR ! is_string($lang))
			return FLASE;

		return (in_array(strtolower($lang), self::$languages));
	}

	/**
	 * Test for a particular character set
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function accept_charset($charset = 'utf-8')
	{
		if (empty($charset) OR ! is_string($charset))
			return FALSE;

		return (in_array(strtolower($charset), $this->charsets()));
	}

} // End User_Agent Class