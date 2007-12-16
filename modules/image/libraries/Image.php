<?php defined('SYSPATH') or die('No direct script access.');

class Image_Core {

	const HEIGHT = 1;
	const WIDTH  = 2;

	public static $allowed_types = array
	(
		IMAGETYPE_GIF => 'gif',
		IMAGETYPE_JPEG => 'jpg',
		IMAGETYPE_PNG => 'png',
		IMAGETYPE_TIFF_II => 'tiff',
		IMAGETYPE_TIFF_MM => 'tiff',
	);

	protected $actions = array();

	protected $image_file = '';
	protected $image_type = '';

	public $master_dim;

	public function __construct($image)
	{
		if ( ! file_exists($image))
			throw new Kohana_Exception('image.file_not_found', $image);

		if (($type = exif_imagetype($image)) == FALSE OR ! isset(Image::$allowed_types[$type]))
			throw new Kohana_Exception('image.type_not_allowed', $image);

		$this->image_file = str_replace('\\', '/', realpath($image));
		$this->image_type = $type;

		$this->master_dim = Image::WIDTH;
	}

	public function resize($width, $height, $force = FALSE)
	{
		$this->actions['resize'] = array('width' => $width, 'height' => $height, 'force' => (bool) $force);

		return $this;
	}

	public function crop($width, $height, $top = 'center', $left = 'center')
	{
		if (is_string($top))
		{
			if ( ! in_array($top, array('top', 'bottom', 'center')))
			{
				$top = 0;
			}
		}

		if (is_string($left))
		{
			if ( ! in_array($top, array('left', 'right', 'center')))
			{
				$left = 0;
			}
		}

		$this->actions['crop'] = array
		(
			'width'  => $width,
			'height' => $height,
			'top'    => $top,
			'left'   => $left,
		);

		return $this;
	}

	public function rotate($degrees)
	{
		$this->actions['rotate'] = array
		(
			'degrees' => ($degrees < 0) ? max(-360, min(0, $degrees)) : max(0, min($degrees, 360)),
		);

		return $this;
	}

}