<?php defined('SYSPATH') or die('No direct script access.');

class Image_Core {

	protected $driver;
	protected $properties;
	protected $commands;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct($source_image, $driver = NULL)
	{
		// Load the driver
		try
		{
			$driver = (empty($driver)) ? Config::item('image.driver') : $driver;
			$driver_filename = 'Image_'.ucfirst($driver).'_Driver';

			// Manually call auto-loading, for proper exception handling
			Kohana::auto_load($driver_filename);

			$this->driver = new $driver_filename();
		}
		catch (Kohana_Exception $exception)
		{
			throw new Kohana_Exception('image.driver_not_supported', $driver);
		}

		// Validate the driver
		if ( ! in_array('Image_Driver', class_implements($this->driver)))
			throw new Kohana_Exception('image.driver_not_supported', 'Image drivers must use the Image_Driver interface.');
		
		// Take care of source image
		$realpath  = str_replace('\\', '/', realpath($source_image));
		$imagesize = getimagesize($realpath);
		
		if ($imagesize === FALSE)
			// @todo convert to exception
			trigger_error('Invalid source image', E_USER_ERROR);
		
		// Store the original image properties
		$this->properties = array
		(
			'dirname'   => pathinfo($realpath, PATHINFO_DIRNAME).'/',
			'filename'  => pathinfo($realpath, PATHINFO_FILENAME),
			'extension' => '.'.pathinfo($realpath, PATHINFO_EXTENSION),
			'mime'      => $imagesize['mime'],
			'width'     => $imagesize[0],
			'height'    => $imagesize[1]
		);
		
		// Initialize the command list
		$this->commands = array
		(
			'destination'           => $realpath,
			'width'                 => 0,
			'height'                => 0,
			'constrain_proportions' => TRUE,
			'rotate'                => 0
		);
		
		Log::add('debug', 'Image Library initialized');
	}

	/**
	 * Returns the original image properties
	 *
	 * @access	public
	 * @param	mixed
	 * @return	mixed
	 */
	public function properties()
	{
		$args = func_get_args();
		
		// Return array with all properties
		if (empty($args))
			return $this->properties;
		
		// Return one property
		if (count($args) == 1)
			return (isset($this->properties[$args[0]])) ? $this->properties[$args[0]] : FALSE;
		
		// Return multiple properties in specified order
		foreach ($args as $property)
		{
			$return[$property] = (array_key_exists($property, $this->properties)) ? $this->properties[$property] : FALSE;
		}
		
		return $return;
	}
	
	/**
	 * Returns driver version
	 *
	 * @access	public
	 * @return	string
	 */
	public function driver()
	{
		return $this->driver->version();
	}

	/**
	 * Set width
	 *
	 * @access	public
	 * @param	mixed
	 * @return	object
	 */
	public function width($width)
	{
		// Percentage value given?
		$percentage = (bool) strpos($width, '%');
		
		// Clean width
		$width = (int) $width;
		
		// Store height command
		$this->commands['width'] = (int) ($percentage) ? $this->properties['width'] / 100 * $percentage : $width;
		
		return $this;
	}

	/**
	 * Set height
	 *
	 * @access	public
	 * @param	mixed
	 * @return	object
	 */
	public function height($height)
	{
		// Percentage value given?
		$percentage = (bool) strpos($height, '%');
		
		// Clean height
		$height = (int) $height;
		
		// Store height command
		$this->commands['height'] = (int) ($percentage) ? $this->properties['height'] / 100 * $percentage : $height;
		
		return $this;
	}

	/**
	 * Set constrain proportions flag
	 *
	 * @access	public
	 * @param	boolean
	 * @return	object
	 */
	public function constrain_proportions($bool)
	{
		$this->commands['constrain_proportions'] = (bool) $bool;
		
		return $this;
	}

	/**
	 * Set rotation
	 *
	 * @access	public
	 * @param	integer
	 * @return	object
	 */
	public function rotate($degrees)
	{
		// Don't spin just because you like to, no more than 360°, baby!
		$degrees = (int) $degrees % 360;
		
		// Only spin forward
		if ($degrees < 0)
		{
			$degrees += 360;
		}
		
		// Store rotation command
		$this->commands['rotate'] = $degrees;
		
		return $this;
	}

	/**
	 * Display image
	 *
	 * @access	public
	 * @return	void
	 */
	public function display()
	{
		header('Content-type: '.$this->properties['mime']);
		// Process all commands ...
	}

	/**
	 * Save image
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
	 */
	public function save($destination = NULL)
	{
		// Process all commands ...
	}
	
} // End Image Class