<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| TABLE RELATIONSHIPS
| -------------------------------------------------------------------
| This file specifies relationships between fields and tables. This
| configuration is used for ORM.
|
| -------------------------------------------------------------------
| Instructions
| -------------------------------------------------------------------
|
| Please define each of the table relationships using the following
| keywords:
|
| belongs_to      - object "belongs to" another object
| has_one         - object "has one" child object
| has_many        - object "has many" child objects
| belongs_to_many - object "belongs to many" objects
| alias_for       - object is an "alias for" another object
|
*/

$config = array(
	// Backends
	'backend' => array(
		'belongs_to_many' => array('sites')),
	// Contracts
	'contract' => array(
		'belongs_to'      => array('customer', 'vendor'),
		'has_one'         => array('status', 'service')),
	// Customers
	'customer' => array(
		'has_many'        => array('contacts', 'users'),
		'has_one'         => array('status')),
	// Groups
	'group'    => array(
		'belongs_to_many' => array('users')),
	// Notes
	'note'     => array(
		'belongs_to'      => array('task', 'user')),
	// Services
	'service'  => array(
		'has_one'         => array('category')),
	// Sites
	'site'     => array(
		'belongs_to'      => array('contract'),
		'has_one'         => array('domain', 'backend')),
	// Tasks
	'task'     => array(
		'belongs_to'      => array('contract')),
	// Users
	'user'     => array(
		'belongs_to'      => array('customer'),
		'has_many'        => array('groups')),
	// Vendor
	'vendor'   => array(
		'belongs_to'      => array('vendor'),
		'has_many'        => array('vendors'))
);

?>