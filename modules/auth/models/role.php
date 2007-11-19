<?php defined('SYSPATH') or die('No direct script access.');

class Role_Model extends ORM {

	protected $belongs_to_many = array('users');

	protected function where($id)
	{
		if (($where = parent::where($id)) !== NULL)
			return $where;

		return array('name' => $id);
	}

	/**
	 * Removes all user<>role relationships for this object when deleted.
	 */
	public function delete()
	{
		// Set WHERE before deleting, to access the object id
		$where = array($this->class.'_id' => $this->object->id);

		// Related table name
		$table = $this->related_table('users');

		if ($return = parent::delete())
		{
			// Delete the many<>many relationships for users<>roles
			self::$db
				->where($where)
				->delete($table);
		}

		return $return;
	}

} // End Role_Model
