<?php defined('SYSPATH') or die('No direct script access.');

/*
| -------------------------------------------------------------------------
| Site Domain
| -------------------------------------------------------------------------
|
| Domain part of the URL to your Kohana installation, with the install path.
|
| Example: your-site.com/kohana/
|
*/
$config['site_domain'] = $_SERVER['SERVER_NAME'].'/kohana/';

/*
| -----------------------------------------------------------------------------
| Site Protocol
| -----------------------------------------------------------------------------
| 
| Protocol part of the URL to your Kohana installation.
| 
| Note: This will almost always be "http" or "https"
|
*/
$config['site_protocol'] = 'http';

/*
| -----------------------------------------------------------------------------
| Index File
| -----------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you've renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config['index_page'] = 'index.php';

$config['url_suffix'] = '.html';

$config['permitted_uri_chars'] = 'a-z 0-9~%.:_-';

$config['locale'] = 'en';

$config['include_paths'] = array
(
	'modules/user_guide'
);

$config['enable_hooks'] = FALSE;

$config['extension_prefix'] = 'MY_';

$config['timezone'] = '';

$config['autoload'] = array
(
	'libraries' => '',
	'models'    => ''
);