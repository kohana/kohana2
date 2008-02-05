<?php defined('SYSPATH') or die('No direct script access.');
/**
 * FORGE hidden input library.
 *
 * $Id$
 *
 * @package    Forge
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Form_Hidden_Core extends Form_Input {

	protected $data = array
	(
		'type'  => 'hidden',
		'class' => 'hidden',
		'value' => '',
	);

	protected $protect = array('type', 'label');

	public function html()
	{
		$data = $this->data;

		return form::hidden($data);
	}

} // End Form Hidden