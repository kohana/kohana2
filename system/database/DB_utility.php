<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Database Utility Class
 *
 * @category	Database
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/database/
 */
class Core_DB_utility {

	var $db;
	var $data_cache = array();

	/**
	 * Constructor
	 *
	 * Grabs the Core super object instance so we can access it.
	 *
	 */	
	function Core_DB_utility()
	{
		// Assign the main database object to $this->db
		$CORE =& get_instance();
		$this->db =& $CORE->db;
		
		log_message('debug', "Database Utility Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Create database
	 *
	 * @access	public
	 * @param	string	the database name
	 * @return	bool
	 */
	function create_database($db_name)
	{
		$sql = $this->_create_database($db_name);
		
		if (is_bool($sql))
		{
			return $sql;
		}
	
		return $this->db->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Drop database
	 *
	 * @access	public
	 * @param	string	the database name
	 * @return	bool
	 */
	function drop_database($db_name)
	{
		$sql = $this->_drop_database($db_name);
		
		if (is_bool($sql))
		{
			return $sql;
		}
	
		return $this->db->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * List databases
	 *
	 * @access	public
	 * @return	bool
	 */
	function list_databases()
	{	
		// Is there a cached result?
		if (isset($this->data_cache['db_names']))
		{
			return $this->data_cache['db_names'];
		}
	
		$query = $this->db->query($this->_list_databases());
		$dbs = array();
		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$dbs[] = current($row);
			}
		}
			
		$this->data_cache['db_names'] = $dbs;
		return $this->data_cache['db_names'];
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Table
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	bool
	 */
	function optimize_table($table_name)
	{
		$sql = $this->_optimize_table($table_name);
		
		if (is_bool($sql))
		{
			return $sql;
		}
	
		$query = $this->db->query($sql);
		$res = $query->result_array();
		
		// Note: Due to a bug in current() that affects some versions
		// of PHP we can not pass function call directly into it
		return current($res);
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Database
	 *
	 * @access	public
	 * @return	array
	 */
	function optimize_database()
	{
		$result = array();
		foreach ($this->db->list_tables() as $table_name)
		{
			$sql = $this->_optimize_table($table_name);
			
			if (is_bool($sql))
			{
				return $sql;
			}
			
			$query = $this->db->query($sql);
			
			// Build the result array...
			// Note: Due to a bug in current() that affects some versions
			// of PHP we can not pass function call directly into it
			$res = $query->result_array();
			$res = current($res);
			$key = str_replace($this->db->database.'.', '', current($res));
			$keys = array_keys($res);
			unset($res[$keys[0]]);
			
			$result[$key] = $res;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Optimize Table
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	bool
	 */

	function repair_table($table_name)
	{
		$sql = $this->_repair_table($table_name);
		
		if (is_bool($sql))
		{
			return $sql;
		}
	
		$query = $this->db->query($sql);
		
		// Note: Due to a bug in current() that affects some versions
		// of PHP we can not pass function call directly into it
		$res = $query->result_array();
		return current($res);
	}

	// --------------------------------------------------------------------

	/**
	 * Drop Table
	 *
	 * @access	public
	 * @param	string	the table name
	 * @return	bool
	 */
	function drop_table($table_name)
	{
		$sql = $this->_drop_table($table_name);
		
		if (is_bool($sql))
		{
			return $sql;
		}
	
		return $this->db->query($sql);
	}

	// --------------------------------------------------------------------

	/**
	 * Generate CSV from a query result object
	 *
	 * @access	public
	 * @param	object	The query result object
	 * @param	string	The delimiter - tab by default
	 * @param	string	The newline character - \n by default
	 * @return	string
	 */
	function csv_from_result($query, $delim = "\t", $newline = "\n")
	{
		if ( ! is_object($query) OR ! method_exists($query, 'field_names'))
		{
			show_error('You must submit a valid result object');
		}	
	
		$out = '';
		
		// First generate the headings from the table column names
		foreach ($query->list_fields() as $name)
		{
			$out .= $name.$delim;
		}
		
		$out = rtrim($out);
		$out .= $newline;
		
		// Next blast through the result array and build out the rows
		foreach ($query->result_array() as $row)
		{
			foreach ($row as $item)
			{
				$out .= $item.$delim;			
			}
			$out = rtrim($out);
			$out .= $newline;
		}

		return $out;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Generate XML data from a query result object
	 *
	 * @access	public
	 * @param	object	The query result object
	 * @param	array	Any preferences
	 * @return	string
	 */
	function xml_from_result($query, $params = array())
	{
		if ( ! is_object($query) OR ! method_exists($query, 'field_names'))
		{
			show_error('You must submit a valid result object');
		}
		
		// Set our default values
		foreach (array('root' => 'root', 'element' => 'element', 'newline' => "\n", 'tab' => "\t") as $key => $val)
		{
			if ( ! isset($params[$key]))
			{
				$params[$key] = $val;
			}
		}
		
		// Create variables for convenience
		extract($params);
			
		// Load the xml helper
		$CORE =& get_instance();
		$CORE->load->helper('xml');

		// Generate the result
		$xml = "<{$root}>".$newline;
		foreach ($query->result_array() as $row)
		{
			$xml .= $tab."<{$element}>".$newline;
			
			foreach ($row as $key => $val)
			{
				$xml .= $tab.$tab."<{$key}>".xml_convert($val)."</{$key}>".$newline;
			}
			$xml .= $tab."</{$element}>".$newline;
		}
		$xml .= "</$root>".$newline;
		
		return $xml;
	}

	// --------------------------------------------------------------------

	/**
	 * Database Backup
	 *
	 * @access	public
	 * @return	void
	 */
	function backup($params = array())
	{
		// If the parameters have not been submitted as an
		// array then we know that it is simply the table
		// name, which is a valid short cut.
		if (is_string($params))
		{
			$params = array('tables' => $params);
		}
		
		// ------------------------------------------------------
	
		// Set up our default preferences
		$prefs = array(
							'tables'		=> array(),
							'ignore'		=> array(),
							'filename'		=> '',
							'format'		=> 'gzip', // gzip, zip, txt
							'add_drop'		=> TRUE,
							'add_insert'	=> TRUE,
							'newline'		=> "\n"
						);

		// Did the user submit any preferences? If so set them....
		if (count($params) > 0)
		{
			foreach ($prefs as $key => $val)
			{
				if (isset($params[$key]))
				{
					$prefs[$key] = $params[$key];
				}
			}
		}

		// ------------------------------------------------------

		// Are we backing up a complete database or individual tables?	
		// If no table names were submitted we'll fetch the entire table list
		if (count($prefs['tables']) == 0)
		{
			$prefs['tables'] = $this->db->list_tables();
		}
		
		// ------------------------------------------------------

		// Validate the format
		if ( ! in_array($prefs['format'], array('gzip', 'zip', 'txt'), TRUE))
		{
			$prefs['format'] = 'txt';
		}

		// ------------------------------------------------------

		// Is the encoder supported?  If not, we'll either issue an
		// error or use plain text depending on the debug settings
		if (($prefs['format'] == 'gzip' AND ! @function_exists('gzencode'))
		 OR ($prefs['format'] == 'zip'  AND ! @function_exists('gzcompress')))
		{
			if ($this->db->db_debug)
			{
				return $this->db->display_error('db_unsuported_compression');
			}
		
			$prefs['format'] = 'txt';
		}

		// ------------------------------------------------------

		// Set the filename if not provided - Only needed with Zip files
		if ($prefs['filename'] == '' AND $prefs['format'] == 'zip')
		{
			$prefs['filename'] = (count($prefs['tables']) == 1) ? $prefs['tables'] : $this->db->database;
			$prefs['filename'] .= '_'.date('Y-m-d_H-i', time());
		}

		// ------------------------------------------------------
				
		// Was a Gzip file requested?
		if ($prefs['format'] == 'gzip')
		{
			return gzencode($this->_backup($prefs));
		}

		// ------------------------------------------------------
		
		// Was a text file requested?
		if ($prefs['format'] == 'txt')
		{
			return $this->_backup($prefs);
		}

		// ------------------------------------------------------

		// Was a Zip file requested?		
		if ($prefs['format'] == 'zip')
		{
			// If they included the .zip file extension we'll remove it
			$prefs['filename'] = preg_replace('/\.zip$/i', '', $prefs['filename']);
			
			// Tack on the ".sql" file extension if needed
			if ( ! preg_match('/\.sql$/i', $prefs['filename']))
			{
				$prefs['filename'] .= '.sql';
			}

			// Load the Zip class and output it
			
			$CORE =& get_instance();
			$CORE->load->library('zip');
			$CORE->zip->add_data($prefs['filename'], $this->_backup($prefs));							
			return $CORE->zip->get_zip();
		}
		
	}






}

?>