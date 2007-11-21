<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: User_Agent
 *
 * Kohana Source Code:
 *  author    - Rick Ellis, Kohana Team
 *  copyright - Copyright (c) 2006, EllisLab, Inc.
 *  license   - <http://www.codeigniter.com/user_guide/license.html>
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
	 * Constructor: __construct
	 *  Loads user agent data.
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
	 * Method: __get
	 *  Fetch information about the user agent, examples:
	 *
	 *  is_browser, is_mobile, is_robot
	 *  agent, browser, mobile, version, referrer
	 *
	 * Parameters:
	 *  key - key name
	 *
	 * Returns:
	 *   Key value
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
	 * Method: __call
	 *  So that users can use $user_agent->is_robot() or $user_agent->is_robot.
	 *
	 * Parameters:
	 *  func - function name
	 *  args - function arguments
	 *
	 * Returns:
	 *   Function return value
	 */
	public function __call($func, $args = FALSE)
	{
		return $this->__get($func);
	}

	/**
	 * Method: __toString
	 *  Returns the full user agent string when the object is turned into a string.
	 *
	 * Returns:
	 *   User agent string
	 */
	public function __toString()
	{
		return self::$agent;
	}

	/**
	 * Method: accept_lang
	 *  Test for a particular language.
	 *
	 * Parameters:
	 *  lang - language to test for
	 *
	 * Returns:
	 *   TRUE or FALSE
	 */
	public function accept_lang($lang = 'en')
	{
		if (empty($lang) OR ! is_string($lang))
			return FALSE;

		return in_array(strtolower($lang), self::$languages);
	}

	/**
	 * Method: accept_charset
	 *  Test for a particular character set.
	 *
	 * Parameters:
	 *  charset - character set to test for
	 *
	 * Returns:
	 *   TRUE or FALSE
	 */
	public function accept_charset($charset = 'utf-8')
	{
		if (empty($charset) OR ! is_string($charset))
			return FALSE;

		return in_array(strtolower($charset), $this->charsets());
	}

} // End User_Agent Class