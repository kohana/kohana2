<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package  Captcha
 *
 * Configure the Captcha
 * Custom styles can be added by extending the Captcha class
 */
/**
 * Width of captcha image, ignored if using a background
 */
$config['width'] = 150;
/**
 * Height of captcha image, ignored if using a background
 */
$config['height'] = 40;
/**
 * Captcha style to use. Default is 'basic' and is only for testing as it
 * does not require any TrueType fonts installed.
 * 'standard' is the recommended style. A font must be supplied. A background
 * image is optional.
 * 'math' is a 'solve the question' style.
 * A font must be supplied. A background image is optional.
 * Custom styles can be added easily by extending the library
 */
$config['style'] = 'basic';
/**
 * Number of characters to use for the captcha, ignored if using style 'math'
 * Four or five seems optimal.
 */
$config['num_chars'] = 4;
/**
 * Path to font files. Default is none, ''. Example 'application/fonts/'
 * If using 'standard' style, you must supply a valid truetype font file.
 */
$config['font_path'] = '';
/**
 * Name of the font, Case sensitive, with the file extension, default is ''
 */
$config['font_name'] = '';
/**
 * Background image. Default ''. Example 'application/images/pattern.jpg'
 * The dimensions of this image will be used.
 */
$config['background_image'] = '';