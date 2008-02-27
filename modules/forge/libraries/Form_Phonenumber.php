<?php defined('SYSPATH') or die('No direct script access.');
/**
 * FORGE phone number input library.
 *
 * $Id$
 *
 * @package    Forge
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Form_Phonenumber_Core extends Form_Input {

	protected $data = array
	(
		'name'  => '',
		'class' => 'phone_number',
	);

	protected $protect = array('type');

	// Precision for the parts, you can use @ to insert a literal @ symbol
	protected $parts = array
	(
		'area_code'   => '',
		'exchange'     => '',
		'last_four'    => '',
	);

	public function __construct($name)
	{
		// Set name
		$this->data['name'] = $name;
	}

	public function __call($method, $args)
	{
		if (isset($this->parts[substr($method, 0, -1)]))
		{
			// Set options for date generation
			$this->parts[substr($method, 0, -1)] = $args;
			return $this;
		}

		return parent::__call($method, $args);
	}

	public function html_element()
	{
		// Import base data
		$data = $this->data;

		$input = '';
		foreach($this->parts as $type => $val)
		{
			isset($data['value']) OR $data['value'] = '';
			$data['name'] = $this->data['name'].'['.$type.']';
			$data['class'] = $type;
			switch ($type)
			{
				case 'area_code':
					$data['value'] = substr($data['value'], 0, 3);
					$input .= form::input(array_merge(array('value' => $val), $data)).'-';
					break;
				case 'exchange':
					$data['value'] = substr($data['value'], 3, 3);
					$input .= form::input(array_merge(array('value' => $val), $data)).'-';
					break;
				case 'last_four':
					$data['value'] = substr($data['value'], 6, 4);
					$input .= form::input(array_merge(array('value' => $val), $data));
					break;
			}
			
		}

		return $input;
	}

	protected function load_value()
	{
		if (is_bool($this->valid))
			return;

		$data = $this->input_value($this->name, $this->data['name']);

		$this->data['value'] = $data['area_code'].$data['exchange'].$data['last_four'];
	}
} // End Form Phonenumber