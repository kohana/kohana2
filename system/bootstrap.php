<?php

require SYSPATH.'classes/kohana'.EXT;

Kohana::init();

$route = Route::factory('(:controller(/:method(/:id)))')
	->defaults(array('controller' => 'welcome', 'method' => 'index'));

$route = Route::factory('(:path/):file(.:format)', array('path' => '.*'));

echo Kohana::debug($route->matches('uploads/doc/foo.xml'));