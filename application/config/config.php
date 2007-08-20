<?php defined('SYSPATH') or die('No direct script access.');

$config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].'/kfsk/';

$config['index_page'] = '';

$config['url_suffix'] = '.html';

$config['permitted_uri_chars'] = 'a-z 0-9~%.:_-';

$config['locale'] = 'en';

$config['include_paths'] = array
(
	'modules/user_guide'
);

$config['enable_hooks'] = FALSE;

$config['subclass_prefix'] = 'MY_';
