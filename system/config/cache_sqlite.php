<?php defined('SYSPATH') or die('No direct script access.');

$config['schema'] =
'CREATE TABLE caches(
	id varchar(127),
	hash char(40),
	tags varchar(255),
	expiration int,
	cache blob);';