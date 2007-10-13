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
 * User Agent Class
 *
 * @category    Libraries
 * @author      Rick Ellis, Kohana Team
 * @copyright   Copyright (c) 2006, EllisLab, Inc.
 * @license     http://www.codeigniter.com/user_guide/license.html
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
			return FALSE;

		return in_array(strtolower($lang), self::$languages);
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

		return in_array(strtolower($charset), $this->charsets());
	}

} // End User_Agent Class