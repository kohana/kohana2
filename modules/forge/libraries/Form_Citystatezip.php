<?php defined('SYSPATH') or die('No direct script access.');

class Form_Citystatezip_Core extends Form_Input {

	protected $data = array
	(
		'type'  => 'text',
		'class' => 'address',
		'value' => array('city' => '', 'state' => '', 'zip' => '')
	);

	protected $protect = array('type');

	protected function html_element()
	{
		$city = $this->data;

		// Don't use label
		unset($city['label']);

		// Get input values
		$values = arr::remove('value', $city);

		$state = $zip = $city;

		// City input
		$city['name']  .= '[city]';
		$city['value']  = current($values);

		// State dropdown
		$state['name']  .= '[state]';
		$state['type']   = 'dropdown';
		$state_options   = locale_US::states();
		$state_selected  = next($values);
		$state['class'] .= '-state';

		// Zip input
		$zip['name']  .= '[zip]';
		$zip['value']  = next($values);
		$zip['class'] .= '-zip';

		return
			form::input($city)."<br/>\n".
			form::dropdown($state, $state_options, $state_selected).' '.
			form::input($zip);
	}

	protected function rule_required()
	{
		foreach($this->data['value'] as $key => $val)
		{
			if (empty($val))
			{
				$this->errors['required'] = TRUE;
			}
		}
	}

} // End Form Citystatezip