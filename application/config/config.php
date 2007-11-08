<?php defined('SYSPATH') or die('No direct script access.');
/*
 * File: Config
 *  This configuration file is unique to every application.
 *
 * Options:
 *  site_domain          - domain and installation directory
 *  site_protocol        - protocol used to access the site, usually HTTP
 *  index_page           - name of the front controller, can be removed with URL rewriting
 *  url_suffix           - an extension that will be added to all generated URLs
 *  allow_config_set     - enable or disable setting of Config items
 *  global_xss_filtering - enable or disable XSS attack filtering on all user input
 *  extension_prefix     - filename prefix for library extensions
 *  include_paths        - extra Kohana resource paths, see <Kohana.find_file>
 *  autoload             - libraries and models to be loaded with the controller
 */
$config = array
(
	'site_domain'          => 'localhost/kohanaphp.com/',
	'site_protocol'        => 'http',
	'index_page'           => 'index.php',
	'url_suffix'           => '.html',
	'allow_config_set'     => FALSE,
	'global_xss_filtering' => FALSE,
	'extension_prefix'     => 'MY_',
	'include_paths'        => array
	(
	),
	'autoload'             => array
	(
		'libraries' => '',
		'models'    => ''
	)
);