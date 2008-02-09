<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package  Media Module
 *
 * $Id$
 *
 * The media controller is a way of serving up various media content (JS, CSS, images, etc)
 *
 * The idea is that you have a subdirectory in your application views directory called "media",
 * which contains all the files needed. For example:
 *
 * http://baseurl/media/css/style1.css   ->   views/media/css/style1.css  or  style1.css.php
 *
 * Additionally, CSS and Javascript files can be packed and cached.
 */

/**
 * Enable media caching.
 *
 * Set to false to disable, true to enable using default cache lifetimes, or number of seconds to cache for
 *
 * Strongly recommended if you are using packing.
 */
$config['cache'] = false;

/**
 * If CSS files should be packed (whitespace, comments, etc removed)
 *
 * Boolean
 */
$config['pack_css'] = false;

/**
 * If javascript files should be packed.
 *
 * Value should be one of: false, 0,10,62,95 or 'Numeric', 'Normal', 'High ASCII'.
 *
 * false or 0 disables packing
 */
$config['pack_js'] = false;

