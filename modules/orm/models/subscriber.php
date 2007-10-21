<?php defined('SYSPATH') or die('No direct script access.');

class Subscriber_Model extends ORM {

	protected $_relationships = array
	(
		'belongs_to_many' => array('newsletters:subscriptions')
	);

}