<?php defined('SYSPATH') or die('No direct script access.');

class Donation_Model extends ORM {

	protected $sorting = array('date' => 'desc');

	public function save()
	{
		if ( ! $this->loaded)
		{
			// Set the current date
			$this->date = time();
		}

		return parent::save();
	}

} // End Donation