<?php defined('SYSPATH') or die('No direct script access.');

class Form_Dateselect_Core extends Form_input{

	protected $data = array
	(
		'name'  => '',
		'class' => 'dropdown',
	);

	protected $protect = array('type');

	public function __get($key)
	{
		if ($key == 'value')
		{
			return $this->selected;
		}

		return parent::__get($key);
	}

	public function html_element()
	{
		// Import base data
		$base_data = $this->data;
		$name = $base_data['name'];

		// Get the options and default selection
		$date = arr::remove('value', $base_data);

		return form::dropdown($name.'[month]', date::months(), date('m', $date)).' '.
		       form::dropdown($name.'[day]', date::days(12), date('d', $date)).' '.
		       form::dropdown($name.'[year]', date::years(), date('Y', $date)).' @ '.
		       form::dropdown($name.'[hour]', date::hours(), date('g', $date)).':'.
		       form::dropdown($name.'[minute]', date::minutes(), date('i', $date));
	}

	protected function load_value()
	{
		if (empty($_POST))
			return;

		$time = self::$input->post($this->name);

		$this->data['value'] = mktime($time['hour'], $time['minute'], 0, $time['month'], $time['day'], $time['year']);
	}

} // End Form Dateselect