<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		BlueFlame
 * @author		Rick Ellis
 * @copyright	Copyright (c) 2006, EllisLab, Inc.
 * @license		http://www.codeignitor.com/user_guide/license.html
 * @link		http://www.codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Model class for Object Relational Mapping (ORM)
 *
 * @package     BlueFlame
 * @subpackage  Libraries
 * @category    Libraries
 * @author      Woody Gilk
 * @link        http://www.ciforge.com/trac/blueflame/wiki/ORM
 */

class Core_ORM {

	// CI Database object
	public $db;
	// Table information
	protected $prefix;
	protected $table;
	protected $fields;
	// Table relationships
	protected $relationships;
	// Current object data
	private $table_data;
	// Must always be FALSE here
	private $model_loaded = FALSE;
	private $model_parent = FALSE;
	private $model_cache  = array();
	private $model_errors = array();

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($table)
	{
		// Need access to some CI things
		$CI =& get_instance();
		$CI->load->helper('inflector');

		$this->db =& $CI->db;
		// Set table prefix
		$this->prefix = $table.'_';
		$this->table  = plural($table);
		// Load model cache from database
		$this->_load_cache();
		// Set field variables
		foreach ($this->_fields() as $field)
		{
			$this->$field = FALSE;
		}
		// Loading complete
		log_message('debug', 'ORM Model Initialized for '.ucfirst($this->table)).' table.';
	}

	// --------------------------------------------------------------------

	/**
	 * Debugging method
	 *
	 * Recursively prints the object and it's children
	 *
	 * @access	public
	 * @param	bool
	 * @return 	bool
	 */
	public function debug($recursion = FALSE)
	{
		if ($recursion == FALSE)
		{
			ob_start();
			print '<pre>';
			print '<b>DEBUG &quot;'.$this->_table().'&quot;</b>'."\n\n";
		}

		foreach($this->_fields() as $field)
		{
			print sprintf('%-30s', $field);
			$s = 0;
			while ($str = substr($this->$field, $s, 50))
			{
				if ($s >= 50)
				{
					print "\n&raquo;".str_repeat(' ', 29);
				}
				print trim($str);

				$s += 50;
			}

			print "\n";
		}
		print "\n\n";

		if (count($children = $this->_children()) > 0)
		{
			foreach($children as $child)
			{
				print '<b>CHILD &quot;'.$child.'&quot;</b>'."\n\n";
				$this->$child->debug(TRUE);
			}
		}

		if ($recursion == FALSE)
		{
			print '</pre>'."\n";
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Return Errors
	 *
	 * Return the error messages array
	 *
	 * @access	public
	 * @param	string	start tag
	 * @param	string	end tag
	 * @return 	mixed
	 */
	public function error_messages($open = FALSE, $close = FALSE)
	{
		if ($open != FALSE AND $close != FALSE AND count($this->model_errors) > 0)
		{

			foreach($this->model_errors as $error)
			{
				$func  = substr($error, 0, strpos($error, ' '));
				$error = substr($error, strlen($func)+2);
				$return[] = $open.'<strong>'.$func.'</strong> '.$error.$close;
			}
		}
		else
		{
			$return = $this->model_errors;
		}

		return (array) $return;
	}

	// --------------------------------------------------------------------

	/**
	 * __call Overloading
	 *
	 * Proxying for undefined methods
	 *
	 * @access	public
	 * @param	string	method to call
	 * @param	array	arguments
	 * @return 	mixed
	 */
	public function __call($method, $args = array())
	{

		$return = FALSE;
		$args   = (array) $args;

		if (count($args) < 1)
		{
			$CI =& get_instance();
			if ($this->_relationship('has_many', $method))
			{
				$id = $this->prefix.'id';
				$fd = singular($method).'_id';
				$model = $CI->load->orm(singular($method));
				if (in_array($id, $model->_fields()))
				{
					$table = $model->_table();
					$this->db->where(sprintf('%s.%s', $table, $id), $this->$id);
					$return = $model->all();
				}
				else
				{
					$table = $this->_table().'_'.$model->_table();
					$this->db->where(sprintf('%s.%s', $table, $id), $this->$id);
					$this->db->join($model->_table(), sprintf('%s.%s = %s.%s', $table, $fd, $method, $fd), 'left');
					$return = $model->all(0, 0, $table);
				}
			}
		}
		elseif (preg_match('/\bget_/', $method))
		{
			$key = substr($method, 4);
			$val = $args[0];

			$return = $this->get(array($key => $val));
		}
		elseif (preg_match('/\bfind_/', $method, $x) OR preg_match('/\bsearch_/', $method, $x))
		{
			$key = substr($method, strlen($x[0]));
			$val = $args[0];

			$return = $this->find(array($key => $val));
		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * __get Overloading
	 *
	 * Proxying for object variable fetching requests
	 *
	 * @access	public
	 * @param	string	variable name
	 * @return 	bool
	 */
	public function &__get($key)
	{
		$key = (string) $key;

		if (isset($this->table_data[$key]))
		{
			$value = $this->table_data[$key];
		}
		elseif (isset($this->$key))
		{
			$value =& $this->$key;
		}
		else
		{
			$value = NULL;
		}

		return $value;
	}

	// --------------------------------------------------------------------

	/**
	 * __set Overloading
	 *
	 * Proxying for object variable setting requests
	 *
	 * @access	public
	 * @param	string	variable name
	 * @param	mixed	variable value
	 * @return 	bool
	 */
	public function __set($key, $value)
	{
		$key = (string) $key;

		if (in_array($key, $this->_fields()))
		{
			$allow = TRUE;
			$value = (string) $value;

			if ($this->model_parent == FALSE)
			{
				if ($key == $this->prefix.'id' AND $this->table_data[$key] > 0)
				{
					$allow = FALSE;
				}
				elseif (preg_match('/(.+)_id\b/', $key, $child) AND in_array($child[1], $this->_children()))
				{
					$child = $child[1];
					if (is_object($this->$child) AND $this->$child->$key != $value)
					{
						$this->$child->get($value);
						$value = $this->$child->$key;
					}
				}
			}
			else
			{
				$allow = FALSE;
			}

			if ($allow === TRUE)
			{
				$this->table_data[$key] = $value;
			}
		}
		elseif(isset($this->$key) OR in_array($key, $this->_children()))
		{
			$allow = FALSE;
			if (in_array($key, $this->_children()))
			{
				$allow = TRUE;
			}
			elseif (in_array($key, array('db', 'prefix', 'table')))
			{
				if ($this->$key == NULL)
				{
					$allow = TRUE;
				}
			}
			elseif (substr($key, 0, 6) == 'model_')
			{
				if ( ! is_array($this->$key) AND $this->$key == FALSE)
				{
					$allow = TRUE;
				}
			}

			if ($allow === TRUE)
			{
				$this->$key = $value;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * __isset Overloading
	 *
	 * Proxying for object variable isset requests
	 *
	 * @access	public
	 * @param	string	variable name
	 * @return 	bool
	 */
	public function __isset($key)
	{
		$key = (string) $key;
		return ((isset($this->$key) OR isset($this->table_data[$key])) ? TRUE : FALSE);
	}

	// --------------------------------------------------------------------

	/**
	 * __unset Overloading
	 *
	 * Proxying for object variable unset requests
	 *
	 * @access	public
	 * @param	string	variable name
	 * @return 	bool
	 */
	public function __unset($key)
	{
		$key = (string) $key;

		if (isset($this->$key))
		{
			unset($this->$key);
		}
		elseif (isset($this->table_data[$key]))
		{
			unset($this->table_data[$key]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Simple function
	 *
	 * This function loads the requested object
	 *
	 * @access	public
	 * @param	mixed	an array or matches, or a numeric id
	 * @return 	bool
	 */
	public function initialize($parent = FALSE)
	{
		$data = $this->_data();
		$this->_cache($data);
		$this->model_loaded = TRUE;
		if ($parent != FALSE)
		{
			$this->model_parent = $parent;
		}

		$children = $this->_children();
		if (count($children) > 0)
		{
			foreach($children as $child)
			{
				if ( ! is_object($this->$child))
				{
					$this->_error(__FUNCTION__, 'Unexpected format for child '.$child);
				}
				else
				{
					$this->$child->initialize($this->table);
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Find a single subject
	 *
	 * This function loads the requested object
	 *
	 * @access	public
	 * @param	mixed	an array or matches, or a numeric id
	 * @return 	bool
	 */
	public function create_new()
	{
		$this->table_data = array();
		foreach($this->_fields() as $field)
		{
			$this->table_data[$field] = '';
		}

		$this->initialize();
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Find a single subject
	 *
	 * This function loads the requested object
	 *
	 * @access	public
	 * @param	mixed	an array or matches, or a numeric id
	 * @return 	bool
	 */
	public function all($limit = '0', $offset = '0', $table = FALSE)
	{
		$table = ($table == FALSE) ? $this->table : $table;
		$this->db->from($table);
		return $this->_query($limit, $offset, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Get a single subject
	 *
	 * This function loads the requested object, with WHERE
	 *
	 * @access	public
	 * @param	mixed	an array or matches, or a numeric id
	 * @return 	bool
	 */
	public function get($subject = FALSE, $limit = '1', $offset = '0')
	{
		if ($subject === FALSE)
			return FALSE;
		// Convert to array
		$subject = $this->_subject($subject);

		$where = array();
		foreach ($subject as $key => $val)
		{
			// Remove comparisons from the key name
			$var = trim(str_replace(array('<', '>', '=', '!', '%', '$'), '', $key));
			// If the key is not set, then the query will fail
			if ( ! isset($this->$var))
				return FALSE;

			$where[sprintf('%s.%s', $this->table, $key)] = $val;
		}

		$this->db->from($this->table);
		$this->db->where($where);
		return $this->_query($limit, $offset);
	}

	// --------------------------------------------------------------------

	/**
	 * Find a single subject
	 *
	 * This function loads the requested object, with FIND
	 *
	 * @access	public
	 * @param	mixed	an array of matches, or an id
	 * @return 	bool
	 */
	public function find($subject = FALSE, $limit = '1', $offset = '0')
	{
		if ($subject === FALSE)
			return FALSE;

		$like = array();
		foreach ($this->_subject($subject) as $key => $val)
		{
			// If the key is not set, then the query will fail
			if ( ! isset($this->$key))
				return FALSE;

			$like[sprintf('%s.%s', $this->table, $key)] = $val;
		}

		$this->db->from($this->table);
		$this->db->like($like);
		return $this->_query($limit, $offset);
	}

	// --------------------------------------------------------------------

	/**
	 * Save the current object
	 *
	 * Save the current object back into the database
	 *
	 * @access	public
	 * @return 	bool
	 */
	public function save()
	{
		if ($this->model_parent != FALSE)
		{
			return $this->_error(__FUNCTION__, 'Child models must be disconnected before saving.');
		}

		$id = $this->prefix.'id';
		$action = ($this->$id == FALSE) ? 'insert' : 'update';
		// Get data and cache for comparison
		$data = $this->_data();
		// Remove duplicate data
		if ($action == 'update')
		{
			foreach($this->_cache() as $key => $val)
			{
				if ($key == $id OR $data[$key] == $val)
				{
					unset($data[$key]);
				}
			}
		}

		if (count($data) < 1)
		{
			return $this->_error(__FUNCTION__, 'No data changes made before attempting to save');
		}

		// If updating, we need to set a WHERE
		if ($action == 'update')
		{
			$this->db->where($id, $this->$id);
		}

		$success = $this->db->$action($this->table, $data);
		if ($success == TRUE)
		{
			// Reload all the local variables after insert
			if ($action == 'insert' AND $insert_id = $this->db->insert_id())
			{
				$query = $this->db->getwhere($this->table, array($id => $insert_id), 1);
				// No rows mean that something failed
				if ($query->num_rows() < 1)
					return FALSE;

				// Set the variables
				foreach ($query->row_array() as $key => $val)
				{
					$this->table_data[$key] = $val;
				}
				// Clean up
				$query->free_result();
			}
			// Cache the current data
			$this->_cache($this->_data());
			return TRUE;
		}

		return $success;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete an Object
	 *
	 * This will delete the currently loaded object
	 *
	 * @access	public
	 * @return 	bool
	 */
	public function delete()
	{
		$id = $this->prefix.'id';
		if ($this->$id == FALSE)
			return FALSE;

		$this->db->where($id, $this->$id);
		$success = $this->db->delete($this->table);
		if ($success == TRUE)
		{
			foreach($this->_fields() as $field)
			{
				$this->field = FALSE;
			}
		}

		return $success;
	}

	// --------------------------------------------------------------------

	/**
	 * Sync Database
	 *
	 * Regenerate the current model's database table based on the definition
	 *
	 * @access	public
	 * @return 	bool
	 */
	public function sync_database()
	{
		if ($this->db->table_exists($this->_table()))
		{
			return $this->_error(__FUNCTION__, 'Table "'.$this->_table().'" already exists.');
		}
		else
		{
			$esc = ($this->db->dbdriver == 'postgre') ? '"' : '`';
			$table = $this->_generate_table($esc);
			// Create the actual query that's used
			$query = 'CREATE TABLE '.$esc.$this->_table().$esc.' ('."\n"
			       . "\t".implode(",\n\t", $table['columns'])."\n"
			       . ') COMMENT="'.$table['comment'].'";';
			// Return result from simple query
			return $this->db->simple_query($query);
		}
	}

	private function _load_cache($rebuild = FALSE)
	{
		static $cache;

		if ($cache == NULL)
		{
			$debug = $this->db->db_debug;
			$this->db->db_debug = FALSE;

			if ($query = $this->db->get('orm_tables') AND $query->num_rows() > 0)
			{
				foreach($query->result_array() as $row)
				{
					$cache[$row['table_name']] = unserialize($row['table_data']);
				}
			}
			else
			{
				$rebuild = TRUE;
			}

			$this->db->query_count--;
			$this->db->db_debug = $debug;
		}


		if ($rebuild == TRUE)
		{
			$table_exists = FALSE;
			foreach($this->db->list_tables() as $table)
			{
				if ($table == 'orm_tables')
				{
					$table_exists = TRUE;
					continue;
				}

				$cache[$table] = $this->db->table_data($table);
			}

			if ( ! $table_exists)
			{
				$query = 'CREATE TABLE orm_tables ('."\n"
				       . '    table_name varchar(64) NOT NULL,'."\n"
				       . '    table_data text NOT NULL,'."\n"
				       . '    PRIMARY KEY (table_name)'."\n"
				       . ')';
				if ( ! $this->db->simple_query($query))
				{
					show_error ('Could not create the ORM cache table. Please create it using the following prototype: <br/>'."\n<pre>$query</pre>");
				}
			}

			foreach($cache as $table => $fields)
			{
				$data = array();
				$data['fields']        = array();
				$data['relationships'] = array();

				foreach($fields as $field)
				{
					$l = TRUE; // Field has a length
					$f =& $data['fields'][$field['name']];
					$r =& $data['relationships'];

					if ($field['key'] == 'primary')
					{
						$f = 'primary';
						$l = FALSE;
					}
					elseif (preg_match('/(.+)_id\b/', $field['name'], $x))
					{
						$f = 'foreign';
						$l = FALSE;

						$r['has_one'][] = ($x[1] == 'parent') ? singular($table) : $x[1];
					}
					elseif (strpos($field['type'], 'int') !== FALSE)
					{
						$f = 'numeric';
					}
					elseif (strpos($field['type'], 'text') !== FALSE
					OR      strpos($field['type'], 'blob') !== FALSE)
					{
						$f = 'text';
						$l = FALSE;
					}
					elseif (strpos($field['type'], 'decimal') !== FALSE
					OR      strpos($field['type'], 'float')   !== FALSE)
					{
						$f = 'decimal';
					}
					elseif (strpos($field['type'], 'char') !== FALSE)
					{
						$f = 'string';
					}
					// Add size
					if ($l == TRUE AND $field['size'] > 0)
					{
						$f .= '['.$field['size'].']';
					}
					// Add unique attribute
					if ($field['key'] == 'unique')
					{
						$f .= '|unique';
					}
					// Add optional attribute
					if ($field['null'] == TRUE)
					{
						$f .= '|optional';
					}
					// References must be unset before being reset
					if (count($r) > 0)
					{
						foreach($r as $_r => $_t)
						{
							$r[$_r] = array_unique($_t);
						}
					}
					unset($f, $r);
				}

				$cache[$table] = $data;
			}

			foreach($cache as $table => $data)
			{
				$has_one = (isset($data['relationships']['has_one'])) ? $data['relationships']['has_one'] : FALSE;

				if ( ! is_array($has_one))
					continue;

				if ((count($has_one) == 2) == count($data['fields']))
				{
					$maps = array_map('plural', $has_one);
					$mapping = implode('_', $maps);
					if (isset($cache[$mapping]))
					{
						$cache[$maps[0]]['relationships']['has_many'][] = $maps[1];
						$cache[$maps[1]]['relationships']['belongs_to_many'][] = $maps[0];
						$cache[$mapping]['relationships']['mapping'] = $has_one;
						unset($cache[$mapping]['relationships']['has_one']);
						continue;
					}
				}

				foreach($has_one as $key => $child)
				{
					if ( ! isset($cache[plural($child)]))
						continue;

					$cache[plural($child)]['relationships']['has_many'][] = $table;
					$through = $has_one;
					unset($through[array_search($child, $through)]);

					foreach($through as $t)
					{
						if (plural($t) == $table OR isset($cache[plural($child)]['fields'][$t.'_id']))
							continue;

						$cache[plural($child)]['relationships']['has_many'][] = plural($t);
						$cache[plural($child)]['relationships']['through'][plural($t)] = $table;
					}
				}
			}

			foreach($cache as $table => $data)
			{
				$this->db->set('table_name', $table);
				$this->db->set('table_data', serialize($data));
				$this->db->insert('orm_tables');
			}
		}

		if ( ! isset($cache[$this->table]))
		{
			show_error('The table you selected &quot;'.$this->table.'&quot; does not exist in the database.');
		}

		$this->fields = $cache[$this->table]['fields'];
		$this->relationships = $cache[$this->table]['relationships'];
	}

	// --------------------------------------------------------------------

	/**
	 * Generate Table
	 *
	 * Create an ORM table definition based on model fields
	 *
	 * @access	public
	 * @param	string	escape string
	 * @param	bool	only return columns that have changed
	 * @return 	array
	 */
	private function _generate_table($esc, $diff = FALSE)
	{
		$table = array(
			'primary_key' => '',
			'index'       => array(),
			'unique'      => array(),
			'columns'     => array(),
			'comment'     => 'ORM Generated Table');
		// Loop through fields and construct columns
		foreach ($this->fields as $name => $def)
		{
			$col = $esc.$name.$esc;
			$def = explode('|', $def);
			// Extract the type of column, and it's size arguments
			list($type, $args) = str_to_array(array_shift($def));
			// Optional fields are allowed to be NULL
			$null = (in_array('optional', $def));
			switch($type)
			{
				case 'primary':
					$table['primary_key'] = $esc.$name.$esc;
					$col .= ($this->db->dbdriver == 'postgre') ? ' serial' : ' integer unsigned auto_increment';
					$null = FALSE;
					break;
				case 'parent':
				case 'foreign':
				case 'timestamp':
					$col .= ' integer unsigned';
					break;
				case 'string':
					$length = ($args[0] > 0) ? $args[0] : '127';
					$col .= ' varchar('.$length.')';
					break;
				case 'numeric':
					$length = ($args[0] > 0) ? (int) $args[0] : 127;
					if ($length > 10)
					{
						$col .= ' bigint';
					}
					elseif ($length > 4)
					{
						$col .= ' integer';
					}
					else
					{
						$col .= ' smallint';
					}
					break;
				case 'boolean':
					$col .= ' boolean';
					$null = FALSE;
					break;
				case 'telephone':
					$length = array_sum($args) + count($args) - 1;
					$col .= ' varchar('.$length.')';
					break;
				case 'text':
					$col .= ' text';
					break;
				case 'bigtext':
					$col .= ' mediumtext';
					break;
				case 'decimal':
					$args   = array_slice($args, 0, 2);
					$length = implode(',', $args);
					$col .= ' decimal('.$length.')';
					break;
			}
			// Add NOT NULL
			if ($null == FALSE)
			{
				$col .= ' NOT NULL';
			}

			while($rule = array_shift($def))
			{
				switch($rule)
				{
					case 'unique':
						$table['unique'][] = $esc.$name.$esc;
						break;
					case 'index':
						$table['index'][] = $esc.$name.$esc;
						break;
				}
			}

			$table['columns'][] = $col;
		}
		// Add PRIMARY KEY
		if ($table['primary_key'] != '')
		{
			$table['columns'][] = 'PRIMARY KEY ('.$table['primary_key'].')';
		}
		// Add other KEYs
		if (count($table['index']) > 0)
		{
			$table['columns'][] = 'KEY '.$esc.$this->prefix.'idx'.$esc.' ('.implode(',', $table['index']).')';
		}
		if (count($table['unique']) > 0)
		{
			$table['columns'][] = 'UNIQUE KEY '.$esc.$this->prefix.'idx'.$esc.' ('.implode(',', $table['unique']).')';
		}

		return $table;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Table Name
	 *
	 * Retrieve the table name
	 *
	 * @access	public
	 * @return 	string
	 */
	public function _table()
	{
		return $this->table;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Field Names
	 *
	 * Retrieve all the field names and return
	 *
	 * @access	public
	 * @return 	array
	 */
	public function _fields()
	{
		return array_keys($this->fields);
	}

	public function _relationship($type, $name)
	{
		$type = strtolower((string) $type);

		return (bool) (isset($this->relationships[$type]) AND in_array($name, $this->relationships[$type]));
	}

	// --------------------------------------------------------------------

	/**
	 * Get Child Tables
	 *
	 * Retrieve all the child names and return
	 *
	 * @access	public
	 * @return 	array
	 */
	public function _children()
	{
		$children = NULL;

		if (isset($this->relationships['has_one']))
		{
			$children = $this->relationships['has_one'];
		}

		return (array) $children;
	}

	// --------------------------------------------------------------------

	/**
	 * Gather Data
	 *
	 * Create an array of the current object
	 *
	 * @access	private
	 * @param	bool	validate the current data before returning
	 * @return 	array
	 */
	private function _data($validate = FALSE)
	{
		$data = array();

		foreach ($this->_fields() as $key)
		{
			$val = (string) $this->table_data[$key];

			if ($validate == TRUE)
			{
				$val = $this->_validate($key, $val);
			}

			$data[$key] = $val;
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Cache Data
	 *
	 * This function caches data after loading/saving for comparison
	 *
	 * @access	private
	 * @param	array	array of data to cache
	 * @return 	array
	 */
	private function _cache($data = FALSE)
	{
		if ($data === FALSE)
		{
			return $this->model_cache;
		}
		else
		{
			$this->model_cache = $data;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Add an Error
	 *
	 * Adds an error to the current model errors
	 *
	 * @access	private
	 * @param	string	function name
	 * @param	string	error message
	 * @return 	false
	 */
	private function _error($function, $message = '')
	{
		$message = trim((string) $message);
		if ($message != '')
		{
			$this->model_errors[] = sprintf('%-30s %s', $function, $message);
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Checks Subject
	 *
	 * Changes a subject into an array if it is a string
	 *
	 * @access	private
	 * @param	mixed	subject to check
	 * @return 	array
	 */
	private function _subject($subject)
	{
		if ( ! is_array($subject))
		{
			if (is_numeric($subject))
			{
				$subject = array($this->prefix.'id' => $subject);
			}
		}

		return (array) $subject;
	}

	// --------------------------------------------------------------------

	/**
	 * Run Query
	 *
	 * Runs an ORM query, attaching children and initializing
	 *
	 * @access	private
	 * @param	numeric	number of records to fetch
	 * @param	numeric	number of records to sk
	 * @return 	array
	 */
	private function _query($limit, $offset, $array = FALSE)
	{
		// Find fields for selection
		foreach ($this->_fields() as $key)
		{
			$select[] = "$this->table.$key";
		}
		// Add children for joining
		if (count($children = $this->_children()) > 0)
		{
			$CI =& get_instance();
			foreach($children as $child)
			{
				$key = $child.'_id';
				$this->$child = $CI->load->orm($child);
				$table = $this->$child->_table();

				$this->db->join($table, sprintf('%s.%s = %s.%s', $this->table, $key, $table, $key), 'left');
				foreach ($this->$child->_fields() as $field)
				{
					// @note: We use "->" as the separator between $child and
					// $field to allow a wide range of field names
					$select[] = sprintf('%s.%s AS `%s->%s`', $table, $field, $child, $field);
				}
			}
		}
		// Now we can actually build a query
		$this->db->select($select);
		if ($limit > 0)
		{
			$this->db->limit($limit, $offset);
		}
		// Fetch and return result
		$query  = $this->db->get();
		$result = FALSE;
		if ($query->num_rows() > 0)
		{
			if($array == TRUE OR $query->num_rows() > 1)
			{
				$CI =& get_instance();
				$result = array();
				foreach ($query->result_array() as $row)
				{
					$model =& $CI->load->orm(singular($this->_table()));
					$model->_result($row);
					$model->initialize();
					$result[] = $model;
					unset($model);
				}
			}
			else
			{
				$result = $this->_result($query->row_array());
				$this->initialize();
			}
		}
		$query->free_result();

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Process Query Result
	 *
	 * Checks the query result, sets data, and returns
	 *
	 * @access	public
	 * @param	object	active record query object
	 * @return 	bool
	 */
	public function _result($array)
	{
		if ( ! is_array($array) OR count($array) < 1)
			return FALSE;

		$CI =& get_instance();
		foreach ($array as $key => $val)
		{
			if (in_array($key, $this->_fields()))
			{
				$this->table_data[$key] = $val;
				continue;
			}

			list ($child, $key) = explode('->', $key);
			if ( ! is_object($this->$child))
			{
				$this->$child = $CI->load->orm($child);
			}

			$this->$child->$key = $val;
		}

		return TRUE;
	}

}

// END ORM_Model Class
?>