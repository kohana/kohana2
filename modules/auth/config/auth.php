<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Authentication configuration options.
 *
 * Parameters:
 *  hash_method  - hash type used for passwords, see <http://php.net/hash_algos>
 *  salt_pattern - character offsets to place the random salt at
 */
$config = array
(
	'user_table'   => 'users',
	'role_table'   => 'roles',
	'hash_method'  => 'sha1',
	'salt_pattern' => '1, 3, 5, 9, 14, 15, 20, 21, 28, 30'
);