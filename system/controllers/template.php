<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana - The Swift PHP Framework
 *
 *  License:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

/**
 * Allows a template to be automatically loaded and displayed. Display can be
 * dynamically turned off in the controller methods, and the template file
 * can be overloaded.
 *
 * Usage:
 * To use the Template_Controller, declare your controller to extend this class:
 * `class Your_Controller extends Template_Controller`
 */
class Template_Controller extends Controller {

	// Template view name
	protected $template = 'template';

	// Default to no auto-rendering
	protected $auto_render = TRUE;

	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
		parent::__construct();

		// Load the template
		$this->template = new View($this->template);

		if ($this->auto_render === TRUE)
		{
			// Display the template immediately after the controller method
			Event::add('system.post_controller', array($this, '_display'));
		}
	}

	/**
	 * Display the loaded template.
	 */
	public function _display()
	{
		if ($this->auto_render === TRUE)
		{
			// Render the template when the class is destroyed
			$this->template->render(TRUE);
		}
	}

} // End Template_Controller