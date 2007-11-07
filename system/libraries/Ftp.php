<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: FTP
 *
 * Kohana Source Code:
 *  author    - Rick Ellis, Kohana Team
 *  copyright - Copyright (c) 2006, EllisLab, Inc.
 *  license   - <http://www.codeigniter.com/user_guide/license.html>
 */
class FTP_Core {

	private $hostname = '';
	private $username = '';
	private $password = '';
	private $port     = 21;
	private $passive  = TRUE;
	private $debug    = FALSE;
	private $conn_id  = FALSE;

	/*
	 * Constructor: __construct
	 *  Sets Preferences.
	 *
	 * Parameters:
	 *  config - custom configuration
	 */
	public function __construct($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		Log::add('debug', 'FTP Library initialized');
	}

	/*
	 * Method: initialize
	 *  Initialize preferences.
	 *
	 * Parameters:
	 *  config - custom configuration
	 */
	public function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}

		// Prep the hostname
		$this->hostname = preg_replace('|^[^:]++://|', '', $this->hostname); 
	}

	/*
	 * Method: connect
	 *  Connect to the FTP.
	 *
	 * Parameters:
	 *  config - the connection values
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function connect($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		if (FALSE === ($this->conn_id = @ftp_connect($this->hostname, $this->port)))
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_connect');
			}
			return FALSE;
		}

		if ( ! $this->login())
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_login');
			}
			return FALSE;
		}

		// Set passive mode if needed
		if ($this->passive == TRUE)
		{
			ftp_pasv($this->conn_id, TRUE);
		}

		return TRUE;
	}

	/*
	 * Method: login
	 *  Login to the FTP.
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	private function login()
	{
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	/*
	 * Method: is_conn
	 *  Validates the connection ID.
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	private function is_conn()
	{
		if ( ! is_resource($this->conn_id))
		{
			if ($this->debug == TRUE)
			{
				$this->error('no_connection');
			}
			return FALSE;
		}
		
		return TRUE;
	}

	/*
	 * Method: changedir
	 *  The second parameter lets us momentarily turn off debugging so that
	 *  this function can be used to test for the existence of a folder
	 *  without throwing an error.  There's no FTP equivalent to is_dir()
	 *  so we do it by trying to change to a particular directory.
	 *  Internally, this parameter is only used by the "mirror" function.
	 *
	 * Parameters:
	 *  path          - path of directory
	 *  supress_debug - supress debug message
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function changedir($path = '', $supress_debug = FALSE)
	{
		if (empty($path) OR ! $this->is_conn())
		{
			return FALSE;
		}

		$result = @ftp_chdir($this->conn_id, $path);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE AND $supress_debug == FALSE)
			{
				$this->error('unable_to_changedir');
			}
			return FALSE;
		}

		return TRUE;
	}

	/*
	 * Method: mkdir
	 *  Create a directory.
	 *
	 * Parameters:
	 *  path        - path of directory
	 *  permissions - permissions to give directory
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function mkdir($path = '', $permissions = NULL)
	{
		if (empty($path) OR ! $this->is_conn())
			return FALSE;

		$result = @ftp_mkdir($this->conn_id, $path);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_makdir');
			}
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($path, (int) $permissions);
		}

		return TRUE;
	}

	/*
	 * Method: upload
	 *  Upload a file to the server.
	 *
	 * Parameters:
	 *  locpath     - path of file to upload
	 *  rempath     - path on FTP to upload to
	 *  mode        - transfer mode
	 *  permissions - permissions to give file
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if ( ! $this->is_conn())
			return FALSE;

		if ( ! file_exists($locpath))
		{
			$this->error('no_source_file');
			return FALSE;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->get_extension($locpath);
			$mode = $this->set_type($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_put($this->conn_id, $rempath, $locpath, $mode);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_upload');
			}
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($rempath, (int) $permissions);
		}

		return TRUE;
	}

	/*
	 * Method: rename
	 *  Rename (or move) a file.
	 *
	 * Parameters:
	 *  old_file - old file
	 *  new_file - new file
	 *  move     - use move debug message instead of rename
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function rename($old_file, $new_file, $move = FALSE)
	{
		if ( ! $this->is_conn())
			return FALSE;

		$result = @ftp_rename($this->conn_id, $old_file, $new_file);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$msg = ($move == FALSE) ? 'unable_to_remame' : 'unable_to_move';

				$this->error($msg);
			}
			return FALSE;
		}

		return TRUE;
	}

	/*
	 * Method: move
	 *  Move a file.
	 *
	 * Parameters:
	 *  old_file - old file
	 *  new_file - new file
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file, TRUE);
	}

	/*
	 * Method: delete_file
	 *  Delete a file.
	 *
	 * Parameters:
	 *  filepath - file path
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function delete_file($filepath)
	{
		if ( ! $this->is_conn())
			return FALSE;

		$result = @ftp_delete($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_delete');
			}
			return FALSE;
		}

		return TRUE;
	}

	/*
	 * Method: delete_dir
	 *  Delete a folder and recursively delete everything (including sub-folders) contained within it.
	 *
	 * Parameters:
	 *  filepath - directory path
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function delete_dir($filepath)
	{
		if ( ! $this->is_conn())
			return FALSE;

		// Add a trailing slash to the file path
		$filepath = rtrim($filepath, '/').'/';

		$list = $this->list_files($filepath);

		if ($list !== FALSE AND count($list) > 0)
		{
			foreach ($list as $item)
			{
				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()
				if ( ! @ftp_delete($this->conn_id, $filepath.$item))
				{
					$this->delete_dir($filepath.$item);
				}
			}
		}

		$result = @ftp_rmdir($this->conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_delete');
			}
			return FALSE;
		}

		return TRUE;
	}

	/*
	 * Method: chmod
	 *  Set file permissions.
	 *
	 * Parameters:
	 *  path - path of file or directory
	 *  perm - permissions to set
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function chmod($path, $perm)
	{
		if ( ! $this->is_conn())
			return FALSE;

		// Permissions can only be set when running PHP 5
		if ( ! function_exists('chmod'))
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_chmod');
			}
			return FALSE;
		}

		$result = @ftp_chmod($this->conn_id, $perm, $path);

		if ($result === FALSE)
		{
			if ($this->debug == TRUE)
			{
				$this->error('unable_to_chmod');
			}
			return FALSE;
		}

		return TRUE;
	}

	/*
	 * Method: list_files
	 *  FTP List files in the specified directory.
	 *
	 * Parameters:
	 *  path - path to the directory
	 *  mode - mode in which the results will be returned (raw or nice)
	 *
	 * Returns:
	 *  Array of files in directory
	 */
	public function list_files($path = '.', $mode = "nice")
	{
		if ( ! $this->is_conn())
			return FALSE;

		if ($mode != "nice" AND $mode != "raw")
			return FALSE;

		return ($mode == "nice") ? ftp_nlist($this->conn_id, $path) : ftp_rawlist($this->conn_id, $path, FALSE);
	}

	/*
	 * Method: mirror
	 *  This function recursively reads a folder and everything it contains (including
	 *  sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 *  of the original file path will be recreated on the server.
	 *
	 * Parameters:
	 *  locpath - path to source with trailing slash
	 *  rempath - path to destination - include the base folder with trailing slash
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function mirror($locpath, $rempath)
	{
		if ( ! $this->is_conn())
			return FALSE;

		// Open the local file path
		if ($fp = @opendir($locpath))
		{
			// Attempt to open the remote file path.
			if ( ! $this->changedir($rempath, TRUE))
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( ! $this->mkdir($rempath) OR ! $this->changedir($rempath))
					return FALSE;
			}

			// Recursively read the local directory
			while (($file = readdir($fp)) !== FALSE)
			{
				if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.')
				{
					$this->mirror($locpath.$file."/", $rempath.$file."/");
				}
				elseif (substr($file, 0, 1) != ".")
				{
					// Get the file extension so we can se the upload type
					$ext = $this->get_extension($file);
					$mode = $this->set_type($ext);

					$this->upload($locpath.$file, $rempath.$file, $mode);
				}
			}
			return TRUE;
		}

		return FALSE;
	}

	/*
	 * Method: get_extension
	 *  Extract the file extension.
	 *
	 * Parameters:
	 *  filename - filename
	 *
	 * Returns:
	 *  Extracted file extension
	 */
	private function get_extension($filename)
	{
		if (strpos($filename, '.') === FALSE)
		{
			return 'txt';
		}

		$x = explode('.', $filename);
		return end($x);
	}

	/*
	 * Method: set_type
	 *  Returns the transfer mode for a file extension (ascii or binary).
	 *
	 * Parameters:
	 *  ext - file extension
	 *
	 * Returns:
	 *  'ascii' or 'binary'
	 */
	private function set_type($ext)
	{
		$text_types = array(
			'txt',
			'text',
			'php',
			'phps',
			'php4',
			'js',
			'css',
			'htm',
			'html',
			'phtml',
			'shtml',
			'log',
			'xml',
			'php3'
		);

		return (in_array($ext, $text_types)) ? 'ascii' : 'binary';
	}

	/*
	 * Method: close
	 *  Close the connection.
	 *
	 * Returns:
	 *  TRUE or FALSE
	 */
	public function close()
	{
		if ( ! $this->is_conn())
			return FALSE;

		@ftp_close($this->conn_id);
	}

	/*
	 * Method: error
	 *  Display error message.
	 *
	 * Parameters:
	 *  msg - error message
	 *
	 * Throws:
	 *  <Kohana_Exception>
	 */
	private function error($msg)
	{
		throw new Kohana_Exception('ftp.'.$msg);
	}

} // End FTP Class