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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Validation Class
 *
 * $Id$
 *
 * @package        Kohana
 * @subpackage     Libraries
 * @category       Validation
 * @author         Rick Ellis, Kohana Team
 * @link           http://kohanaphp.com/user_guide/libraries/validation.html
 */
class Validation_Core {

	// Instance count
	private static $instances = 0;

	// Currently validating field
	public $current_field = '';

	// Enable or disable safe form errors
	public $form_safe = FALSE;

	// Error message format
	public $error_format = '<p class="error">{message}</p>';
	public $newline_char = "\n";

	// Error messages
	public $messages = array();

	// Field names, rules, and errors
	protected $fields = array();
	protected $rules  = array();
	protected $errors = array();

	// Data to validate
	protected $data = array();

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

		// Add one more instance to the count
		self::$instances++;

		Log::add('debug', 'Validation Library Initialized, instance '.self::$instances);
	}

	public function __get($key)
	{
		if ( ! isset($this->$key))
		{
			if (substr($key, -6) === '_error')
			{
				// Get the field name
				$field = substr($key, 0, -6);

				// Return the error messages for this field
				$messages = FALSE;
				if (isset($this->errors[$field]) AND ! empty($this->errors[$field]))
				{
					foreach($this->errors[$field] as $error)
					{
						// Replace the message with the error in the html error string
						$messages .= str_replace('{message}', $error, $this->error_format).$this->newline_char;
					}
				}
				return $messages;
			}
			elseif (isset($this->data[$key]))
			{
				return $this->data[$key];
			}
		}
	}

	public function debug()
	{
		// Start buffering
		ob_start();

		// Debug important variables
		foreach(array('data', 'fields', 'rules', 'errors', 'messages') as $var)
		{
			print strtoupper($var);
			print '<pre>'.print_r($this->$var, TRUE)."</pre>\n\n";
		}

		// Fetch the buffer
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	/**
	 * Set Field Information
	 *
	 * This function takes an array of key names, rules, and field names as
	 * input and sets internal field information.
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   string
	 * @return  void
	 */
	public function set_rules($data, $rules = '', $field = FALSE)
	{
		// Normalize rules to an array
		if ( ! is_array($data))
		{
			if ($rules == '') return FALSE;

			// Make data into an array
			$data = array($data => array($field, $rules));
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

	/**
	 * Set Error Message
	 *
	 * Lets users set their own error messages on the fly.  Note:  The key
	 * name has to match the  function name that it corresponds to.
	 *
	 * @access  public
	 * @param   string
	 * @param   string
	 * @return  string
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

	public function add_error($func, $field)
	{
		// Set the friendly field name
		$friendly = isset($this->fields[$field]) ? $this->fields[$field] : $field;

		// Fetch the message
		$message = isset($this->messages[$func]) ? $this->messages[$func] : $this->messages['unknown_error'];

		// Replacements in strings
		$replace = array_slice(func_get_args(), 1);

		// Add the field name into the message, if there is a place for it
		$message = (strpos($message, '%s') !== FALSE) ? vsprintf($message, $replace) : $message;

		$this->errors[$field][] = $message;
	}

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function run()
	{
		// Do we even have any data to process?  Mm?
		if (count($this->data) == 0 OR count($this->rules) == 0)
		{
			return FALSE;
		}

		if ($this->messages == FALSE)
		{
			// Load the default error messages
			$this->messages = Kohana::lang('validation');
		}

		// Cycle through the rules and test for errors
		foreach ($this->rules as $field => $rules)
		{
			// Set the current field, for other functions to use
			$this->current_field = $field;

			// Insert uploads into the data
			if (strpos($rules, 'upload') !== FALSE AND isset($_FILES[$field]))
			{
				if (is_array($_FILES[$field]['error']))
				{
					foreach($_FILES[$field]['error'] as $error)
					{
						if ($error !== UPLOAD_ERR_NO_FILE)
						{
							$this->data[$field] = $_FILES[$field];
							break;
						}
					}
				}
				elseif ($_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE)
				{
					$this->data[$field] = $_FILES[$field];
				}
			}

			// Process empty fields
			if ( ! isset($this->data[$field]))
			{
				// This field is required
				if (preg_match('/required|callback|matches/', $rules))
				{
					$this->add_error('required', $field);
				}
				continue;
			}

			// Loop through the rules and process each one
			foreach(explode('|', $rules) as $rule)
			{
				if ($rule === 'trim' OR $rule === 'sha1' OR $rule === 'md5')
				{
					/**
					 * @todo safe_form_data
					 */
					$this->data[$field] = $rule($this->data[$field]);
				}

				// Handle callback rules
				$callback = FALSE;
				if (preg_match('/callback_(.+)/', $rule, $match))
				{
					$callback = $match[1];
				}

				// Handle params
				$params = FALSE;
				if (preg_match('/([^\[]*+)\[(.*?)\]/', $rule, $match))
				{
					$rule   = $match[1];
					$params = explode(',', $match[2]);
				}

				// Process this field with the rule
				if ($callback !== FALSE)
				{
					if ( ! method_exists(Kohana::instance(), $callback))
						throw new Kohana_Exception('validation.invalid_rule', $callback);

					$result = Kohana::instance()->$callback($this->data[$field], $params);
				}
				elseif (method_exists($this, $rule))
				{
					$result = $this->$rule($this->data[$field], $params);
				}
				elseif (is_callable($rule, TRUE))
				{
					if (strpos($rule, '::') !== FALSE)
					{
						$result = call_user_func(explode('::', $rule), $field);
					}
					else
					{
						$result = $rule($this->data[$field]);
					}
				}
				else
				{
					// Trying to validate with a rule that does not exist? No way!
					throw new Kohana_Exception('validation.invalid_rule', $rule);
				}
			}
		}

		// Run validation finished Event and return
		if (count($this->errors) == 0)
		{
			Event::run('validation.success', $this->data);
			return TRUE;
		}
		else
		{
			Event::run('validation.failure', $this->data);
			return FALSE;
		}
	}

	public function upload($data, $params = FALSE)
	{
		// By default, nothing is allowed
		$allowed = FALSE;

		// Maximum sizes of various attributes
		$maxsize = array
		(
			'file'   => FALSE,
			'human'  => FALSE,
			'width'  => FALSE,
			'height' => FALSE
		);

		// Validate the uploaded file
		if ( ! isset($data['tmp_name']) OR ! is_uploaded_file($data['tmp_name']))
		{
			return FALSE;
		}
		elseif (is_array($data['name']))
		{
			// Handle an array of inputs
			$files = $data;
			$total = count($files['name']) + 1;

			for ($i = 0; $i < $total; $i++)
			{
				// Fake a single upload input
				$data = array
				(
					'name'     => $files['name'][$i],
					'type'     => $files['type'][$i],
					'size'     => $files['size'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error'    => $files['error'][$i]
				);

				// Recursion
				if ( ! $this->upload($data, $params))
					return FALSE;
			}

			// All files uploaded successfully
			return TRUE;
		}

		// Parse addition parameters
		if (is_array($params) AND ! empty($params))
		{
			// Creates a mirrored array: foo=foo,bar=bar
			$params = array_combine($params, $params);

			foreach($params as $param)
			{
				if (preg_match('/[0-9]+x[0-9]+/', $param))
				{
					// Maximum image size, eg: 200x100
					list($maxsize['width'], $maxsize['height']) = explode('x', $param);
				}
				elseif (preg_match('/[0-9].+[BKMG]/i', $param))
				{
					// Maximum file size, eg: 1M
					$maxsize['human'] = strtoupper($param);

					switch(strtoupper(substr($param, -1)))
					{
						case 'G': $param = intval($param) * pow(1024, 3); break;
						case 'M': $param = intval($param) * pow(1024, 2); break;
						case 'K': $param = intval($param) * pow(1024, 1); break;
						default:  $param = intval($param);                break;
					}

					$maxsize['file'] = $param;
				}
				else
				{
					$allowed[strtolower($param)] = strtolower($param);
				}
			}
		}

		// Uploads must use a white-list of allowed file types
		if (empty($allowed))
			throw new Kohana_Exception('upload.set_allowed');

		// Error code definitions available at:
		// http://us.php.net/manual/en/features.file-upload.errors.php
		switch($data['error'])
		{
			// Valid upload
			case UPLOAD_ERR_OK:
			break;
			// Upload to large, based on php.ini settings
			case UPLOAD_ERR_INI_SIZE:
				if ($maxsize['human'] == FALSE)
				{
					$maxsize['human'] = ini_get('upload_max_filesize');
				}
				$this->add_error('max_size', $this->current_field, $maxsize['human']);
				return FALSE;
			break;
			// Kohana does not allow the MAX_FILE_SIZE input to control filesize
			case UPLOAD_ERR_FORM_SIZE:
				throw new Kohana_Exception('upload.max_file_size');
			break;
			// User aborted the upload, or a connection error occurred
			case UPLOAD_ERR_PARTIAL:
				$this->add_error('user_aborted', $this->current_field);
				return FALSE;
			break;
			// No file was uploaded, or an extension blocked the upload
			case UPLOAD_ERR_NO_FILE:
			case UPLOAD_ERR_EXTENSION:
				return FALSE;
			break;
			// No temporary directory set in php.ini
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Kohana_Exception('upload.no_tmp_dir');
			break;
			// Could not write to the temporary directory
			case UPLOAD_ERR_CANT_WRITE:
				throw new Kohana_Exception('upload.tmp_unwritable');
			break;
		}

		if ($maxsize['file'] AND $data['size'] > $maxsize['file'])
		{
			$this->add_error('max_size', $this->current_field, $maxsize['file']);
			return FALSE;
		}

		// Find the MIME type of the file. Although the mime type is available
		// in the upload data, it can easily be faked. Instead, we use the
		// server filesystem functions (if possible) to determine the MIME type.

		if (preg_match('/jpe?g|png|[gt]if/', implode(' ', $allowed)))
		{
			// Use getimagesize() to find the mime type on images
			$mime = @getimagesize($data['tmp_name']);

			// Validate height and width
			if ($maxsize['width'] AND $mime[0] > $maxsize['width'])
			{
				$this->add_error('max_width', $this->current_field, $maxsize['width']);
				return FALSE;
			}
			elseif ($maxsize['height'] AND $mime[1] > $maxsize['height'])
			{
				$this->add_error('max_height', $this->current_field, $maxsize['height']);
				return FALSE;
			}

			// Set mime type
			$mime = isset($mime['mime']) ? $mime['mime'] : FALSE;
		}
		elseif (function_exists('finfo_open'))
		{
			// Try using the fileinfo extension
			$finfo = finfo_open(FILEINFO_MIME);
			$mime  = finfo_file($finfo, $data['tmp_name']);
			finfo_close($finfo);
		}
		elseif (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type'))
		{
			// Use mime_content_type(), deprecated by PHP
			$mime = mime_content_type($data['tmp_name']);
		}
		elseif (file_exists($cmd = trim(exec('which file'))))
		{
			// Use the UNIX 'file' command
			$mime = escapeshellarg($data['tmp_name']);
			$mime = trim(exec($cmd.' -bi '.$mime));
		}
		else
		{
			// Trust the browser, as a last resort
			$mime = $data['type'];
		}

		// Find the list of valid mime types by the extension of the file
		$ext = strtolower(end(explode('.', $data['name'])));

		// Validate file mime type based on the extension. Because the mime type
		// is trusted (validated by the server), we check if the mime is in the
		// list of known mime types for the current extension.

		if ($ext == FALSE OR array_search($mime, Config::item('mimes.'.$ext)) === FALSE)
		{
			$this->add_error('invalid_type', $this->current_field);
			return FALSE;
		}

		// Removes spaces from the filename if configured to do so
		$filename = Config::item('upload.remove_spaces') ? preg_replace('/\s+/', '_', $data['name']) : $data['name'];

		// Fetch the real path of the upload directory, add the filename
		$filename = realpath(Config::item('upload.upload_directory')).'/'.$filename;

		// Move the upload file to the new location
		move_uploaded_file($data['tmp_name'], $filename);

		// Set the data to the current field name
		$this->data[$this->current_field] = $filename;

		return TRUE;
	}

	/**
	 * Required
	 *
	 * @access  public
	 * @param   string
	 * @param   integer
	 * @return  boolean
	 */
	public function required($str, $length = FALSE)
	{
		if ($str === '' OR $str === FALSE OR (is_array($str) AND empty($str)))
		{
			$this->add_error('required', $this->current_field);
			return FALSE;
		}
		elseif ($length != FALSE AND is_array($length))
		{
			if (count($length) > 1)
			{
				// Get the min and max length
				list ($min, $max) = $length;

				// Change length to the length of the string
				$length = strlen($str);

				// Test min length
				if ($length < $min)
				{
					$this->add_error('min_length', $this->current_field, (int) $min);
					return FALSE;
				}
				// Test max length
				elseif ($length > $max)
				{
					$this->add_error('max_length', $this->current_field, (int) $max);
					return FALSE;
				}
			}
			else
			{
				// Test exact length
				$this->add_error('exact_length', $this->current_field, current($length));
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}
	}

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	boolean
	 */
	public function matches($str, $field)
	{
		if ( ! isset($this->data[$field]))
			return FALSE;

		return (bool) ($str === $this->data[$field]);
	}

	/**
	 * Minimum Length
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @return	boolean
	 */
	public function min_length($str, $val)
	{
		if ( ! is_numeric($val))
			return FALSE;

		return (bool) (strlen($str) > $val);
	}

	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @return	boolean
	 */
	public function max_length($str, $val)
	{
		if ( ! is_numeric($val))
			return FALSE;

		return (bool) (strlen($str) < $val);
	}

	/**
	 * Exact Length
	 *
	 * @access	public
	 * @param	string
	 * @param	integer
	 * @return	boolean
	 */
	public function exact_length($str, $val)
	{
		if ( ! is_numeric($val))
			return FALSE;

		return (bool) (strlen($str) == $val);
	}

	/**
	 * Valid Email, Commonly used characters only
	 *
	 * @access  public
	 * @param   string
	 * @return  boolean
	 */
	public function valid_email($email)
	{
		return (bool) preg_match('/^(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}$/iD', $email);
	}

	/**
	 * Valid Email, RFC compliant version
	 *
	 * NOTE: This function is LESS strict than valid_email. Choose carefully.
	 *
	 * Originally by Cal Henderson, modified to fit Kohana syntax standards
	 *
	 * @author  Cal Henderson
	 * @link    http://www.iamcal.com/publish/articles/php/parsing_email/
	 * @link    http://www.w3.org/Protocols/rfc822/
	 *
	 * @access  public
	 * @param   string
	 * @return  boolean
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

	/**
	 * Validate IP Address
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
	 */
	public function valid_ip($ip)
	{
		return Kohana::instance()->input->valid_ip($ip);
	}

	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
	 */
	public function alpha($str)
	{
		return ctype_alpha($str);
	}

	/**
	 * Alpha-numeric
	 *
	 * @access  public
	 * @param   string
	 * @return  boolean
	 */
	public function alpha_numeric($str)
	{
		return ctype_alnum($str);
	}

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
	 */
	public function alpha_dash($str)
	{
		return (bool) preg_match('/^[-a-z0-9_]+$/iD', $str);
	}

	/**
	 * Digits: 0-9, no dots or dashes
	 *
	 * @access  public
	 * @param   integer
	 * @return  boolean
	 */
	public function digit($str)
	{
		return ctype_digit($str);
	}

	/**
	 * Numeric
	 *
	 * @access  public
	 * @param   integer
	 * @return  boolean
	 */
	public function numeric($str)
	{
		if ( ! is_numeric($str))
		    return FALSE;

		if ( ! preg_match('/^[-0-9.]+$/', $str))
		    return FALSE;

		return TRUE;
	}

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
		if ($field == '' OR $value == '' OR  ! isset($this->data[$field]))
			return '';

		if ($this->data[$field] == $value)
			return ' selected="selected"';
	}

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
		if ($field == '' OR $value == '' OR  ! isset($this->data[$field]))
			return '';

		if ($this->data[$field] == $value)
			return ' checked="checked"';
	}

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
		if ($field == '' OR $value == '' OR  ! isset($this->data[$field]))
			return '';

		if ($this->data[$field] == $value)
			return ' checked="checked"';
	}

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
		if ($this->form_safe == FALSE OR $str == '')
			return $str;

		return html::specialchars($str);
	}

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
			$this->data[$this->current_field] = '';
			return;
		}

		if (substr($str, 0, 7) != 'http://' AND substr($str, 0, 8) != 'https://')
		{
			$str = 'http://'.$str;
		}

		$this->data[$this->current_field] = $str;
	}

	/**
	 * Strip Image Tags
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function strip_image_tags($str)
	{
		$this->data[$this->current_field] = security::strip_image_tags($str);
	}

	/**
	 * XSS Clean
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function xss_clean($str)
	{
		$this->data[$this->current_field] = Kohana::instance()->input->xss_clean($str);
	}

	/**
	 * Convert PHP tags to entities
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function encode_php_tags($str)
	{
		$this->data[$this->current_field] = security::encode_php_tags($str);
	}

} // End Validation Class