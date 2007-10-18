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
| variable to an empty string.
|
*/
$config['index_page'] = '';

/*
| -----------------------------------------------------------------------------
| URL Suffix
| -----------------------------------------------------------------------------
|
| You can set an arbitrary filename extension to Kohana URLs. This extension is
| completely freeform, but should start with a period.
|
*/
$config['url_suffix'] = '.html';

/*
| -----------------------------------------------------------------------------
| Global XSS Filtering
| -----------------------------------------------------------------------------
|
| Enable or disable global XSS (Cross-Site-Scripting) attack filtering on all
| user input, include POST, GET, and FILES.
|
*/
$config['global_xss_filtering'] = FALSE;

/*
| -----------------------------------------------------------------------------
| Include Paths
| -----------------------------------------------------------------------------
|
| User Guide: http://kohanaphp.com/user_guide/en/general/configuration.html
|
*/
$config['include_paths'] = array
(
	'modules/user_guide'
);

/*
| -----------------------------------------------------------------------------
| Extension Prefix
| -----------------------------------------------------------------------------
|
| This prefix is used for the filename of your extended libraries, eg:
| MY_Controller.php, MY_Database.php, MY_Validation.php
|
*/
$config['extension_prefix'] = 'MY_';

/*
| -----------------------------------------------------------------------------
| Auto-Load
| -----------------------------------------------------------------------------
|
| You can autoload libraries and models. This feature is not necessary due to
| Kohana's internal auto-loading methods, but you can use it to save a small
| amount of overhead.
|
| Set the class names in a comma separated list, eg:
|
|   'libraries' => 'Database, Validation',
|   'models'    => 'Users, Pages'
|
*/
$config['autoload'] = array
(
	'libraries' => '',
	'models'    => ''
);