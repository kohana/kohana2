<?php defined('SYSPATH') or die('No direct script access.');

class Image_Core {

	const NONE = 1;
	const AUTO = 2;
	const HEIGHT = 3;
	const WIDTH  = 4;
	const HORIZONTAL = 5;
	const VERTICAL = 6;

	public static $allowed_types = array
	(
		IMAGETYPE_GIF => 'gif',
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_PNG => 'png',
		IMAGETYPE_TIFF_II => 'tiff',
		IMAGETYPE_TIFF_MM => 'tiff',
	);

	protected $image = '';

	protected $driver;

	protected $actions = array();

	/**
	 * Creates a new image editor instance.
	 *
	 * @throws  Kohana_Exception
	 * @param   string   filename of image
	 * @param   array    non-default configurations
	 * @return  void
	 */
	public function __construct($image, $config = array())
	{
		// Load configuration
		$this->config = (array) $config + Config::item('image');

		try
		{
			$driver = 'Image_'.ucfirst($this->config['driver']).'_Driver';

			// Manually autoload so that exceptions can be caught
			Kohana::auto_load($driver);
		}
		catch (Kohana_Exception $e)
		{
			// Driver was not found
			throw new Kohana_Exception('cache.driver_not_supported', $this->config['driver']);
		}

		// Initialize the driver
		$this->driver = new $driver($this->config['params']);

		if ( ! file_exists($image))
			throw new Kohana_Exception('image.file_not_found', $image);

		if (($type = exif_imagetype($image)) == FALSE OR ! isset(Image::$allowed_types[$type]))
			throw new Kohana_Exception('image.type_not_allowed', $image);

		$this->image = str_replace('\\', '/', realpath($image));
	}

	/**
	 * Resize an image to a specific width and height. By default, Kohana will
	 * maintain the aspect ratio using the width as the master dimension. If you
	 * wish to use height as master dim, set $image->master_dim = Image::HEIGHT
	 * This method is chainable.
	 *
	 * @param   integer  width
	 * @param   integer  height
	 * @param   integer  one of: Image::NONE, Image::AUTO, Image::WIDTH, Image::HEIGHT
	 * @return  object
	 */
	public function resize($width, $height, $master = NULL)
	{
		if ( ! $this->valid_size('width', $width))
			throw new Kohana_Exception('image.invalid_width', $width);

		if ( ! $this->valid_size('height', $height))
			throw new Kohana_Exception('image.invalid_height', $height);

		if ($master === NULL)
		{
			// Maintain the aspect ratio by default
			$master = self::AUTO;
		}
		elseif ( ! $this->valid_size('master', $master))
			throw new Kohana_Exception('image.invalid_master');

		$this->actions['resize'] = array
		(
			'width'  => $width,
			'height' => $height,
			'master' => $master,
		);

		return $this;
	}

	/**
	 * Crop an image to a specific width and height. You may also set the top
	 * and left offset.
	 * This method is chainable.
	 *
	 * @param   integer  width
	 * @param   integer  height
	 * @param   integer  top offset, pixel value or one of: top, center, bottom
	 * @param   integer  left offset, pixel value or one of: left, center, right
	 * @return  object
	 */
	public function crop($width, $height, $top = 'center', $left = 'center')
	{
		if ( ! $this->valid_size('width', $width))
			throw new Kohana_Exception('image.invalid_width', $width);

		if ( ! $this->valid_size('height', $height))
			throw new Kohana_Exception('image.invalid_height', $height);

		if ( ! $this->valid_size('top', $top))
			throw new Kohana_Exception('image.invalid_top', $top);

		if ( ! $this->valid_size('left', $left))
			throw new Kohana_Exception('image.invalid_left', $left);

		$this->actions['crop'] = array
		(
			'width'  => $width,
			'height' => $height,
			'top'    => $top,
			'left'   => $left,
		);

		return $this;
	}

	/**
	 * Allows rotation of an image by 180 degrees clockwise or counter clockwise.
	 * This method is chainable.
	 *
	 * @param   integer  degrees
	 * @return  object
	 */
	public function rotate($degrees)
	{
		$degrees = (int) $degrees;

		if ($degrees > 180)
		{
			do
			{
				// Keep subtracting full circles until the degrees have normalized
				$degrees -= 360;
			}
			while($degrees > 180);
		}

		if ($degrees < -180)
		{
			do
			{
				// Keep adding full circles until the degrees have normalized
				$degrees += 360;
			}
			while($degrees < -180);
		}

		$this->actions['rotate'] = $degrees;

		return $this;
	}

	public function flip($direction)
	{
		if ($direction !== self::HORIZONTAL AND $direction !== self::VERTICAL)
			throw new Kohana_Exception('image.invalid_flip');

		$this->actions['flip'] = $direction;

		return $this;
	}

	public function quality($value)
	{
		$this->actions['quality'] = $value;

		return $this;
	}

	public function save($new_image = FALSE)
	{
		// If no new image is defined, use the current image
		empty($new_image) and $new_image = $this->image;

		// Separate the directory and filename
		$dir  = pathinfo($new_image, PATHINFO_DIRNAME);
		$file = pathinfo($new_image, PATHINFO_BASENAME);

		// Normalize the path
		$dir = str_replace('\\', '/', realpath($dir)).'/';

		if ( ! is_writable($dir))
			throw new Kohana_Exception('image.directory_unwritable', $dir);

		$this->driver->process($this->image, $this->actions, $dir, $file);
	}

	protected function valid_size($type, & $value)
	{
		if (is_null($value))
			return TRUE;

		if ( ! is_scalar($value))
			return FALSE;

		switch($type)
		{
			case 'width':
			case 'height':
				if (is_string($value) AND ! ctype_digit($value))
				{
					if ( ! preg_match('/[0-9]+%$/', $value))
						return FALSE;
				}
				else
				{
					$value = (int) $value;
				}
			break;
			case 'top':
				if (is_string($value) AND ! ctype_digit($value))
				{
					if ( ! in_array($value, array('top', 'bottom', 'center')))
						return FALSE;
				}
				else
				{
					$value = (int) $value;
				}
			break;
			case 'left':
				if (is_string($value) AND ! ctype_digit($value))
				{
					if ( ! in_array($value, array('left', 'right', 'center')))
						return FALSE;
				}
				else
				{
					$value = (int) $value;
				}
			break;
			case 'master':
				if ($value !== self::NONE AND $value !== self::AUTO AND $value !== self::WIDTH AND $value !== self::HEIGHT)
					return FALSE;
			break;
		}
		return TRUE;
	}

}