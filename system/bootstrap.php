<?php

require SYSPATH.'classes/kohana'.EXT;

Kohana::init();

/*
$route = Route::factory('(:controller(/:method(/:id)))')
	->defaults(array('controller' => 'welcome', 'method' => 'index'));


echo Kohana::debug($route->matches('uploads/doc/foo.xml'));
*/
$route = Route::factory('(:path/):file(.:format)', array('path' => '.*'));

$view = View::factory('test');

utf8::clean_globals();

echo $view->render();