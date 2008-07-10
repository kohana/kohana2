<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package  Core
 *
 * Captcha configuration is defined in groups which allows you to easily switch
 * between different Captcha settings for different forms on your website.
 * Note: all groups inherit and overwrite the default group.
 *
 * Group Options:
 *  style           - Captcha type, e.g. basic, alpha, word, math, riddle
 *  width           - Width of the Captcha image
 *  height          - Height of the Captcha image
 *  complexity      - Difficulty level (0-10), usage depends on chosen style
 *  background_path - Path to folder in which background image reside
 *  background_file - Image file name
 *  font_path       - Path to folder in which fonts reside
 *  font_file       - Font file name
 */
$config['default'] = array
(
	'style'           => 'alpha',
	'width'           => 150,
	'height'          => 50,
	'complexity'      => 4,
	'background_path' => '',
	'background_file' => '',
	'font_path'       => SYSPATH.'fonts/',
	'font_file'       => 'DejaVuSerif.ttf',
);