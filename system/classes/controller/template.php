<?php
/**
 * Allows a template to be automatically loaded and displayed. Display can be
 * dynamically turned off in the controller methods, and the template file
 * can be overloaded.
 *
 * To use it, declare your controller to extend this class:
 * `class Controller_Your extends Controller_Template`
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
abstract class Controller_Template extends Controller {

	// Template view name
	public $template = 'template';

	// Default to do auto-rendering
	public $auto_render = TRUE;

	/**
	 * Template loading and setup routine.
	 */
	public function __construct(Kohana_Request $request)
	{
		parent::__construct($request);

		// Load the template
		$this->template = View::factory($this->template);
	}

	/**
	 * Render the loaded template.
	 */
	public function _end_request()
	{
		if ($this->auto_render === TRUE)
		{
			// Render the template when the class is destroyed
			$this->template->render(TRUE);
		}
	}

} // End Controller_Template