<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Loader extends CI_Loader {

	function MY_Loader()
	{
		parent::CI_Loader();
	}

	/**
	 * ORM Loader
	 *
	 * This function lets users load ORM for a specific table
	 *
	 * @access	public
	 * @param	string the name of the table
	 * @param	mixed  WHERE clause
	 * @return	void
	 */
	function orm($table, $where = FALSE)
	{
		if ($table == FALSE)
			return FALSE;

		$this->helper('inflector');
		$this->config('relationships', TRUE);
		if ( ! class_exists('CI_DB'))
		{
			$this->database();
		}

		require_once($this->_ci_find_class('ORM'));

		$model = new Core_ORM($table);
		if ($where != FALSE)
		{
			$model->get($where);
		}

		return $model;
	}

}

// END class MY_Loader
?>