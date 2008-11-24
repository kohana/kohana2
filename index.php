<?php

$application = 'application';

$modules = 'modules';

$system = 'system';

define('EXT', '.php');

$fc = pathinfo(__FILE__);

define('DOCROOT', str_replace('\\', '/', $fc['dirname']).'/');
define('APPPATH', str_replace('\\', '/', realpath($application)).'/');
define('MODPATH', str_replace('\\', '/', realpath($modules)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($system)).'/');

unset($fc, $application, $modules, $system);

if (file_exists('install'.EXT))
{
	// Installation check
	include 'install'.EXT;
}
elseif (file_exists(APPPATH.'bootstrap'.EXT))
{
	// Custom boostrap
	include APPPATH.'bootstrap'.EXT;
}
else
{
	// Default bootstrap
	include SYSPATH.'bootstrap'.EXT;
}
