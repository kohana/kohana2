<?php defined('SYSPATH') or die('No direct script access.');

class Video_Tutorial_Model extends ORM {

	public function save()
	{
		if (empty($this->object->created))
		{
			// Set the time to the current UNIX timestamp
			$this->object->created = time();
		}

		return parent::save();
	}

	protected function where_key($id)
	{
		if ( ! empty($id) AND is_string($id) AND ! ctype_digit($id))
		{
			// Allow searches by video
			return 'video';
		}

		return parent::where_key();
	}

} // End