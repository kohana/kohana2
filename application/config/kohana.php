<?php

/**
 * Additional resource paths, or "modules". Each path can either be absolute
 * or relative to the DOCROOT. Modules can include any resource that can exist
 * in your application directory, configuration files, controllers, views, etc.
 */
$config['modules'] = array
(
	// MODPATH.'archive',        // Archive utility
	// MODPATH.'auth',           // Authentication
	// MODPATH.'documentation',  // Kohana documentation
	// MODPATH.'gmaps',          // Google Maps integration
	// MODPATH.'object_db',      // New OOP Database library (testing only!)
	// MODPATH.'payment',        // Online payments
	// MODPATH.'smarty',         // Smarty templating
	// MODPATH.'unittest',       // Unit testing
);

/**
 * Default language locale name(s).
 * First item must be a valid i18n directory name, subsequent items are alternative locales
 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
 * @see http://php.net/setlocale
 */
$config['locale'] = array('en_US.UTF-8', 'English_United States');

/**
 * Locale timezone. Defaults to use the server timezone.
 * @see http://php.net/timezones
 */
$config['timezone'] = '';

/**
 * Enable or disable hooks, raw PHP files that are included during setup.
 *
 *     $config['enable_hooks'] = FALSE;
 *
 * Disabled by default, hooks allow you to change default Events, run custom
 * code, and extend Kohana in completely custom ways.
 */
$config['enable_hooks'] = FALSE;

/**
 * Length of internal configuration, language, and include path caching.
 *
 *    $config['caching'] = FALSE;
 *
 * Disabled by default, internal caching can give significant speed improvements
 * at the expense of configuration changes being visibly delayed. Enabling
 * short (30-300) seconds of internal caching on production sites is a highly
 * recommended way to increase performance.
 */
$config['caching'] = FALSE;
