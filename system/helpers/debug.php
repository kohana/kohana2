<?php
/**
 * Debug output helper
 *
 * $Id$
 *
 * @package    Text Helper
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class debug_Core
{
	/** The combined text output from the dump() method. 
	 */
	public static $output = '';
	
	/**
	 * Dumps the output of a specified variable, equivalent to var_dump()
	 *
	 * @param   mixed    The value to dump
	 */
	public static function dump($data)
	{
		// exit out if we don't display errors
		static $enabled;
		(isset($enabled)) OR $enabled = Config::item('config.display_errors');
		if (!$enabled) 
			return;
		
		static $hasKrumo = false;
		static $addedEvent = false;
		
		// capture the output to display later
		ob_start();
		
		// try using krumo
		if ($hasKrumo) 
		{
			try 
			{
				!defined('KRUMO_DIR') AND define('KRUMO_DIR', MODPATH.'debug_krumo/vendor/');
				Krumo::dump($data);
			} 
			catch (Kohana_Exception $ex) 
			{
				$hasKrumo = false;
			}
		}
		
		// if krumo failed or is not enabled, use var_dump
		if (!$hasKrumo) 
		{
			echo "\n<div style=\"border:2px solid black;\">\n<pre>";
			var_dump($data);
			echo "</pre>\n";
			
			$backtrace = debug_backtrace();
			while($d = array_pop($backtrace)) 
			{
				if ((strToLower(@$d['class']) == 'debug') || (strToLower(@$d['class']) == 'debug_core')) 
				{
					break;
				}
			}
			echo "<em>Called from <code>".$d['file']."</code>, line <code>".$d['line']."</code></em>".
			     "\n</div>\n";
		}
		
		$contents = ob_get_contents();
		
		ob_end_clean();
		
		if (empty($contents)) 
			return;
				
		self::$output .= $contents;
		
		if (!$addedEvent) 
		{
			Event::add('system.display', array('debug_Core', '_display'));
			$addedEvent = true;
		}
	}
	
	public static function _display()
	{
		if (!empty(debug::$output)) 
		{
			$output = '<div id="kohana_debug_output">'.
			          debug::$output.
					  '</div>';
			
			if (stripos(Event::$data, '</body') !== false) 
			{
				// add the data before the </body> tag
				Event::$data = str_ireplace('</body', $output.'</body', Event::$data);
			} 
			else 
			{
				// just append to the end
				Event::$data .= $output;
			}
		}
	}
}