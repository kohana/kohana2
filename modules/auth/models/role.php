<?php defined('SYSPATH') or die('No direct script access.');

class Role_Model extends ORM {

	protected $belongs_to_many = array('users');

	/**
	 * Generate a WHERE array.
	 */
	protected function where($id)
	{
		if (($where = parent::where($id)) !== NULL)
			return $where;

		return array('name' => $id);
	}

} // End Role_Model
