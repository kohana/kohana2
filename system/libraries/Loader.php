<?php defined('SYSPATH') or die('No direct access allowed.');

class Core_Loader {

	function library($name)
	{
		Kohana::$instance->$name = Kohana::load_class($name);
	}
	
	function helper($name)
	{
		include Kohana::find_file('helpers', $name, TRUE);
	}
	
	function model($name)
	{
		Kohana::$instance->$name = Kohana::load_class($name.'_Model');
	}
	
	// Weird prefixes to prevent collisions
	function view($kohana_name, $kohana_data = array(), $kohana_return = FALSE)
	{
		$kohana_filename = Kohana::find_file('views', $name, TRUE);
		
		ob_start();
		extract($kohana_data);
		include $kohana_filename;
		$kohana_output = ob_get_contents();
		ob_end_clean();
		
		if ($kohana_return == TRUE)
		{
			return $kohana_output;
		}
		else
		{
			print $kohana_output;
		}
	}

}

?>