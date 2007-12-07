<?php defined('SYSPATH') or die('No direct script access.');

class Form_Address_Core extends Form_Input {

	protected $data = array
	(
		'type'  => 'text',
		'class' => 'address',
		'value' => array('line1' => '', 'line2' => '', 'unit' => ''),
	);

	protected $protected = array('type');

	protected function rule_required()
	{
		if (empty($this->data['value']['line1']))
		{
			$this->errors['required'] = TRUE;
		}
	}

	protected function html_element()
	{
		// Import base data locally
		$line1 = $this->data;

		// Don't need labels
		unset($line1['label']);

		// Fetch the set values
		$value = arr::remove('value', $line1);

		// All inputs have the same base data
		$unit = $line2 = $line1;

		// Address line 1
		$line1['name']  .= '[line1]';
		$line1['value']  = current($value);

		// Address line 2
		$line2['name']  .= '[line2]';
		$line2['value']  = next($value);
		$line2['class'] .= '-2';

		// Address unit
		$unit['name']  .= '[unit]';
		$unit['value']  = next($value);
		$unit['class'] .= '-unit';

		return
			form::input($line1)."<br/>\n".
			form::input($line2).
			' <label>'.Kohana::lang('forge.address_unit').' '.
				form::input($unit).
			'</label>';
	}

} // End Form Address