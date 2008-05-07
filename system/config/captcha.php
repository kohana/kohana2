<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package  Captcha
 *
 * Configure the Captcha
 * Custom styles can be added by extending the Captcha class
 */

/**
 * Width and height of the Captcha image.
 * These settings are ignored if using a background image.
 */
$config['width']  = 150;
$config['height'] = 50;

/**
 * Captcha style to use. Default is 'basic' and is only for testing as it
 * does not require any truetype fonts installed.
 * 'standard' is the recommended style. A font must be supplied. A background
 * image is optional.
 * 'alphasoup' is an alternative style. A font must be supplied.
 * 'math' is a 'solve the question' style.
 * A font must be supplied. A background image is optional.
 * Custom styles can be added easily by extending the library.
 */
$config['style'] = 'standard';

/**
 * Number of characters to use for the Captcha (4 or 5 recommended).
 * This setting is ignored if using style 'math'.
 */
$config['num_chars'] = 4;

/**
 * Path to font files. Example: 'application/fonts/'.
 * If using 'standard' style, you must supply a valid truetype font file.
 */
$config['font_path'] = SYSPATH.'fonts/';

/**
 * Name of the font, with the file extension. Case sensitive.
 */
$config['font_name'] = 'DejaVuSerif.ttf';

/**
 * Background image. Example: 'application/images/pattern.jpg'.
 * The dimensions of this image will be used.
 */
$config['background_image'] = '';