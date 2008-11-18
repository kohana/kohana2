<?php
/**
 * Base URL path of the website, including domain.
 *
 *     $config['site_domain'] = '/kohana/';
 *
 * If the site_domain contains a domain, eg: wwww.example.com/kohana/, then a
 * full URL, including the protocol and domain will be generated. If set to a
 * a path, generated URLs will not contain a domain name. (See exception in
 * [site_protocol][ref-sip] below.)
 */
$config['site_domain'] = '/kohana/';

/**
 * Set a default protocol protocol for this application.
 *
 *     $config['site_protocol'] = '';
 *
 * If no site_protocol is specified, then the current protocol will be detected.
 * This setting must be left empty if you do not want generated URLs to contain
 * the domain name.
 */
$config['site_protocol'] = '';

/**
 * Name of the front controller for this application.
 *
 *     $config['index_page'] = 'index.php';
 *
 * If the front controller is removed from the URL using [rewriting][ref-url],
 * this setting must be set to an empty string, or generated URLs will still
 * contain the index_page filename.
 *
 * [ref-url]: http://doc.kohanaphp.com/routing
 */
$config['index_page'] = 'index.php';

/**
 * Enable or disable gzip output compression.
 *
 *     $config['output_compression'] = FALSE;
 *
 * Disabled by default, gzip output compression can significantly increase page
 * latency by decreasing server bandwidth usage, at the cost of slightly higher
 * CPU usage. A number from 1-9 can be used to set the compression level, or
 * TRUE can be used to use the PHP default.
 *
 * **Do not enable this if PHP output compression is enabled in php.ini!**
 */
$config['output_compression'] = FALSE;

/**
 * Enable or disable statistics in the final output.
 *
 *     $config['render_stats'] = TRUE;
 *
 * Enabled by default, this will replace specific strings in generated output
 * with generated statistics or information.
 *
 * {execution_time}
 * :  Total execution time in seconds
 *
 * {memory_usage}
 * :  Total memory usage in megabytes (MB)
 *
 * {included_files}
 * :  All of the filenames that are currently loaded
 *
 * {kohana_version}
 * :  The Kohana release version number
 *
 * {kohana_codename}
 * :  The Kohana release code name
 *
 * This setting can be disabled for a small performance increase.
 */
$config['render_stats'] = TRUE;

/**
 * Enable or disable global XSS filtering of GET, POST, and SERVER data.
 *
 *    $config['global_xss_filtering'] = TRUE;
 *
 * Enabled by default, global XSS filtering prevents client-side output attacks.
 * This can either be TRUE or 'htmlpurifier' to use [HTMLPurifier][ref-hpr].
 */
$config['global_xss_filtering'] = TRUE;

/**
 * Enable or disable displaying of Kohana error pages. This will not affect
 * logging. Turning this off will disable ALL error pages.
 */
$config['display_errors'] = TRUE;
