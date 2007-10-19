<?php defined('SYSPATH') or die('No direct script access.');

class User_Model extends ORM {

	protected $_relationships = array
	(
		'has_one'  => array('group'),
		'has_many' => array('newsletters')
	);

}