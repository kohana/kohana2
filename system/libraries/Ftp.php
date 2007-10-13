<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana: The swift, small, and secure PHP5 framework
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  Copyright (c) 2007 Kohana Team
 * @link       http://kohanaphp.com
 * @license    http://kohanaphp.com/license.html
 * @since      Version 2.0
 * @filesource
 * $Id$
 */

/**
 * FTP Class
 *
 * @category    Libraries
 * @author      Rick Ellis, Kohana Team
 * @copyright   Copyright (c) 2006, EllisLab, Inc.
 * @license     http://www.codeigniter.com/user_guide/license.html
 * @link        http://kohanaphp.com/user_guide/en/libraries/ftp.html
 */
class FTP_Core {

	private $hostname	= '';
	private $username	= '';
	private $password	= '';
	private $port		= 21;
	private $passive	= TRUE;
	private $debug		= FALSE;
	private $conn_id	= FALSE;

	/**
	 * Constructor - Sets Preferences
	 *
	 * The constructor can be passed an array of config values
	 */
	public function __construct($config = array())
	{
		if (count($config) > 0)
		{
			$this->initialize($config);
		}

		Log::add('debug', 'FTP Library initialized');
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

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	 the connection values
	 * @return	boolean
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

	/**
	 * FTP Login
	 *
	 * @access	private
	 * @return	boolean
	 */
	private function login()
	{
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	/**
	 * Validates the connection ID
	 *
	 * @access	private
	 * @return	boolean
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

	/**
	 * Change direcotry
	 *
	 * The second parameter lets us momentarily turn off debugging so that
	 * this function can be used to test for the existance of a folder
	 * without throwing an error.  There's no FTP equivalent to is_dir()
	 * so we do it by trying to change to a particular directory.
	 * Internally, this paramter is only used by the "mirror" function below.
	 *
	 * @access	public
	 * @param	string
	 * @param	boolean
	 * @return	boolean
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

	/**
	 * Create a directory
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
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

	/**
	 * Upload a file to the server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	boolean
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

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	boolean
	 * @return	boolean
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

	/**
	 * Move a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	boolean
	 */
	public function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file, TRUE);
	}

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
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

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * containted within it.
	 *
	 * @access	public
	 * @param	string
	 * @return	boolean
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

	/**
	 * Set file permissions
	 *
	 * @access	public
	 * @param	string 	the file path
	 * @param	string	the permissions
	 * @return	boolean
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

	/**
	 * FTP List files in the specified directory
	 *
	 * @access	public
	 * @param	string path to the directory that will be listed
	 * @param	string	mode in which the results will be returned (raw or nice)
	 * @return	array
	 */
	public function list_files($path = '.', $mode = "nice")
	{
		if ( ! $this->is_conn())
			return FALSE;

		if ($mode != "nice" AND $mode != "raw")
			return FALSE;

		return ($mode == "nice") ? ftp_nlist($this->conn_id, $path) : ftp_rawlist($this->conn_id, $path, FALSE);
	}

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access	public
	 * @param	string	path to source with trailing slash
	 * @param	string	path to destination - include the base folder with trailing slash
	 * @return	boolean
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

	/**
	 * Extract the file extension
	 *
	 * @access	private
	 * @param	string
	 * @return	string
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

	/**
	 * Set the upload type
	 *
	 * @access	private
	 * @param	string
	 * @return	string
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

	/**
	 * Close the connection
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	string	path to destination
	 * @return	boolean
	 */
	public function close()
	{
		if ( ! $this->is_conn())
			return FALSE;

		@ftp_close($this->conn_id);
	}

	/**
	 * Display error message
	 *
	 * @access	private
	 * @param	string
	 * @return	boolean
	 */
	private function error($msg)
	{
		throw new Kohana_Exception('ftp.'.$msg);
	}

} // End FTP Class