<?php defined('SYSPATH') or die('No direct script access.');

class Newsletter_Model extends ORM {

	protected $_relationships = array
	(
		'has_many' => array('subscribers:subscriptions')
	);

}