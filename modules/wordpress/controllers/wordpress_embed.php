<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Runs Wordpress as an embedded controller.
 *
 * $Id$
 *
 * @package    Wordpress
 * @author     Woody Gilk
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Wordpress_Embed_Controller extends Controller {

	// WordPress installation directory
	protected $dir;

	public function _remap()
	{
		if ( ! ($this->dir = Config::item('wordpress.directory')) OR ! is_dir($this->dir))
			throw new Kohana_Exception('wordpress.invalid_directory');

		// Directory must always have a trailing slash
		$this->dir = rtrim($this->dir, '/').'/';

		// Prevent wordpress from using themes
		// define('WP_USE_THEMES', FALSE);

		// Start up the blog!
		require $this->dir.'wp-blog-header'.EXT;
		require $this->dir.'includes/application_top'.EXT;

		// Load the footer after the controller has been executed
		Event::add('system.post_controller', array($this, '_load_bottom'));
	}

	public function _load_bottom()
	{
		// Load the footer
		require $this->dir.'application_bottom'.EXT;
	}

} // End Wordpress Embed Controller