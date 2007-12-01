<?php defined('SYSPATH') or die('No direct script access.');

class Role_Model extends ORM {

	protected $belongs_to_many = array('users');

	public function __construct($id = FALSE)
	{
		if ($id != FALSE AND is_string($id))
		{
			// Search by name
			$id = array('name' => $id);
		}

		parent::__construct($id);
	}

	/**
	 * Allows finding roles by name.
	 */
	public function where_key($id = NULL)
	{
		if (is_string($id) AND $id != '')
		{
			return 'name';
		}

		return parent::where_key($id);
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