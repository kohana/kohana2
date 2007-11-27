<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Site-specific application configuration is done here. This configuration file
 * does not extend any other configuration file. All of the items here are prefixed
 * with "core", eg: core.index_page would fetch the index_page variable from the
 * configuration array.
 *
 * Options:
 *  site_domain          - domain and installation directory
 *  site_protocol        - protocol used to access the site, usually HTTP
 *  index_page           - name of the front controller, can be removed with URL rewriting
 *  url_suffix           - an extension that will be added to all generated URLs
 *  output_compression   - enable or disable gzip output compression
 *  global_xss_filtering - enable or disable XSS attack filtering on all user input
 *  allow_config_set     - enable or disable setting of Config items
 *  extension_prefix     - filename prefix for library extensions
 *  include_paths        - "module" support, additional resource paths that will be searched
 *  autoload             - libraries and models to be loaded with the controller
 */
$config = array
(
	'site_domain'          => 'localhost/kohana/',
	'site_protocol'        => 'http',
	'index_page'           => 'index.php',
	'url_suffix'           => '',
	'output_compression'   => FALSE,
	'global_xss_filtering' => FALSE,
	'allow_config_set'     => FALSE,
	'extension_prefix'     => 'MY_',
	'include_paths'        => array
	(
		// To enable the demo module, uncomment the following line
		// 'modules/demo',

		// To enable local API documentation at /kodoc/, uncomment the following line
		// 'modules/kodoc',
	),
	'autoload'             => array
	(
		'libraries' => '',
		'models'    => ''
	),
);