<?php defined('SYSPATH') or die('No direct script access.');
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
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * File Uploading Class
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Uploads
 * @author		Rick Ellis, Kohana Team
 * @link		http://kohanaphp.com/user_guide/libraries/file_uploading.html
 */
class Upload_Core {

	protected $max_size         = 0;
 	protected $max_width        = 0;
	protected $max_height       = 0;
	protected $allowed_types    = '';
	protected $file_temp        = '';
	protected $file_name        = '';
	protected $orig_name        = '';
	protected $file_type        = '';
	protected $file_size        = '';
	protected $file_ext         = '';
	protected $upload_path      = '';
	protected $overwrite        = FALSE;
	protected $encrypt_name     = FALSE;
	protected $is_image         = FALSE;
	protected $image_width      = '';
	protected $image_height     = '';
	protected $image_type       = '';
	protected $image_size_str   = '';
	protected $error_msg        = array();
	protected $mimes            = array();
	protected $remove_spaces    = TRUE;
	protected $xss_clean        = FALSE;
	protected $temp_prefix      = 'tmp_upload_';

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	public function __construct($props = array())
	{
		if (count($props) > 0)
		{
			$this->initialize($props);
		}

		Log::add('debug', 'Upload Class Initialized');
	}

	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array
	 * @return	void
	 */
	public function initialize($config = array())
	{
		$defaults = array
		(
			'max_size'			=> 0,
			'max_width'			=> 0,
			'max_height'		=> 0,
			'allowed_types'		=> '',
			'file_temp'			=> '',
			'file_name'			=> '',
			'orig_name'			=> '',
			'file_type'			=> '',
			'file_size'			=> '',
			'file_ext'			=> '',
			'upload_path'		=> '',
			'overwrite'			=> FALSE,
			'encrypt_name'		=> FALSE,
			'is_image'			=> FALSE,
			'image_width'		=> '',
			'image_height'		=> '',
			'image_type'		=> '',
			'image_size_str'	=> '',
			'error_msg'			=> array(),
			'mimes'				=> array(),
			'remove_spaces'		=> TRUE,
			'xss_clean'			=> FALSE,
			'temp_prefix'		=> 'tmp_upload_'
		);

		foreach ($defaults as $key => $msg)
		{
			if (isset($config[$key]))
			{
				$method = 'set_'.$key;
				if (method_exists($this, $method))
				{
					$this->$method($config[$key]);
				}
				else
				{
					$this->$key = $config[$key];
				}
			}
			else
			{
				$this->$key = $msg;
			}
		}
	}

	/**
	 * Perform a multple file upload
	 *
	 * @access public
	 * @param array
	 * @return boolean
	 */
	public function do_mupload($field_set)
	{
		if ( ! is_array($field_set) OR empty($field_set))
		{
			throw new Kohana_Exception('field_set_empty');
		}

		$return = TRUE;
		foreach($field_set as $nice_name => $userfile)
		{
			if( ! $this->do_upload($userfile, $nice_name))
			{
				$return = FALSE;
			}
		}

		return $return;
	}

	/**
	 * Perform the file upload
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function do_upload($field = 'userfile', $nice_name = 'userfile')
	{
		// Is $_FILES[$field] set? If not, no reason to continue.
		if ( ! isset($_FILES[$field]))
		{
			$this->set_error('userfile_not_set', $nice_name, $field);
			return FALSE;
		}

		// Is the upload path valid?
		if ( ! $this->validate_upload_path())
		{
			$this->set_error('invalid_path');
			return FALSE;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if ( ! is_uploaded_file($_FILES[$field]['tmp_name']))
		{
			$error = ( ! isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

			switch($error)
			{
				case 1:
					$this->set_error('file_exceeds_limit', $nice_name);
				break;
				case 3:
					$this->set_error('file_partial', $nice_name);
				break;
				case 4:
					$this->set_error('no_file_selected', $nice_name);
				break;
				default:
					$this->set_error('no_file_selected', $nice_name);
				break;
			}

			return FALSE;
		}

		// Set the uploaded data as class variables
		$this->file_temp = $_FILES[$field]['tmp_name'];
		$this->file_name = $_FILES[$field]['name'];
		$this->file_size = $_FILES[$field]['size'];
		$this->file_type = strtolower(preg_replace('/;.*$/', '', $_FILES[$field]['type']));
		$this->file_ext	 = $this->get_extension($_FILES[$field]['name']);

		// Convert the file size to kilobytes
		if ($this->file_size > 0)
		{
			$this->file_size = round($this->file_size / 1024, 2);
		}

		// Is the file type allowed to be uploaded?
		if ( ! $this->is_allowed_filetype())
		{
			$this->set_error('invalid_filetype', $nice_name);
			return FALSE;
		}

		// Is the file size within the allowed maximum?
		if ( ! $this->is_allowed_filesize())
		{
			$this->set_error('invalid_filesize', $nice_name, $this->max_size.'KBytes');
			return FALSE;
		}

		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ( ! $this->is_allowed_dimensions())
		{
			$this->set_error('invalid_dimensions', $nice_name, $this->max_width.'x'.$this->max_height);
			return FALSE;
		}

		// Sanitize the file name for security
		$this->file_name = $this->clean_file_name($this->file_name);

		// Remove white spaces in the name
		if ($this->remove_spaces == TRUE)
		{
			$this->file_name = preg_replace('/\s+/', '_', $this->file_name);
		}

		/*
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 */
		$this->orig_name = $this->file_name;

		if ($this->overwrite == FALSE)
		{
			$this->file_name = $this->set_filename($this->upload_path, $this->file_name);

			if ($this->file_name === FALSE)
			{
				return FALSE;
			}
		}

		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 */
		if ( ! @copy($this->file_temp, $this->upload_path.$this->file_name))
		{
			if ( ! @move_uploaded_file($this->file_temp, $this->upload_path.$this->file_name))
			{
				 $this->set_error('destination_error', $nice_name);
				 return FALSE;
			}
		}

		/*
		 * Run the file through the XSS hacking filter
		 * This helps prevent malicious code from being
		 * embedded within a file.  Scripts can easily
		 * be disguised as images or other file types.
		 */
		if ($this->xss_clean == TRUE)
		{
			$this->do_xss_clean();
		}

		/*
		 * Set the finalized image dimensions
		 * This sets the image width/height (assuming the
		 * file was an image).  We use this information
		 * in the 'data' function.
		 */
		$this->set_image_properties($this->upload_path.$this->file_name);

		return TRUE;
	}

	/**
	 * Finalized Data Array
	 *
	 * Returns an associative array containing all of the information
	 * related to the upload, allowing the developer easy access in one array.
	 *
	 * @access	public
	 * @return	array
	 */
	public function data()
	{
		return array (
						'file_name'			=> $this->file_name,
						'file_type'			=> $this->file_type,
						'file_path'			=> $this->upload_path,
						'full_path'			=> $this->upload_path.$this->file_name,
						'raw_name'			=> str_replace($this->file_ext, '', $this->file_name),
						'orig_name'			=> $this->orig_name,
						'file_ext'			=> $this->file_ext,
						'file_size'			=> $this->file_size,
						'is_image'			=> $this->is_image(),
						'image_width'		=> $this->image_width,
						'image_height'		=> $this->image_height,
						'image_type'		=> $this->image_type,
						'image_size_str'	=> $this->image_size_str,
					);
	}

	/**
	 * Set Upload Path
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	protected function set_upload_path($path)
	{
		$this->upload_path = $path;
	}

	/**
	 * Set the file name
	 *
	 * This function takes a filename/path as input and looks for the
	 * existence of a file with the same name. If found, it will append a
	 * number to the end of the filename to avoid overwriting a pre-existing file.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	protected function set_filename($path, $filename)
	{
		if ($this->encrypt_name == TRUE)
		{
			mt_srand();
			$filename = md5(uniqid(mt_rand())).$this->file_ext;
		}

		if ( ! file_exists($path.$filename))
		{
			return $filename;
		}

		$filename = str_replace($this->file_ext, '', $filename);

		$new_filename = '';
		for ($i = 1; $i < 100; $i++)
		{
			if ( ! file_exists($path.$filename.$i.$this->file_ext))
			{
				$new_filename = $filename.$i.$this->file_ext;
				break;
			}
		}

		if ($new_filename == '')
		{
			$this->set_error('bad_filename');
			return FALSE;
		}
		else
		{
			return $new_filename;
		}
	}

	/**
	 * Set Maximum File Size
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */
	protected function set_max_filesize($n)
	{
		$this->max_size = abs((int) $n);
	}

	/**
	 * Set Maximum Image Width
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */
	protected function set_max_width($n)
	{
		$this->max_width = abs((int) $n);
	}

	/**
	 * Set Maximum Image Height
	 *
	 * @access	public
	 * @param	integer
	 * @return	void
	 */
	protected function set_max_height($n)
	{
		$this->max_height = abs((int) $n);
	}

	/**
	 * Set Allowed File Types
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	protected function set_allowed_types($types)
	{
		$this->allowed_types = preg_split('/[|,]+/', $types);
	}

	/**
	 * Set Image Properties
	 *
	 * Uses GD to determine the width/height/type of image
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	protected function set_image_properties($path = '')
	{
		if ( ! $this->is_image())
			return;

		if (function_exists('getimagesize'))
		{
			if (($D = @getimagesize($path)) !== FALSE)
			{
				$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

				$this->image_width		= $D[0];
				$this->image_height		= $D[1];
				$this->image_type		= ( ! isset($types[$D[2]])) ? 'unknown' : $types[$D[2]];
				$this->image_size_str	= $D[3];  // string containing height and width
			}
		}
	}

	/**
	 * Set XSS Clean
	 *
	 * Enables the XSS flag so that the file that was uploaded
	 * will be run through the XSS filter.
	 *
	 * @access	public
	 * @param	boolean
	 * @return	void
	 */
	protected function set_xss_clean($flag = FALSE)
	{
		$this->xss_clean = (bool) $flag;
	}

	/**
	 * Validate the image
	 *
	 * @access	public
	 * @return	boolean
	 */
	protected function is_image()
	{
		$img_mimes = array(
			'image/gif',
			'image/jpg',
			'image/jpe',
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/x-png'
		);

		return (in_array($this->file_type, $img_mimes, TRUE));
	}

	/**
	 * Verify that the filetype is allowed
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function is_allowed_filetype()
	{
		if (count($this->allowed_types) == 0)
		{
			$this->set_error('no_file_types');
			return FALSE;
		}

		foreach ($this->allowed_types as $msg)
		{
			$mime = $this->mimes_types(strtolower($msg));

			if (is_array($mime))
			{
				if (in_array($this->file_type, $mime, TRUE))
					return TRUE;
			}
			elseif ($mime == $this->file_type)
				return TRUE;
		}

		return FALSE;
	}

	/**
	 * Verify that the file is within the allowed size
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function is_allowed_filesize()
	{
		return ($this->max_size == 0  OR  $this->file_size <= $this->max_size);
	}

	/**
	 * Verify that the image is within the allowed width/height
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function is_allowed_dimensions()
	{
		if ( ! $this->is_image())
			return TRUE;

		if (function_exists('getimagesize'))
		{
			$D = @getimagesize($this->file_temp);

			if ($this->max_width > 0 AND $D[0] > $this->max_width)
				return FALSE;

			if ($this->max_height > 0 AND $D[1] > $this->max_height)
				return FALSE;
		}

		return TRUE;
	}

	/**
	 * Validate Upload Path
	 *
	 * Verifies that it is a valid upload path with proper permissions.
	 *
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function validate_upload_path()
	{
		if (function_exists('realpath') AND @realpath($this->upload_path) !== FALSE)
		{
			$this->upload_path = str_replace('\\', '/', realpath($this->upload_path));
		}

		if ($this->upload_path == '' OR ! @is_dir($this->upload_path))
		{
			throw new Kohana_Exception('upload.no_filepath');
		}

		if ( ! is_writable($this->upload_path))
		{
			throw new Kohana_Exception('upload.not_writable', $this->upload_path);
		}

		$this->upload_path = rtrim($this->upload_path, '/').'/';
		return TRUE;
	}

	/**
	 * Extract the file extension
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function get_extension($filename)
	{
		$x = explode('.', $filename);
		return '.'.end($x);
	}

	/**
	 * Clean the file name for security
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function clean_file_name($filename)
	{
		$bad = array(
			'<!--',
			'-->',
			'\'',
			'<',
			'>',
			'"',
			'&',
			'$',
			'=',
			';',
			'?',
			'/',
			'%20',
			'%22',
			'%3c',		// <
			'%253c', 	// <
			'%3e', 		// >
			'%0e', 		// >
			'%28', 		// (
			'%29', 		// )
			'%2528', 	// (
			'%26', 		// &
			'%24', 		// $
			'%3f', 		// ?
			'%3b', 		// ;
			'%3d'		// =
		);

		$filename = str_replace($bad, '', $filename);

		return $filename;
	}

	/**
	 * Runs the file through the XSS clean function
	 *
	 * This prevents people from embedding malicious code in their files.
	 * I'm not sure that it won't negatively affect certain files in unexpected ways,
	 * but so far I haven't found that it causes trouble.
	 *
	 * @access	public
	 * @return	void
	 */
	public function do_xss_clean()
	{
		$file = $this->upload_path.$this->file_name;

		if (filesize($file) == 0)
		{
			return FALSE;
		}

		if ( ! $fp = @fopen($file, 'rb'))
		{
			return FALSE;
		}

		flock($fp, LOCK_EX);

		$data = fread($fp, filesize($file));

		$data = Kohana::instance()->input->xss_clean($data);

		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	/**
	 * Set an error message
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public function set_error($msg, $nice_name = NULL, $extra = NULL)
	{
		if ($nice_name !== NULL)
		{
			$this->error_msg[] = Kohana::lang('upload.error_on_file', $nice_name);
		}
		
		if (is_array($msg))
		{
			foreach ($msg as $msg)
			{
				$msg = ($extra === NULL) ? Kohana::lang('upload.'.$msg) : Kohana::lang('upload.'.$msg, $extra);
				$this->error_msg[] = $msg;
				Log::add('error', $msg);
			}
		}
		else
		{
			$msg = ($extra === NULL) ? Kohana::lang('upload.'.$msg) : Kohana::lang('upload.'.$msg, $extra);
			$this->error_msg[] = $msg;
			Log::add('error', $msg);
		}
	}

	/**
	 * Display the error message
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function display_errors($open = '<p>', $close = '</p>')
	{
		$str = '';
		foreach ($this->error_msg as $msg)
		{
			$str .= $open.$msg.$close;
		}

		return $str;
	}

	/**
	 * List of Mime Types
	 *
	 * This is a list of mime types.  We use it to validate
	 * the 'allowed types' set by the developer
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function mimes_types($mime)
	{
		if (count($this->mimes) == 0)
		{
			$this->mimes = Config::item('mimes');
		}

		return ( ! isset($this->mimes[$mime])) ? FALSE : $this->mimes[$mime];
	}

} // End Upload Class