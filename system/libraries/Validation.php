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
 * Validation Class
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Validation
 * @author		Rick Ellis
 * @link		http://kohanaphp.com/user_guide/libraries/validation.html
 */
class CI_Validation {

	var $CI;
	var $error_string    = '';
	var $_error_array    = array();
	var $_rules          = array();
	var $_fields         = array();
	var $_error_messages = array();
	var $_current_field  = '';
	var $_safe_form_data = FALSE;
	var $_error_prefix   = '<p>';
	var $_error_suffix   = '</p>';

	/**
	 * Constructor
	 */
	function CI_Validation()
	{
		$this->CI =& get_instance();
		log_message('debug', "Validation Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Set Fields
	 *
	 * This function takes an array of field names as input
	 * and generates class variables with the same name, which will
	 * either be blank or contain the $_POST value corresponding to it
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	function set_fields($data = '', $field = '')
	{
		if ($data == '')
		{
			if (count($this->_fields) == 0)
			{
				return FALSE;
			}
		}
		else
		{
			if ( ! is_array($data))
			{
				$data = array($data => $field);
			}

			if (count($data) > 0)
			{
				$this->_fields = $data;
			}
		}

		foreach($this->_fields as $key => $val)
		{
			$this->$key = ( ! isset($_POST[$key]) OR is_array($_POST[$key])) ? '' : $this->prep_for_form($_POST[$key]);

			$error = $key.'_error';
			if ( ! isset($this->$error))
			{
				$this->$error = '';
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input ad simply stores is for use later.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	function set_rules($data, $rules = '')
	{
		if ( ! is_array($data))
		{
			if ($rules == '')
				return;

			$data[$data] = $rules;
		}

		foreach ($data as $key => $val)
		{
			$this->_rules[$key] = $val;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set Error Message
	 *
	 * Lets users set their own error messages on the fly.  Note:  The key
	 * name has to match the  function name that it corresponds to.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function set_message($lang, $val = '')
	{
		if ( ! is_array($lang))
		{
			$lang = array($lang => $val);
		}

		$this->_error_messages = array_merge($this->_error_messages, $lang);
	}

	// --------------------------------------------------------------------

	/**
	 * Set The Error Delimiter
	 *
	 * Permits a prefix/suffix to be added to each error message
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
	{
		$this->_error_prefix = $prefix;
		$this->_error_suffix = $suffix;
	}

	// --------------------------------------------------------------------

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */
	function run()
	{
		// Do we even have any data to process?  Mm?
		if (count($_POST) == 0 OR count($this->_rules) == 0)
		{
			return FALSE;
		}

		// Load the language file containing error messages
		$this->CI->lang->load('validation');

		// Cycle through the rules and test for errors
		foreach ($this->_rules as $field => $rules)
		{
			// Is the field required, a callback, or a match? If not, we can continue
			if ( ! preg_match('/required|callback|matches/', $rules, $ex))
			{
				if ( ! isset($_POST[$field]) OR $_POST[$field] == '')
				{
					continue;
				}
			}

			//Explode out the rules!
			$ex = explode('|', $rules);

			/*
			 * Are we dealing with an "isset" rule?
			 *
			 * Before going further, we'll see if one of the rules
			 * is to check whether the item is set (typically this
			 * applies only to checkboxes).  If so, we'll
			 * test for it here since there's not reason to go
			 * further
			 */
			if ( ! isset($_POST[$field]))
			{
				if (in_array('isset', $ex, TRUE) OR in_array('required', $ex))
				{
					if ( ! isset($this->_error_messages['isset']))
					{
						if (FALSE === ($line = $this->CI->lang->line('isset')))
						{
							$line = 'The field was not set';
						}
					}
					else
					{
						$line = $this->_error_messages['isset'];
					}

					$field = ( ! isset($this->_fields[$field])) ? $field : $this->_fields[$field];
					$this->_error_array[] = sprintf($line, $field);
				}

				continue;
			}

			/*
			 * Set the current field
			 *
			 * The various prepping functions need to know the
			 * current field name so they can do this:
			 *
			 * $_POST[$this->_current_field] == 'bla bla';
			 */
			$this->_current_field = $field;

			// Cycle through the rules!
			foreach ($ex As $rule)
			{
				// Is the rule a callback?
				$callback = FALSE;
				if (substr($rule, 0, 9) == 'callback_')
				{
					$rule = substr($rule, 9);
					$callback = TRUE;
				}

				// Strip the parameter (if exists) from the rule
				// Rules can contain a parameter: max_length[5]
				$param = FALSE;
				if (preg_match('/(.*?)\[(.*?)\]/', $rule, $match))
				{
					$rule  = $match[1];
					$param = $match[2];
				}

				// Call the function that corresponds to the rule
				if ($callback === TRUE)
				{
					if ( ! method_exists($this->CI, $rule))
					{
						continue;
					}

					$result = $this->CI->$rule($_POST[$field], $param);

					// If the field isn't required and we just processed a callback we'll move on...
					if ( ! in_array('required', $ex, TRUE) AND $result !== FALSE)
					{
						continue 2;
					}
				}
				else
				{
					if ( ! method_exists($this, $rule))
					{
						/*
						 * Run the native PHP function if called for
						 *
						 * If our own wrapper function doesn't exist we see
						 * if a native PHP function does. Users can use
						 * any native PHP function call that has one param.
						 */
						if (function_exists($rule))
						{
							$_POST[$field] = $rule($_POST[$field]);
							$this->$field = $_POST[$field];
						}

						continue;
					}

					$result = $this->$rule($_POST[$field], $param);
				}

				// Did the rule test negatively?  If so, grab the error.
				if ($result === FALSE)
				{
					if ( ! isset($this->_error_messages[$rule]))
					{
						if (($line = $this->CI->lang->line($rule)) === FALSE)
						{
							$line = 'Unable to access an error message corresponding to your field name.';
						}
					}
					else
					{
						$line = $this->_error_messages[$rule];;
					}

					// Build the error message
					$mfield = ( ! isset($this->_fields[$field])) ? $field : $this->_fields[$field];
					$mparam = ( ! isset($this->_fields[$param])) ? $param : $this->_fields[$param];
					$message = sprintf($line, $mfield, $mparam);

					// Set the error variable.  Example: $this->username_error
					$error = $field.'_error';
					$this->$error = $this->_error_prefix.$message.$this->_error_suffix;

					// Add the error to the error array
					$this->_error_array[] = $message;
					continue 2;
				}
			}
		}

		$total_errors = count($this->_error_array);

		/*
		 * Recompile the class variables
		 *
		 * If any prepping functions were called the $_POST data
		 * might now be different then the corresponding class
		 * variables so we'll set them anew.
		 */
		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		$this->set_fields();

		// Did we end up with any errors?
		if ($total_errors == 0)
		{
			return TRUE;
		}

		// Generate the error string
		foreach ($this->_error_array as $val)
		{
			$this->error_string .= $this->_error_prefix.$val.$this->_error_suffix."\n";
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Required
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function required($str)
	{
		if ( ! is_array($str))
		{
			return (trim($str) == '') ? FALSE : TRUE;
		}
		else
		{
			return ( ! empty($str));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function matches($str, $field)
	{
		if ( ! isset($_POST[$field]))
		{
			return FALSE;
		}

		return ($str !== $_POST[$field]) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Minimum Length
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function min_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}

		return (bool) (strlen($str) > $val);
	}

	// --------------------------------------------------------------------

	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function max_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}

		return (bool) (strlen($str) < $val);
	}

	// --------------------------------------------------------------------

	/**
	 * Exact Length
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function exact_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}

		return (bool) (strlen($str) == $val);
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Email
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function valid_email($str)
	{
		return (bool) preg_match('/^[+a-z0-9_-]+(\.[+a-z0-9_-]+)*@([a-z0-9-]+\.)+[a-z]{2,6}$/iD', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Email RFC
	 *
	 * Originally by Cal Henderson, modified to fit Kohana syntax standards
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 * @author	Cal Henderson
	 * @link	http://www.iamcal.com/publish/articles/php/parsing_email/
	 * @link	http://www.w3.org/Protocols/rfc822/
	 */
	function valid_email_rfc($str)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom  = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$pair  = '\\x5c[\\x00-\\x7f]';

		$domain_literal = "\\x5b($dtext|$pair)*\\x5d";
		$quoted_string  = "\\x22($qtext|$pair)*\\x22";
		$sub_domain     = "($atom|$domain_literal)";
		$word           = "($atom|$quoted_string)";
		$domain         = "$sub_domain(\\x2e$sub_domain)*";
		$local_part     = "$word(\\x2e$word)*";
		$addr_spec      = "$local_part\\x40$domain";

		return (bool) preg_match('/^'.$addr_spec.'$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Validate IP Address
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function valid_ip($ip)
	{
		$CI =& get_instance();
		return $CI->input->valid_ip($ip);
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alpha($str)
	{
		return ctype_alpha($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alpha_numeric($str)
	{
		return ctype_alnum($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alpha_dash($str)
	{
		return (bool) preg_match('/^[-a-z0-9_]+$/iD', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Digits Only [0-9, no dots or dashes]
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */
	function digit($str)
	{
		return ctype_digit($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Numeric
	 *
	 * @access	public
	 * @param	int
	 * @return	bool
	 */
	function numeric($str) {
		if (!is_numeric($str))
		    return FALSE;

		if (!preg_match('/^[-0-9.]+$/', $str))
		    return FALSE;

		return TRUE;
	}
	// --------------------------------------------------------------------

	/**
	 * Is Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function is_numeric($str)
	{
		return ( ! is_numeric($str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Select
	 *
	 * Enables pull-down lists to be set to the value the user
	 * selected in the event of an error
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function set_select($field = '', $value = '')
	{
		if ($field == '' OR $value == '' OR  ! isset($_POST[$field]))
		{
			return '';
		}

		if ($_POST[$field] == $value)
		{
			return ' selected="selected"';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set Radio
	 *
	 * Enables radio buttons to be set to the value the user
	 * selected in the event of an error
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function set_radio($field = '', $value = '')
	{
		if ($field == '' OR $value == '' OR  ! isset($_POST[$field]))
		{
			return '';
		}

		if ($_POST[$field] == $value)
		{
			return ' checked="checked"';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set Checkbox
	 *
	 * Enables checkboxes to be set to the value the user
	 * selected in the event of an error
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	function set_checkbox($field = '', $value = '')
	{
		if ($field == '' OR $value == '' OR  ! isset($_POST[$field]))
		{
			return '';
		}

		if ($_POST[$field] == $value)
		{
			return ' checked="checked"';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Prep data for form
	 *
	 * This function allows HTML to be safely shown in a form.
	 * Special characters are converted.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function prep_for_form($str = '')
	{
		if ($this->_safe_form_data == FALSE OR $str == '')
		{
			return $str;
		}

		return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep URL
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function prep_url($str = '')
	{
		if ($str == 'http://' OR $str == '')
		{
			$_POST[$this->_current_field] = '';
			return;
		}

		if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://')
		{
			$str = 'http://'.$str;
		}

		$_POST[$this->_current_field] = $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Strip Image Tags
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function strip_image_tags($str)
	{
		$_POST[$this->_current_field] = $this->input->strip_image_tags($str);
	}

	// --------------------------------------------------------------------

	/**
	 * XSS Clean
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function xss_clean($str)
	{
		$_POST[$this->_current_field] = $this->CI->input->xss_clean($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Convert PHP tags to entities
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function encode_php_tags($str)
	{
		$_POST[$this->_current_field] = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
	}

}
// END Validation Class
?>