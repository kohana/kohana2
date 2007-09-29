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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * User Agent Class
 *
 * Identifies the platform, browser, robot, or mobile devise of the browsing agent
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	User Agent
 * @author		Rick Ellis, Kohana Team
 * @link		http://kohanaphp.com/user_guide/libraries/user_agent.html
 */
class User_Agent_Core {

	private $agent		= NULL;

	private $is_browser	= FALSE;
	private $is_robot	= FALSE;
	private $is_mobile	= FALSE;

	private $languages	= array();
	private $charsets	= array();

	private $platforms	= array();
	private $browsers	= array();
	private $mobiles	= array();
	private $robots		= array();

	private $platform	= '';
	private $browser	= '';
	private $version	= '';
	private $mobile		= '';
	private $robot		= '';


	/**
	 * Constructor
	 *
	 * Sets the User Agent and runs the compilation routine
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
		}

		if ( ! is_null($this->agent))
		{
			if ($this->load_agent_file())
			{
				$this->compile_data();
			}
		}

		Log::add('debug', 'Table Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 *
	 * @access	private
	 * @return	bool
	 */
	private function load_agent_file()
	{
		$config = Config::item('user_agents');
		
		if (empty($config))
			return FALSE;


		$return = FALSE;

		if (isset($config['platforms']))
		{
			$this->platforms = $config['platforms'];
			unset($config['platforms']);
			$return = TRUE;
		}

		if (isset($config['browsers']))
		{
			$this->browsers = $config['browsers'];
			unset($config['browsers']);
			$return = TRUE;
		}

		if (isset($config['browsers']))
		{
			$this->mobiles = $config['mobiles'];
			unset($config['mobiles']);
			$return = TRUE;
		}

		if (isset($config['robots']))
		{
			$this->robots = $config['robots'];
			unset($config['robots']);
			$return = TRUE;
		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Compile the User Agent Data
	 *
	 * @access	private
	 * @return	bool
	 */
	private function compile_data()
	{
		$this->set_platform();

		foreach (array('set_browser', 'set_robot', 'set_mobile') as $function)
		{
			if ($this->$function() === TRUE)
			{
				break;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Platform
	 *
	 * @access	private
	 * @return	mixed
	 */
	private function set_platform()
	{
		if (is_array($this->platforms) AND count($this->platforms) > 0)
		{
			foreach ($this->platforms as $key => $val)
			{
				if (preg_match('|'.preg_quote($key).'|i', $this->agent))
				{
					$this->platform = $val;
					return TRUE;
				}
			}
		}
		$this->platform = 'Unknown Platform';
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Browser
	 *
	 * @access	private
	 * @return	bool
	 */
	private function set_browser()
	{
		if (is_array($this->browsers) AND count($this->browsers) > 0)
		{
			foreach ($this->browsers as $key => $val)
			{
				if (preg_match('|'.preg_quote($key).'.*?([0-9.]+)|i', $this->agent, $match))
				{
					$this->is_browser = TRUE;
					$this->version = $match[1];
					$this->browser = $val;
					$this->set_mobile();
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @access	private
	 * @return	bool
	 */
	private function set_robot()
	{
		if (is_array($this->robots) AND count($this->robots) > 0)
		{
			foreach ($this->robots as $key => $val)
			{
				if (preg_match('|'.preg_quote($key).'|i', $this->agent))
				{
					$this->is_robot = TRUE;
					$this->robot = $val;
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @access	private
	 * @return	bool
	 */
	private function set_mobile()
	{
		if (is_array($this->mobiles) AND count($this->mobiles) > 0)
		{
			foreach ($this->mobiles as $key => $val)
			{
				if ((strpos(strtolower($this->agent), $key)) !== FALSE)
				{
					$this->is_mobile = TRUE;
					$this->mobile = $val;
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the accepted languages
	 *
	 * @access	private
	 * @return	void
	 */
	private function set_languages()
	{
		if ((count($this->languages) == 0) AND isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '')
		{
			$languages = preg_replace('/(;q=.+)/i', '', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));

			$this->languages = explode(',', $languages);
		}

		if (count($this->languages) == 0)
		{
			$this->languages = array('Undefined');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the accepted character sets
	 *
	 * @access	private
	 * @return	void
	 */
	private function set_charsets()
	{
		if ((count($this->charsets) == 0) AND isset($_SERVER['HTTP_ACCEPT_CHARSET']) AND $_SERVER['HTTP_ACCEPT_CHARSET'] != '')
		{
			$charsets = preg_replace('/(;q=.+)/i', '', trim($_SERVER['HTTP_ACCEPT_CHARSET']));

			$this->charsets = explode(',', $charsets);
		}

		if (count($this->charsets) == 0)
		{
			$this->charsets = array('Undefined');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Is Browser
	 *
	 * @access	public
	 * @return	bool
	 */
	public function is_browser()
	{
		return $this->is_browser;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Robot
	 *
	 * @access	public
	 * @return	bool
	 */
	public function is_robot()
	{
		return $this->is_robot;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Mobile
	 *
	 * @access	public
	 * @return	bool
	 */
	public function is_mobile()
	{
		return $this->is_mobile;
	}

	// --------------------------------------------------------------------

	/**
	 * Is this a referral from another site?
	 *
	 * @access	public
	 * @return	bool
	 */
	public function is_referral()
	{
		return ( ! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == '') ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Agent String
	 *
	 * @access	public
	 * @return	string
	 */
	public function agent_string()
	{
		return $this->agent;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * @access	public
	 * @return	string
	 */
	public function platform()
	{
		return $this->platform;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Browser Name
	 *
	 * @access	public
	 * @return	string
	 */
	public function browser()
	{
		return $this->browser;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 *
	 * @access	public
	 * @return	string
	 */
	public function version()
	{
		return $this->version;
	}

	// --------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 *
	 * @access	public
	 * @return	string
	 */
	public function robot()
	{
		return $this->robot;
	}
	// --------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 *
	 * @access	public
	 * @return	string
	 */
	public function mobile()
	{
		return $this->mobile;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the referrer
	 *
	 * @access	public
	 * @return	bool
	 */
	function referrer()
	{
		return ( ! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == '') ? '' : trim($_SERVER['HTTP_REFERER']);
	}

	// --------------------------------------------------------------------

	/**
	 * Get the accepted languages
	 *
	 * @access	public
	 * @return	array
	 */
	public function languages()
	{
		if (count($this->languages) == 0)
		{
			$this->set_languages();
		}

		return $this->languages;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the accepted Character Sets
	 *
	 * @access	public
	 * @return	array
	 */
	public function charsets()
	{
		if (count($this->charsets) == 0)
		{
			$this->set_charsets();
		}

		return $this->charsets;
	}

	// --------------------------------------------------------------------

	/**
	 * Test for a particular language
	 *
	 * @access	public
	 * @return	bool
	 */
	public function accept_lang($lang = 'en')
	{
		return (in_array(strtolower($lang), $this->languages(), TRUE)) ? TRUE : FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Test for a particular character set
	 *
	 * @access	public
	 * @return	bool
	 */
	public function accept_charset($charset = 'utf-8')
	{
		return (in_array(strtolower($charset), $this->charsets(), TRUE)) ? TRUE : FALSE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Returns the full user agent string when the object is turned into a string.
	 *
	 * @access public
	 * @return string
	 */
	public function __toString() {
		return $this->agent;
	}
	
} //End User_agent class