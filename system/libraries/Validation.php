<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Class: Validation
 *
 * Kohana Source Code:
 *  author    - Rick Ellis, Kohana Team
 *  copyright - Copyright (c) 2006, EllisLab, Inc.
 *  license   - <http://www.codeigniter.com/user_guide/license.html>
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

	// Result from validation rules
	protected $result;

	/**
	 * Method: __construct
	 *
	 * Parameters:
	 *  data - array to validate
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

	/**
	 * Method: __get
	 *  Magically gets a validation variable. This can be an error string or a
	 *  data field, or an array of all field data.
	 *
	 * Parameters:
	 *  key - variable name
	 *
	 * Returns:
	 *  The variable contents or NULL if the variable does not exist
	 */
	public function __get($key)
	{
		if ( ! isset($this->$key))
		{
			if ($key === 'error_string')
			{
				// Complete error message string
				$messages = FALSE;
				foreach(array_keys($this->errors) as $field)
				{
					$messages .= $this->__get($field.'_error');
				}
				return $messages;
			}
			elseif (substr($key, -6) === '_error')
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
			elseif ($key === 'data_array')
			{
				$data = array();
				foreach (array_keys($this->rules) as $key)
				{
					if (isset($this->data[$key]))
					{
						$data[$key] = $this->data[$key];
					}
				}
				return $data;
			}
		}
	}

	/**
	 * Method: set_rules
	 *  This function takes an array of key names, rules, and field names as
	 *  input and sets internal field information.
	 *
	 * Parameters:
	 *  data  - key names
	 *  rules - rules
	 *  field - field names
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
					$field = current($rules);
					$rules = next($rules);
				}
				else
				{
					$rules = current($rules);
				}
			}

			// Empty field names default to the name of the element
			$this->fields[$name] = empty($field) ? $name : $field;
			$this->rules[$name]  = $rules;

			// Prevent fields from getting the wrong name
			unset($field);
		}
	}

	/**
	 * Method: set_message
	 *  Lets users set their own error messages on the fly.
	 *  Note - The key name has to match the function name that it corresponds to.
	 *
	 * Parameters:
	 *  func    - function name
	 *  message - error message
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
	 * Method: error_format
	 *  Allows the user to change the error message format. Error formats must
	 *  contain the string "{message}" or Kohana_Exception will be triggered.
	 *
	 * Parameters:
	 *  string - error message
	 */
	public function error_format($string = '')
	{
		if (strpos((string) $string, '{message}') === FALSE)
			throw new Kohana_Exception('validation.error_format');

		$this->error_format = $string;
	}

	/**
	 * Method: add_error
	 *
	 * Parameters:
	 *  func  - function name
	 *  field - field name
	 */
	public function add_error($func, $field)
	{
		// Set the friendly field name
		$friendly = isset($this->fields[$field]) ? $this->fields[$field] : $field;

		// Fetch the message
		$message = isset($this->messages[$func]) ? $this->messages[$func] : $this->messages['unknown_error'];

		// Replacements in strings
		$replace = array_slice(func_get_args(), 1);

		if ( ! empty($replace) AND $replace[0] === $field)
		{
			// Add the friendly name instead of the field name
			$replace[0] = $friendly;
		}

		// Add the field name into the message, if there is a place for it
		$message = (strpos($message, '%s') !== FALSE) ? vsprintf($message, $replace) : $message;

		$this->errors[$field][] = $message;
	}

	/**
	 * Method: run
	 *  This function does all the work.
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function run()
	{
		// Do we even have any data to process?  Mm?
		if (count($this->data) == 0 OR count($this->rules) == 0)
		{
			return FALSE;
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
			if ( ! isset($this->data[$field]) OR $this->data[$field] == NULL)
			{
				// This field is required
				if (strpos($rules, 'required') !== FALSE)
				{
					$this->add_error('required', $field);
				}
				continue;
			}

			// Loop through the rules and process each one
			foreach(explode('|', $rules) as $rule)
			{
				// To properly handle recursion
				$this->run_rule($rule, $field);

				// Stop validating when there is an error
				if ($this->result === FALSE)
					break;
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

	/**
	 * Method: run_rule
	 *  Handles recursively calling rules on arrays of data.
	 *
	 * Parameters:
	 *  rule  - validation rule to be run on the data
	 *  field - name of field
	 */
	protected function run_rule($rule, $field)
	{
		// Use key_string to extract the field data
		$data = Kohana::key_string($field, $this->data);

		// Make sure that data input is not upload data
		if (is_array($data) AND ! (isset($data['tmp_name']) AND isset($data['error'])))
		{
			foreach($data as $key => $value)
			{
				// Recursion is fun!
				$this->run_rule($rule, $field.'.'.$key);

				if ($this->result === FALSE)
					break;
			}
		}
		else
		{
			if ($rule === 'trim' OR $rule === 'sha1' OR $rule === 'md5')
			{
				/**
				 * @todo safe_form_data
				 */
				$this->data[$field] = $rule($data);
			}

			// Handle callback rules
			$callback = FALSE;
			if (preg_match('/callback_(.+)/', $rule, $match))
			{
				$callback = $match[1];
			}

			// Handle params
			$params = FALSE;
			if (preg_match('/([^\[]*+)\[(.+)\]/', $rule, $match))
			{
				$rule   = $match[1];
				$params = explode(',', $match[2]);
			}

			// Process this field with the rule
			if ($callback !== FALSE)
			{
				if ( ! method_exists(Kohana::instance(), $callback))
					throw new Kohana_Exception('validation.invalid_rule', $callback);

				$this->result = Kohana::instance()->$callback($data, $params);
			}
			elseif ($rule === 'matches' OR $rule === 'depend_on')
			{
				$this->result = $this->$rule($field, $params);
			}
			elseif (method_exists($this, $rule))
			{
				$this->result = $this->$rule($data, $params);
			}
			elseif (is_callable($rule, TRUE))
			{
				if (strpos($rule, '::') !== FALSE)
				{
					$this->result = call_user_func(explode('::', $rule), $field);
				}
				else
				{
					$this->result = $rule($data);
				}
			}
			else
			{
				// Trying to validate with a rule that does not exist? No way!
				throw new Kohana_Exception('validation.invalid_rule', $rule);
			}
		}
	}

	/**
	 * Method: in_array
	 */
	public function in_array($data, $array = FALSE)
	{
		if (empty($array) OR ! is_array($array) OR ! in_array($data, $array))
		{
			$this->add_error('in_array', $this->current_field);
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Method: event
	 */
	public function event($data, $events = FALSE)
	{
		// Validate the events
		if (empty($events) OR ! is_array($events))
		{
			$this->add_error('event', $this->current_field);
			return FALSE;
		}

		// Run the requested events
		foreach($events as $event)
		{
			Event::run('validation.'.$event, $data);
		}
	}

	/**
	 * Method: upload
	 */
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

		if ($data === $this->data[$this->current_field])
		{
			// Clear the raw upload data, it's internal now
			$this->data[$this->current_field] = NULL;
		}

		if (is_array($data['name']))
		{
			// Handle an array of inputs
			$files = $data;
			$total = count($files['name']);

			for ($i = 0; $i < $total; $i++)
			{
				if (empty($files['name'][$i]))
					continue;

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
			return empty($this->errors);
		}

		// Validate the uploaded file
		if ( ! isset($data['tmp_name']) OR ! is_uploaded_file($data['tmp_name']))
			return FALSE;

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
				elseif (preg_match('/[0-9]+[BKMG]/i', $param))
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

		// Fetch the real upload path
		if (($upload_path = str_replace('\\', '/', realpath(Config::item('upload.upload_directory')))) == FALSE)
		{
			$data['error'] = UPLOAD_ERR_NO_TMP_DIR;
		}

		// Validate the upload path
		if ( ! is_dir($upload_path) OR ! is_writable($upload_path))
		{
			$data['error'] = UPLOAD_ERR_CANT_WRITE;
		}

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
				throw new Kohana_Exception('upload.tmp_unwritable', $upload_path);
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

		if (preg_match('/jpe?g|png|[gt]if|bmp/', implode(' ', $allowed)))
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

		if ($ext == FALSE OR ! in_array($ext, $allowed) OR array_search($mime, Config::item('mimes.'.$ext)) === NULL)
		{
			$this->add_error('invalid_type', $this->current_field);
			return FALSE;
		}

		// Removes spaces from the filename if configured to do so
		$filename = Config::item('upload.remove_spaces') ? preg_replace('/\s+/', '_', $data['name']) : $data['name'];

		// Change the filename to a full path name
		$filename = $upload_path.'/'.$filename;

		// Move the upload file to the new location
		move_uploaded_file($data['tmp_name'], $filename);

		if ( ! empty($this->data[$this->current_field]))
		{
			// Conver the returned data into an array
			$this->data[$this->current_field] = array($this->data[$this->current_field]);
		}

		// Set the data to the current field name
		if (is_array($this->data[$this->current_field]))
		{
			$this->data[$this->current_field][] = $filename;
		}
		else
		{
			$this->data[$this->current_field] = $filename;
		}

		return TRUE;
	}

	/**
	 * Method: required
	 *
	 * Parameters:
	 *  str    - string to validate
	 *  length -
	 *
	 * Returns:
	 *  TRUE or FALSE
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
			elseif (strlen($str) !== (int) current($length))
			{
				// Test exact length
				$this->add_error('exact_length', $this->current_field, (int) current($length));
				return FALSE;
			}
		}
		else
		{
			return TRUE;
		}
	}

	/**
	 * Method: matches
	 *  Match one field to another.
	 *
	 * Parameters:
	 *  field - first field
	 *  match - field to match to first
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function matches($field, $match)
	{
		$match = trim(current($match));

		if ((isset($this->data[$field]) AND $this->data[$field] === $this->data[$match])
		OR ( ! isset($this->data[$field]) AND ! isset($this->data[$match])))
		{
			return TRUE;
		}
		else
		{
			$this->add_error('matches', $field, $match);
			return FALSE;
		}
	}

	/**
	 * Method: min_length
	 *  Check a string for a minimum length.
	 *
	 * Parameters:
	 *  str - string to validate
	 *  val - minimum length
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function min_length($str, $val)
	{
		$val = is_array($val) ? (string) current($val) : FALSE;

		if (ctype_digit($val))
		{
			if (utf8::strlen($str) >= $val)
				return TRUE;
		}

		$this->add_error('min_length', $this->current_field, (int) $val);
		return FALSE;
	}

	/**
	 * Method: max_length
	 *  Check a string for a maximum length.
	 *
	 * Parameters:
	 *  str - string to validate
	 *  val - maximum length
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function max_length($str, $val)
	{
		$val = is_array($val) ? (string) current($val) : FALSE;

		if (ctype_digit($val))
		{
			if (utf8::strlen($str) <= $val)
				return TRUE;
		}

		$this->add_error('max_length', $this->current_field, (int) $val);
		return FALSE;
	}

	/**
	 * Method: exact_length
	 *  Check a string for an exact length.
	 *
	 * Parameters:
	 *  str - string to validate
	 *  val - length
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function exact_length($str, $val)
	{
		$val = is_array($val) ? (string) current($val) : FALSE;

		if (ctype_digit($val))
		{
			if (utf8::strlen($str) == $val)
				return TRUE;
		}

		$this->add_error('exact_length', $this->current_field, (int) $val);
		return FALSE;
	}

	/**
	 * Method: valid_url
	 *  Valid URL.
	 *
	 * Parameters:
	 *  url    - URL
	 *  scheme - protocol
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function valid_url($url, $scheme = '')
	{
		if (empty($scheme))
		{
			$scheme = 'http';
		}

		if (is_array($scheme))
		{
			$scheme = current($scheme);
		}

		if (valid::url($url, $scheme))
			return TRUE;

		$this->add_error('valid_url', $this->current_field, $scheme, $url);
		return FALSE;
	}

	/**
	 * Method: valid_email
	 *  Valid Email, Commonly used characters only.
	 *
	 * Parameters:
	 *  email - email address
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function valid_email($email)
	{
		if (valid::email($email))
			return TRUE;

		$this->add_error('valid_email', $this->current_field);
		return FALSE;
	}

	/**
	 * Method: valid_email_rfc
	 *  Valid Email, RFC compliant version
	 *
	 * Parameters:
	 *  email - email address
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function valid_email_rfc($email)
	{
		if (valid::email_rfc($email))
			return TRUE;

		$this->add_error('valid_email', $this->current_field);
		return FALSE;
	}

	/**
	 * Method: valid_ip
	 *  Validate IP Address.
	 *
	 * Parameters:
	 *  ip - ip address
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function valid_ip($ip)
	{
		if (valid::ip($ip))
			return TRUE;

		$this->add_error('valid_ip', $this->current_field);
		return FALSE;
	}

	/**
	 * Method: alpha
	 *  Alphabetic characters only.
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function alpha($str)
	{
		if (valid::alpha($str))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'alphabetical');
		return FALSE;
	}

	/**
	 * Method: utf8_alpha
	 *  Alphabetic characters only (UTF-8 compatible).
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function utf8_alpha($str)
	{
		if (valid::alpha($str, TRUE))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'alphabetical');
		return FALSE;
	}

	/**
	 * Method: alpha_numeric
	 *  Alphabetic and numeric characters only.
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function alpha_numeric($str)
	{
		if (valid::alpha_numeric($str))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'alphabetical');
		return FALSE;
	}

	/**
	 * Method: utf8_alpha_numeric
	 *  Alphabetic and numeric characters only (UTF-8 compatible).
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function utf8_alpha_numeric($str)
	{
		if (valid::alpha_numeric($str, TRUE))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'alphabetical');
		return FALSE;
	}

	/**
	 * Method: alpha_dash
	 *  Alpha-numeric with underscores and dashes.
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function alpha_dash($str)
	{
		if (valid::alpha_dash($str))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'alphabetical, dash, and underscore');
		return FALSE;
	}

	/**
	 * Method: utf8_alpha_dash
	 *  Alpha-numeric with underscores and dashes (UTF-8 compatible).
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function utf8_alpha_dash($str)
	{
		if (valid::alpha_dash($str, TRUE))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'alphabetical, dash, and underscore');
		return FALSE;
	}

	/**
	 * Method: digit
	 *  Digits 0-9, no dots or dashes.
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function digit($str)
	{
		if (valid::digit($str))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'digit');
		return FALSE;
	}

	/**
	 * Method: utf8_digit
	 *  Digits 0-9, no dots or dashes (UTF-8 compatible).
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function utf8_digit($str)
	{
		if (valid::digit($str, TRUE))
			return TRUE;

		$this->add_error('valid_type', $this->current_field, 'digit');
		return FALSE;
	}

	/**
	 * Method: numeric
	 *  Digits 0-9 (negative and decimal numbers allowed).
	 *
	 * Parameters:
	 *  str - string to validate
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function numeric($str)
	{
		if (valid::numeric($str))
		    return TRUE;

		$this->add_error('valid_type', $this->current_field, 'numeric');
		return FALSE;
	}

	/**
	 * Method: range
	 *  Test that a field is between a range.
	 *
	 * Parameters:
	 *  num    - number to validate
	 *  ranges - ranges
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function range($num, $ranges)
	{
		if (is_array($ranges) AND ! empty($ranges))
		{
			// Number is always an integer
			$num = (float) $num;

			foreach($ranges as $range)
			{
				list($low, $high) = explode(':', $range, 2);

				if ($low == 'FALSE' AND $num <= (float) $high)
				{
					return TRUE;
				}
				elseif ($high == 'FALSE' AND $num >= (float) $low)
				{
					return TRUE;
				}
				elseif ($num >= (float) $low AND $num <= (float) $high)
				{
					return TRUE;
				}
			}
		}

		$this->add_error('range', $this->current_field);
		return FALSE;
	}

	/**
	 * Method: depends_on
	 *
	 * Parameters:
	 *  field     - first field
	 *  depend_on - field which the first field is depend on it
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function depends_on($field, $depends_on)
	{
		$depends_on = trim(current($depends_on));

		if ($depends_on != NULL AND isset($this->data[$field]) AND isset($this->data[$depends_on]))
		{
			return TRUE;
		}

		$depends_on = isset($this->fields[$depends_on]) ? $this->fields[$depends_on] : $depends_on;

		$this->add_error('depends_on', $field, $depends_on);
		return FALSE;
	}

	/**
	 * Method: regex
	 *  Test a field against a regex rule
	 *
	 * Parameters:
	 *  str   - string to test
	 *  regex - regular expression to run
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function regex($str, $regex)
	{
		if ( ! empty($regex))
		{
			// Only one regex validation per field
			$regex = current($regex);

			// Find a usable delimiter
			foreach (str_split('~#|!:;,./=-+*?\'"$', 1) as $delim)
			{
				if (strpos($regex, $delim) === FALSE)
				{
					if (preg_match($delim.$regex.$delim, $str))
					{
						// Regex matches, return
						return TRUE;
					}
					break;
				}
			}
		}

		$this->add_error('regex', $this->current_field);
		return FALSE;
	}

	/**
	 * Method: prep_for_form
	 *  This function allows HTML to be safely shown in a form.
	 *  Special characters are converted.
	 *
	 * Parameters:
	 *  str - HTML
	 *
	 * Returns:
	 *  Prepped HTML
	 */
	public function prep_for_form($str = '')
	{
		if ($this->form_safe == FALSE OR $str == '')
			return $str;

		return html::specialchars($str);
	}

	/**
	 * Method: prep_url
	 *
	 * Parameters:
	 *  str - URL
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
	 * Method: strip_image_tags
	 *  Strip image tags from string.
	 *
	 * Parameters:
	 *  str - string
	 */
	public function strip_image_tags($str)
	{
		$this->data[$this->current_field] = security::strip_image_tags($str);
	}

	/**
	 * Method: xss_clean
	 *  XSS clean string.
	 *
	 * Parameters:
	 *  str - string
	 */
	public function xss_clean($str)
	{
		$this->data[$this->current_field] = Kohana::instance()->input->xss_clean($str);
	}

	/**
	 * Method: encode_php_tags
	 *  Convert PHP tags to entities.
	 *
	 * Parameters:
	 *  str - string
	 */
	public function encode_php_tags($str)
	{
		$this->data[$this->current_field] = security::encode_php_tags($str);
	}

} // End Validation Class
