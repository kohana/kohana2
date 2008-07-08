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

	// Config
	protected $font_path        = '';
	protected $font_name        = '';
	protected $width            = 150;
	protected $height           = 50;
	protected $background_image = '';
	protected $style            = 'basic';
	protected $num_chars        = 4;

	// Class internal variables
	protected $image;
	protected $color_black;
	protected $color_white;
	protected $spacing;
	protected $captcha_code;
	protected $numerals = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');

	/**
	 * Creates a new Captcha instance.
	 *
	 * @throws  Kohana_Exception
	 * @param   array  configuration
	 * @return  void
	 */
	public function __construct($config = array())
	{
		static $check;

		// Check that a suitable GD2 library is available
		($check === NULL) and $check = function_exists('imagegd2');

		if ($check === FALSE)
			throw new Kohana_Exception('captcha.requires_GD2');

		// Load configuration
		$config += Config::item('captcha', FALSE, FALSE);

		$this->initialize($config);

		// If using a background image, check if it exists.
		if ($this->background_image)
		{
			if ( ! file_exists($this->background_image))
				throw new Kohana_Exception('captcha.file_not_found', $this->background_image);
		}

		// If using a font, check if it exists.
		if ($this->font_name)
		{
			if ( ! file_exists($this->font_path.$this->font_name))
				throw new Kohana_Exception('captcha.file_not_found', $this->font_path.$this->font_name);
		}

		Log::add('debug', 'Captcha Library initialized');
	}

	/**
	 * Sets or overwrites config values.
	 *
	 * @param   array  configuration
	 * @return  void
	 */
	public function initialize($config)
	{
		// Assign config values to the object
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * Sets the Captcha code to use.
	 *
	 * @param   string  captcha code generated in captcha controller
	 * @return  void
	 */
	public function set_code($str)
	{
		$this->captcha_code = (string) $str;
	}

	/**
	 * Generates the Captcha image.
	 *
	 * @return  void
	 */
	public function render()
	{
		// If extending the class with a custom Captcha function, name it 'xyz_captcha'.
		// Style 'xyz' must be added to config. Now call the method that implements the Captcha.
		$this->{$this->style.'_captcha'}();

		// Tell browser what to expect
		// TODO: make this automatic
		// header('Content-Type: image/jpeg');
		header('Content-Type: image/png');

		// Output the captcha image
		// imagejpeg($this->image);
		imagepng($this->image);

		// Free up resources
		imagedestroy($this->image);
	}

	/**
	 * Validates the Captcha code against session Captcha code
	 *
	 * @param   string   captcha code text
	 * @return  boolean
	 */
	public static function valid_captcha($str)
	{
		return (strtoupper($str) === strtoupper(Session::instance()->get('captcha_code')));
	}

	/**
	 * Creates image resource and allocates some basic colors.
	 * If a background image is supplied, the image dimensions are used.
	 *
	 * @return  void
	 */
	protected function img_create()
	{
		if ($this->background_image)
		{
			// TODO: create from any valid image
			$this->image = imagecreatefromjpeg($this->background_image);
			$this->color_white = imagecolorallocate($this->image, 255, 255, 255);

			// Get the background image dimensions
			$this->width  = imagesx($this->image);
			$this->height = imagesy($this->image);
		}
		else
		{
			$this->image = imagecreatetruecolor($this->width, $this->height);
			$this->color_white = imagecolorallocate($this->image, 255, 255, 255);

			// Fill the image with a colored gradient (use random colors, but try not to obscure text)
			$left_color  = array(mt_rand(100,255), 0, 255);
			$right_color = array(100, 100, mt_rand(100,0));
			$this->img_color_gradient($this->image, 0, 0, $this->height, $this->width, $left_color, $right_color);
		}
	}

	/**
	 * Allocates a background color to image.
	 *
	 * @param   array  GD image color identifier
	 * @return  void
	 */
	protected function img_background($color)
	{
		imagefill($this->image, 0, 0, $color);
	}

	/**
	 * Draws a very basic Captcha image.
	 * Requires only GD. Useful for testing or if you can't use truetype fonts.
	 *
	 * @return  void
	 */
	protected function basic_captcha()
	{
		$this->image       = imagecreate($this->width, $this->height);
		$this->color_white = imagecolorallocate($this->image, 255, 255, 255);
		$this->color_black = imagecolorallocate($this->image, 0, 0, 0);

		imagestring($this->image, 5, 50, 15, $this->captcha_code, $this->color_black);
	}

	/**
	 * Draws the standard Captcha image:
	 * Requires GD with freetype and available truetype compatible font files.
	 *
	 * @param   none
	 * @return  void
	 */
	protected function standard_captcha()
	{
		$this->img_create();

		$font = $this->font_path.$this->font_name;
		$this->calculate_spacing();

		// Draw each Captcha character with varying attributes
		for ($i = 0, $strlen = strlen($this->captcha_code); $i < $strlen; $i++)
		{
			// Allocate random color, size and rotation attributes to text
			$text_color = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
			$angle = mt_rand(-40, 40);

			// Make first char angle inward
			if ($i === 0)
			{
				$angle = -abs($angle);
			}
			// Make last char angle inward
			if ($i === ($this->num_chars - 1))
			{
				$angle = abs($angle);
			}

			// Scale the character size on image height
			$font_size = mt_rand($this->height - 20, $this->height - 12);
			$char_details = imageftbbox($font_size, $angle, $font, $this->captcha_code[$i], array());

			// Calculate character starting coordinates
			$iX = $this->spacing / 4 + $i * $this->spacing;
			$char_height = $char_details[2] - $char_details[5];
			$iY = $this->height / 2 + $char_height / 4;

			// Write text character to image
			imagefttext($this->image, $font_size, $angle, $iX, $iY, $text_color, $font, $this->captcha_code[$i], array());
		}
	}

	/**
	 * Draws the alphasoup Captcha image:
	 * Requires GD with freetype and available truetype compatible font files.
	 *
	 * @param   none
	 * @return  void
	 */
	protected function alphasoup_captcha()
	{
		$this->img_create();
		$font = $this->font_path.$this->font_name;
		$text_color = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
		$color_limit = mt_rand(96, 160);
		$fill_color = imageColorAllocate($this->image, mt_rand($color_limit, 255), mt_rand($color_limit, 255), mt_rand($color_limit, 255));
		imageFilledRectangle($this->image, 0, 0, $this->width, $this->height, $fill_color);
		$font_width = imageFontWidth(10);
		$chars = 'ABCDEFGHIJKLMNO';

		for($loop = 0; $loop < 20; $loop++)
		{
			$text_color = imageColorAllocate($this->image, mt_rand($color_limit + 8, 255), mt_rand($color_limit + 8, 255), mt_rand($color_limit + 8, 255));
			$char = substr($chars, mt_rand(0, 15), 1);
			imageTTFtext($this->image, mt_rand(23, 27), mt_rand(160, 200), mt_rand(-10, $this->width + 10), mt_rand(-10, 60), $text_color, $font, $char);
		}

		$this->calculate_spacing();
		// Draw each Captcha character with varying attributes
		for ($i = 0, $strlen = strlen($this->captcha_code); $i < $strlen; $i++)
		{
			// Allocate random color, size and rotation attributes to text
			$text_color = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
			$angle = mt_rand(-40, 40);

			// Make first char angle inward
			if ($i === 0)
			{
				$angle = -abs($angle);
			}
			// Make last char angle inward
			if ($i === ($this->num_chars - 1))
			{
				$angle = abs($angle);
			}

			// Scale the character size on image height
			$font_size = mt_rand($this->height - 20, $this->height - 12);
			$char_details = imageftbbox($font_size, $angle, $font, $this->captcha_code[$i], array());

			// Calculate character starting coordinates
			$iX = $this->spacing / 4 + $i * $this->spacing;
			$char_height = $char_details[2] - $char_details[5];
			$iY = $this->height / 2 + $char_height / 4;

			// Write text character to image
			imagefttext($this->image, $font_size, $angle, $iX, $iY, $text_color, $font, $this->captcha_code[$i], array());
		}

	}

	/**
	 * Draws the math riddle Captcha image.
	 * Requires GD with freetype and available truetype compatible font files.
	 *
	 * @return  void
	 */
	protected function math_captcha()
	{
		$answer = Session::instance()->get('captcha_code');

		// Convert to numeral
		$numeral = $this->numerals[substr($answer, -1)];

		// Subtract last digit from answer
		$number = substr($answer, 0, 2).'0';

		// $number plus $numeral equals $answer
		$text = $number.' + '.$numeral.' = ';
		$this->img_create();
		$font = $this->font_path.$this->font_name;

		// Scale the font size to image height
		$font_size = $this->height / 3;
		$text_details = imageftbbox($font_size, 0, $font, $text, array());
		$iX = 5;
		$iY = ($this->height / 2) + 5;

		imagefttext($this->image, $font_size, 0, $iX, $iY, $this->color_white, $font, $text, array());
	}

	/**
	 * Calculates letter spacing for truetype font characters.
	 *
	 * @return  integer
	 */
	protected function calculate_spacing()
	{
		return $this->spacing = (int) $this->width / $this->num_chars;
	}

	/**
	 * Fills the image with a colored gradient.
	 *
	 * @param   resource  gd image resource identifier
	 * @param   integer   start X position
	 * @param   integer   start Y position
	 * @param   integer   height of fill in pixels
	 * @param   integer   width of fill in pixels
	 * @param   resource  gd image color identifier for left of image
	 * @param   resource  gd image color identifier for right of image
	 * @return  void
	 */
	protected function img_color_gradient($image, $x1, $y1, $height, $width, $left_color, $right_color)
	{
		$color0 = ($left_color[0] - $right_color[0]) / $width;
		$color1 = ($left_color[1] - $right_color[1]) / $width;
		$color2 = ($left_color[2] - $right_color[2]) / $width;

		for ($i = 0; $i <= $width; $i++)
		{
			$red   = $left_color[0] - floor($i * $color0);
			$green = $left_color[1] - floor($i * $color1);
			$blue  = $left_color[2] - floor($i * $color2);
			$col   = imagecolorallocate($this->image, $red, $green, $blue);

			imageline($this->image, $x1 + $i, $y1, $x1 + $i, $y1 + $height, $col);
		}
	}

} // End Captcha Class