<?php defined('SYSPATH') or die('No direct script access.');

class Form_Dateselect_Core extends Form_input{

	protected $data = array
	(
		'name'  => '',
		'class' => 'dropdown',
	);

	protected $protect = array('type');

	public function __construct($name)
	{
		// Set name
		$this->data['name'] = $name;

		// Default to the current time
		$this->data['value'] = time();
	}

	public function html_element()
	{
		// Import base data
		$base_data = $this->data;
		$name = $base_data['name'];

		// Get the options and default selection
		$time = $this->time_array(arr::remove('value', $base_data));

		return form::dropdown($name.'[month]', date::months(), $time['month']).' '.
		       form::dropdown($name.'[day]', date::days(date('m')), $time['day']).' '.
		       form::dropdown($name.'[year]', date::years(), $time['year']).' @ '.
		       form::dropdown($name.'[hour]', date::hours(), $time['hour']).':'.
		       form::dropdown($name.'[minute]', date::minutes(), $time['minute']).' '.
		       form::dropdown($name.'[am_pm]', array('AM' => 'AM', 'PM' => 'PM'), $time['am_pm']);
	}

	protected function time_array($timestamp)
	{
		$time = array_combine(
			array('month', 'day', 'year', 'hour', 'minute', 'am_pm'), 
			explode('--', date('n--j--Y--g--i--A', $timestamp)));

		// Minutes should always be in 5 minute increments
		$time['minute'] = num::round($time['minute'], 5);

		return $time;
	}

	protected function load_value()
	{
		if (empty($_POST))
			return;

		$time = self::$input->post($this->name);

		// Make sure all the required inputs keys are set
		$time += $this->time_array(time());

		$this->data['value'] = mktime
		(
			// If the time is PM, add 12 hours for 24 hour time
			($time['am_pm'] == 'PM') ? $time['hour'] + 12  : $time['hour'],
			$time['minute'],
			0,
			$time['month'],
			$time['day'],
			$time['year']);
	}

} // End Form Dateselect