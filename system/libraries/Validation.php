<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The small, swift, and secure PHP5 framework
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
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
 * $Id: Encrypt.php 475 2007-09-03 02:28:49Z Shadowhand $
 *
 * @package        Kohana
 * @subpackage     Libraries
 * @category       Validation
 * @author         Rick Ellis, Kohana Team
 * @link           http://kohanaphp.com/user_guide/libraries/validation.html
 */
class Validation_Core {

	private static $instances = 0;

	public $form_safe    = FALSE;
	public $messages     = array();
	public $error_format = '<p class="error">{message}</p>';

	private $fields = array();
	private $rules  = array();
	private $errors = array();

	private $data = array();

	/**
	 * Constructor
	 *
	 * @access  public
	 * @param   array   array to validate
	 * @return  void
	 */
	public function __construct( & $data = array())
	{
		if ($data === array())
		{
			$this->data =& $_POST;
		}
		elseif (is_array($data) AND count($data) > 0)
		{
			$this->data =& $data;
		}

		// Load the default error messages
		$this->messages = Kohana::lang('validation');

		// Add one more instance to the count
		self::$instances++;

		Log::add('debug', 'Validation Library Initialized, instance '.self::$instances);
	}

	public function debug()
	{
		ob_start();

		foreach(array('data', 'fields', 'rules', 'errors', 'messages') as $var)
		{
			print strtoupper($var);
			print "<pre>".print_r($this->$var, TRUE)."</pre>\n\n";
		}

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Field Information
	 *
	 * This function takes an array of key names, rules, and field names as
	 * input and sets internal field information
	 *
	 *This function takes an array of field names and validation
	 * rules as input ad simply stores is for use later.
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	public function set($data, $rules = '', $field = FALSE)
	{
		// Normalize rules to an array
		if ( ! is_array($data))
		{
			if ($rules == '') return FALSE;

			// Make data into an array
			$data = array($data => array($rules, $field));
		}

		// Set the field information
		foreach ($data as $name => $rules)
		{
			if (is_array($rules))
			{
				if (count($rules) > 1)
				{
					// Double equals revents $rules from getting borked by list()
					list($field, $rules) = $rule = $rules;
				}
				else
				{
					$rules = current($rules);
				}
			}

			// Empty field names default to the name of the element
			$this->fields[$name] = ($field == '') ? $name : $field;
			$this->rules[$name]  = $rules;
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
	public function set_message($func, $message = '')
	{
		if ( ! is_array($func))
		{
			$func = array($func, $message);
		}
		
		foreach($func as $name => $message)
		{
			$this->messages[$name] = $message;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Error Message Format
	 *
	 * Allows the user to change the error message format. Error formats must
	 * contain the string "{message}" or Kohana_Exception will be triggered.
	 *
	 * @access  public
	 * @param   string
	 * @return  void
	 */
	public function error_format($string = '')
	{
		if (strpos('{message}', (string) $string) === FALSE)
			throw new Kohana_Exception('validation.error_format');

		$this->error_format = $string;
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
	public function run()
	{
		// Do we even have any data to process?  Mm?
		if (count($_POST) == 0 OR count($this->_rules) == 0)
		{
			return FALSE;
		}

		// Load the language file containing error messages
		$this->CORE->lang->load('validation');

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
						if (FALSE === ($line = $this->CORE->lang->line('isset')))
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
			foreach ($ex as $rule)
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
				if (preg_match('/([^\[]*+)\[(.*?)\]/', $rule, $match))
				{
					$rule  = $match[1];
					$param = $match[2];
				}

				// Call the function that corresponds to the rule
				if ($callback === TRUE)
				{
					if ( ! method_exists($this->CORE, $rule))
					{
						continue;
					}

					$result = $this->CORE->$rule($_POST[$field], $param);

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
						if (($line = $this->CORE->lang->line($rule)) === FALSE)
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
	public function required($str)
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
	public function matches($str, $field)
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
	public function min_length($str, $val)
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
	public function max_length($str, $val)
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
	public function exact_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}

		return (bool) (strlen($str) == $val);
	}

	// --------------------------------------------------------------------

	/**
	 * E-mail validator
	 *
	 * @access  public
	 * @param   string
	 * @return  boolean
	 */
	public function valid_email($email)
	{
		return (bool) preg_match('/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/iD', $email);
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
	public function valid_email_rfc($str)
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
	public function valid_ip($ip)
	{
		$CORE = Kohana::$instance;
		return $CORE->input->valid_ip($ip);
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha($str)
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
	public function alpha_numeric($str)
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
	public function alpha_dash($str)
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
	public function digit($str)
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
	public function numeric($str) {
		if ( ! is_numeric($str))
		    return FALSE;

		if ( ! preg_match('/^[-0-9.]+$/', $str))
		    return FALSE;

		return TRUE;
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
	public function set_select($field = '', $value = '')
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
	public function set_radio($field = '', $value = '')
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
	public function set_checkbox($field = '', $value = '')
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
	public function prep_for_form($str = '')
	{
		if ($this->_safe_form_data == FALSE OR $str == '')
		{
			return $str;
		}

		return html::specialchars($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep URL
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function prep_url($str = '')
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
	public function strip_image_tags($str)
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
	public function xss_clean($str)
	{
		$_POST[$this->_current_field] = $this->CORE->input->xss_clean($str);
	}

	// --------------------------------------------------------------------

	/**
	 * Convert PHP tags to entities
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function encode_php_tags($str)
	{
		$_POST[$this->_current_field] = str_replace(array('<?', '?>'),  array('&lt;?', '?&gt;'), $str);
	}



} // End Validation Class