<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'getimagesize_missing'    => 'The Image library requires the <tt>getimagesize</tt> PHP function, which is not available in your installation.',
	'driver_not_supported'    => 'The requested Image driver, %s, was not found.',
	'unsupported_method'      => 'Your configured driver does not support the %s image transformation.',
	'file_not_found'          => 'The specified image, %s was not found. Please verify that images exist by using <tt>file_exists</tt> before manipulating them.',
	'type_not_allowed'        => 'The specified image, %s, is not an allowed image type.',

	// ImageMagick specific messages
	'imagemagick' => array
	(
		'not_found' => 'The ImageMagick directory specified does not contain a required program, %s.',
	),


	// GD specific messages
	'gd' => array
	(
		'requires_v2' => 'The Image library requires GD2. Please see http://php.net/gd_info for more information.',
	),

	// CI's Image_lib stuff below
	'source_image_required'   => 'You must specify a source image in your preferences.',
	'gd_required'             => 'The GD image library is required for this feature.',
	'gd_required_for_props'   => 'Your server must suppor the GD image library in order to determine the image properties',
	'unsupported_imagecreate' => 'Your server does not support the GD function required to process this type of image.',
	'gif_not_supported'       => 'GIF images are often not supported due to licensing restrictions.  You may have to use JPG or PNG images instead.',
	'jpg_not_supported'       => 'JPG images are not supported',
	'png_not_supported'       => 'PNG images are not supported',
	'jpg_or_png_required'     => 'The image resize protocol specified in your preferences only works with JPEG or PNG image types.',
	'copy_error'              => 'An error was encountered while attempting to replace the file.  Please make sure your file directory is writable.',
	'rotate_unsupported'      => 'Image rotation does not appear to be supported by your server.',
	'libpath_invalid'         => 'The path to your image library is not correct.  Please set the correct path in your image preferences.',
	'image_process_failed'    => 'Image processing failed.  Please verify that your server supports the chosen protocol and that the path to your image library is correct.',
	'rotation_angle_required' => 'An angle of rotation is required to rotate the image.',
	'writing_failed_gif'      => 'GIF image ',
	'invalid_path'            => 'The path to the image is not correct',
	'copy_failed'             => 'The image copy routine failed.',
	'missing_font'            => 'Unable to find a font to use.'
);
