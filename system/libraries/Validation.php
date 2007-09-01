<?php defined('SYSPATH') or die('No direct access allowed.');

class Validation_Core {

	/**
	 * E-mail validator
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
	 */
	public function valid_email($email)
	{
		return (bool) preg_match('/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/iD', $email);
	}

} // End Validation Class