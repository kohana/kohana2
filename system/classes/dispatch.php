<?php defined('SYSPATH') or die('No direct access');

class dispatch_Core {

	public static function get($uri)
	{
		if ($route = Router::find_route($uri))
		{
			if (isset($route['prefix']['controller']))
			{
				$class = $route['prefix']['controller'].$route['controller'];
			}
			else
			{
				$class = $route['controller'];
			}

			// Clear all events
			Event::clear('dispatch.pre_controller');
			Event::clear('dispatch.post_controller');

			try
			{
				$class = new ReflectionClass('Controller_'.ucfirst($class));

				$method = $class->getMethod($route['method']);

				Event::run('dispatch.pre_controller');

				$controller = $class->newInstance();

				ob_start();

				$method->invokeArgs($controller, $route['arguments']);

				Event::run('dispatch.post_controller');

				return ob_get_clean();

			}
			catch (Exception $e)
			{
				return '<div class="error-404">'.$e->getMessage().'</div>';
			}
		}
	}

} // End dispatch
