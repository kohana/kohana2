<?php defined('SYSPATH') or die('No direct script access.');

class Form_Upload_Core extends Form_Input {

	protected $data = array
	(
		'class' => 'upload',
		'value' => '',
	);

	protected $protect = array('type', 'label', 'value');

	protected $upload;
	protected $directory;

	public function __construct($name)
	{
		parent::__construct($name);

		if ( ! empty($_FILES[$name]))
		{
			if (empty($_FILES[$name]['tmp_name']) OR is_uploaded_file($_FILES[$name]['tmp_name']))
			{
				// Cache the upload data in this object
				$this->upload = $_FILES[$name];

				// Hack to allow file-only inputs, where no POST data is present
				$_POST[$name] = $this->upload['name'];
			}
			else
			{
				// Attempt to delete the invalid file
				is_writable($_FILES[$name]['tmp_name']) and unlink($_FILES[$name]['tmp_name']);

				// Invalid file upload, possible hacking attempt
				unset($_FILES[$name]);
			}
		}
	}

	public function directory($dir = NULL)
	{
		// Use the global upload directory by default
		empty($dir) and $dir = Config::item('upload.upload_directory');

		// Make the path asbolute and normalize it
		$dir = str_replace('\\', '/', realpath($dir)).'/';

		// Make sure the upload director is valid and writable
		if ($dir === '/' OR ! is_dir($dir) OR ! is_writable($dir))
			throw new Kohana_Exception('forge.upload.unwritable', $dir);

		$this->directory = $dir;
	}

	public function validate()
	{
		// The upload directory must always be set
		empty($this->directory) and $this->directory();

		if ($status = parent::validate())
		{
			// No filename means an invalid upload
			$filename = '';

			if ($this->upload['error'] === UPLOAD_ERR_OK)
			{
				// Set the filename to the original name
				$filename = $this->upload['name'];

				if (Config::item('upload.remove_spaces'))
				{
					// Remove spaces, due to global upload configuration
					$filename = preg_replace('/\s+/', '_', $this->data['value']);
				}

				// Move the uploaded file to the upload directory
				move_uploaded_file($this->upload['tmp_name'], $filename = $this->directory.$filename);
			}

			// Reset the POST value to the new filename
			$this->data['value'] = $_POST[$this->data['name']] = $filename;
		}

		return $status;
	}

	protected function rule_required()
	{
		if (empty($this->upload) OR $this->upload['error'] === UPLOAD_ERR_NO_FILE)
		{
			$this->errors['required'] = TRUE;
		}
	}

	public function rule_allow()
	{
		if (empty($this->upload['tmp_name']))
			return;

		if (defined('FILEINFO_MIME'))
		{
			$info = new finfo(FILEINFO_MIME);

			// Get the mime type using Fileinfo
			$mime = $info->file($this->upload['tmp_name']);

			$info->close();
		}
		elseif (ini_get('magic.mime') AND function_exists('mime_content_type'))
		{
			// Get the mime type using magic.mime
			$mime = mime_content_type($this->upload['tmp_name']);
		}
		else
		{
			// Trust the browser
			$mime = $this->upload['type'];
		}

		// Allow nothing by default
		$allow = FALSE;

		foreach (func_get_args() as $type)
		{
			if (in_array($mime, Config::item('mimes.'.$type)))
			{
				// Type is valid
				$allow = TRUE;
				break;
			}
		}

		if ($allow === FALSE)
		{
			$this->errors['allow'] = TRUE;
		}
	}

	public function rule_size($size)
	{
		$bytes = (int) $size;

		switch (substr($size, -1))
		{
			case 'G': $bytes *= 1024;
			case 'M': $bytes *= 1024;
			default:  $bytes *= 1024;
		}

		if (empty($this->upload['size']) OR $this->upload['size'] > $bytes)
		{
			$this->errors['size'] = $size;
		}
	}

	public function html()
	{
		return form::upload($this->data);
	}

} // End Form Upload