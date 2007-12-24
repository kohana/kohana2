<?php defined('SYSPATH') or die('No direct script access.');

class Calendar_Core {

	protected $month;
	protected $year;

	public function __construct($month = NULL, $year = NULL)
	{
		empty($month) and $month = date('n'); // Current month
		empty($year)  and $year  = date('Y'); // Current year

		$this->month = $month;
		$this->year  = $year;
	}

	public function weeks()
	{
		// First day of the month as a timestamp
		$first = mktime(1, 0, 0, $this->month, 1, $this->year);

		// Total number of days in this month
		$total = (int) date('t', $first);

		// Last day of the month as a timestamp
		$last  = mktime(1, 0, 0, $this->month, $total, $this->year);

		// Make the month and week empty arrays
		$month = $week = array();

		// Number of days added. When this reaches 7, start a new month
		$days = 0;

		if (($w = (int) date('w', $first)) > 0)
		{
			// Number of days in the previous month
			$n = (int) date('t', mktime(1, 0, $this->month - 1, 1, $this->year));

			// i = number of day, t = number of days to pad
			for($i = $n - $w, $t = $w; $t > 0; $t--, $i++)
			{
				// Add the previous months padding days
				$week[] = array($i, FALSE);
				$days++;
			}
		}

		// i = number of day
		for($i = 1; $i <= $total; $i++)
		{
			if ($days % 7 === 0)
			{
				// Start a new week
				$month[] = $week;
				$week = array();
			}

			// Add days to this month
			$week[] = array($i, TRUE);
			$days++;
		}

		if (($w = (int) date('w', $last)) < 6)
		{
			// i = number of day, t = number of days to pad
			for ($i = 1, $t = 6 - $w; $t > 0; $t--, $i++)
			{
				// Add next months padding days
				$week[] = array($i, FALSE);
			}

			$month[] = $week;
		}

		return $month;
	}

	public function render()
	{
		$view =  new View('kohana_calendar', array
		(
			'month' => $this->month,
			'year'  => $this->year,
			'weeks' => $this->weeks(),
		));

		return $view->render();
	}

	public function __toString()
	{
		return $this->render();
	}

} // End