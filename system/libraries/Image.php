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
		$realpath     = str_replace('\\', '/', realpath($source_image));
		$getimagesize = getimagesize($realpath);
		
		if ($getimagesize === FALSE)
			trigger_error('Invalid source image', E_USER_ERROR);
		
		// Store the original image properties
		$this->properties = array
		(
			'dirname'   => pathinfo($realpath, PATHINFO_DIRNAME).'/',
			'filename'  => pathinfo($realpath, PATHINFO_FILENAME),
			'extension' => '.'.pathinfo($realpath, PATHINFO_EXTENSION),
			'mime'      => $getimagesize['mime'],
			'width'     => $getimagesize[0],
			'height'    => $getimagesize[1]
		);
		
		// Initialize the command list
		$this->commands = array
		(
			'width'                 => $this->properties['width'],
			'height'                => $this->properties['height'],
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
	 * Set width
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	public function width($width)
	{
		// Percentage value given?
		$percentage = (bool) strpos($width, '%');
		
		// Clean width
		$width = preg_replace('/\D+/', '', $width);
		
		// Store width command
		$this->commands['width'] = (int) ($percentage) ? $this->properties['width'] / 100 * $percentage : $width;
	}

	/**
	 * Set height
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
	public function height($height)
	{
		// Percentage value given?
		$percentage = (bool) strpos($height, '%');
		
		// Clean height
		$height = preg_replace('/\D+/', '', $height);
		
		// Store width command
		$this->commands['height'] = (int) ($percentage) ? $this->properties['height'] / 100 * $percentage : $height;
	}

	/**
	 * Set constrain proportions flag
	 *
	 * @access	public
	 * @param	boolean
	 * @return	void
	 */
	public function constrain_proportions($bool)
	{
		$this->commands['constrain_proportions'] = (bool) $bool;
	}

	/**
	 * Set rotation
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */
	public function rotate($degrees)
	{
		// Clean degrees
		$degrees = (int) $degrees;
		
		// Don't spin just because you like to, no more than 360°, baby!
		$degrees = $degrees % 360;
		
		// No rotation to be done
		if ($degrees == 0)
			return;
		
		// We only spin forward
		if ($degrees < 0)
		{
			$degrees += 360;
		}
		
		// Store rotation command
		$this->commands['rotate'] = $degrees;
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