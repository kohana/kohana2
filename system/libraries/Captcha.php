<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Captcha library.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Captcha_Core {

	// Style-dependent Captcha driver
	protected $driver;

	// Config values
	public static $config = array
	(
		'style'      => 'basic',
		'width'      => 150,
		'height'     => 50,
		'complexity' => 4,
		'background' => '',
		'font'       => '',
	);

	// The Captcha challenge answer, the text the user is supposed to enter
	public static $answer;

	/**
	 * Constructs a new Captcha object.
	 *
	 * @throws  Kohana_Exception
	 * @param   string  configuration settings
	 * @return  void
	 */
	public function __construct($config = array())
	{
		static $gd2_check;

		// We need GD2 exclusively
		if ($gd2_check === NULL AND ($gd2_check = function_exists('imagegd2')) === FALSE)
			throw new Kohana_Exception('captcha.requires_GD2');

		// No custom config group name given
		if ( ! isset($config['group']))
		{
			$config['group'] = 'default';
		}

		// Load and validate config group
		if ( ! is_array($group_config = Config::item('captcha.'.$config['group'])))
			throw new Kohana_Exception('captcha.undefined_group', $config['group']);

		// All captcha config groups inherit default config group
		if ($config['group'] !== 'default')
		{
			// Load and validate default config group
			if ( ! is_array($default_config = Config::item('captcha.default')))
				throw new Kohana_Exception('captcha.undefined_group', 'default');

			// Merge config group with default config group
			$group_config += $default_config;
		}

		// Merge custom config items with config group
		$config += $group_config;

		// Assign config values to the object
		foreach ($config as $key => $value)
		{
			if (array_key_exists($key, self::$config))
			{
				self::$config[$key] = $value;
			}
		}

		// If using a background image, check if it exists
		if ( ! empty($config['background_file']))
		{
			self::$config['background'] = str_replace('\\', '/', realpath($config['background_path'])).'/'.$config['background_file'];

			if ( ! file_exists(self::$config['background']))
				throw new Kohana_Exception('captcha.file_not_found', self::$config['background']);
		}

		// If using a font, check if it exists
		if ( ! empty($config['font_file']))
		{
			self::$config['font'] = str_replace('\\', '/', realpath($config['font_path'])).'/'.$config['font_file'];

			if ( ! file_exists(self::$config['font']))
				throw new Kohana_Exception('captcha.file_not_found', self::$config['font']);
		}

		// Set driver name
		$driver = 'Captcha_'.ucfirst($config['style']).'_Driver';

		// Load the driver
		if ( ! Kohana::auto_load($driver))
			throw new Kohana_Exception('core.driver_not_found', $config['style'], get_class($this));

		// Initialize the driver
		$this->driver = new $driver();

		// Validate the driver
		if ( ! ($this->driver instanceof Captcha_Driver))
			throw new Kohana_Exception('core.driver_implements', $type, get_class($this), 'Captcha_Driver');

		// Generate a new Captcha challenge
		self::$answer = (string) $this->driver->generate_challenge();

		// Store the answer in a session
		Session::instance()->set('captcha_answer', self::$answer);

		Log::add('debug', 'Captcha Library initialized');
	}

	/**
	 * Validates a Captcha answer.
	 *
	 * @param   string   captcha answer
	 * @return  boolean
	 */
	public static function valid($answer)
	{
		return (strtoupper($answer) === strtoupper(Session::instance()->get('captcha_answer')));
	}

	/**
	 * Output the Captcha challenge.
	 *
	 * @param   boolean  TRUE to output html, e.g. <img src="#" />
	 * @return  mixed
	 */
	public function render($html = TRUE)
	{
		return $this->driver->render($html);
	}

	/**
	 * Magically outputs the Captcha challenge.
	 *
	 * @return  mixed
	 */
	public function __toString()
	{
		return $this->render();
	}

} // End Captcha Class