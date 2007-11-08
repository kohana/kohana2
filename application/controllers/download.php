<?php defined('SYSPATH') or die('No direct script access.');

class Download_Controller extends Controller {

	protected $auto_render = TRUE;

	public function index()
	{
		if ( ! is_writable('application/cache/counter.txt'))
		{
			throw new Kohana_User_Exception
			(
				'Counter Unwritable',
				'Please make the file application/cache/counter.txt writable!'
			);
		}

		// Load content
		$content = new View('pages/download');

		// Set up groups array
		$groups = array
		(
			'minimal' => array
			(
				'index.php',
				'application',
				'application/config',
				'application/config/config.php',
				'application/controllers',
				'application/controllers/welcome.php',
				'application/logs',
				'application/models',
				'application/views',
				'system',
				'system/config',
				'system/config/cookie.php',
				'system/config/locale.php',
				'system/config/log.php',
				'system/config/mimes.php',
				'system/config/routes.php',
				'system/config/session.php',
				'system/config/user_agents.php',
				'system/helpers',
				'system/helpers/arr.php',
				'system/helpers/cookie.php',
				'system/helpers/form.php',
				'system/helpers/html.php',
				'system/helpers/security.php',
				'system/helpers/url.php',
				'system/helpers/valid.php',
				'system/i18n',
				'system/libraries',
				'system/libraries/drivers',
				'system/libraries/drivers/Session.php',
				'system/libraries/drivers/Session_Cookie.php',
				'system/libraries/Controller.php',
				'system/libraries/Input.php',
				'system/libraries/Loader.php',
				'system/libraries/Model.php',
				'system/libraries/Router.php',
				'system/libraries/Session.php',
				'system/libraries/URI.php',
				'system/libraries/User_agent.php',
				'system/libraries/Validation.php',
				'system/libraries/View.php',
				'system/views',
				'system/views/kohana_error_page.php'
			),
			'standard' => array()
		);

		// Add core files
		foreach(Kohana::list_files('core') as $file)
		{
			$groups['minimal'][] = substr($file, strlen(DOCROOT.'kohana_trunk/'));
		}

		// Standard Group
		$groups['standard'] = array_merge($groups['minimal'], array
		(
			'application/helpers',
			'application/hooks',
			'application/libraries',
			'application/controllers/examples.php',
			'system/config/database.php',
			'system/config/encryption.php',
			'system/config/hooks.php',
			'system/config/pagination.php',
			'system/helpers/date.php',
			'system/helpers/download.php',
			'system/helpers/feed.php',
			'system/helpers/inflector.php',
			'system/helpers/text.php',
			'system/libraries/drivers/Archive.php',
			'system/libraries/drivers/Archive_Zip.php',
			'system/libraries/drivers/Database.php',
			'system/libraries/drivers/Database_Mysql.php',
			'system/libraries/drivers/Session_Database.php',
			'system/libraries/Archive.php',
			'system/libraries/Calendar.php',
			'system/libraries/Database.php',
			'system/libraries/Encrypt.php',
			'system/libraries/Pagination.php',
			'system/libraries/Profiler.php',
			'system/views',
			'system/views/kohana_holiday.php',
			'system/views/kohana_profiler.php',
			'system/views/pagination',
			'system/views/pagination/classic.php',
			'system/views/pagination/digg.php',
			'system/views/pagination/extended.php',
			'system/views/pagination/punbb.php'
		));

		// Language files for each group
		$group_langs = array
		(
			'minimal' => array
			(
				'core',
				'errors',
				'session',
				'validation'
			),
			'standard' => array
			(
				'archive',
				'calendar',
				'database',
				'encrypt',
				'inflector',
				'pagination',
				'profiler'
			)
		);

		// Vendor resources
		$content->vendors = array
		(
			'Markdown' => array
			(
				'link' => 'http://michelf.com/projects/php-markdown/',
				'description' => 'Text formatting tool that offers text to HTML markup with a natural syntax.',
				'file' => 'Markdown.php'
			),
			'SwiftMailer' => array
			(
				'link' => 'http://www.swiftmailer.org/',
				'description' => 'Feature-rich emailing library that supports HTML email, STMP connections, and many other features.',
				'file' => 'swift'
			),
			'HTMLPurifier' => array
			(
				'link' => 'http://htmlpurifier.org/',
				'description' => 'Standards-compliant HTML filtering library that offers XSS (Cross Site Scripting) prevention and XHTML normalization.',
				'file' => 'htmlpurifier'
			)
		);

		// Supported languages
		$content->languages = array
		(
			'en_US' => 'English (US)',
			'fr_FR' => 'French',
			'nl_NL' => 'Dutch',
			'mk_MK' => 'Macedonian'
		);

		// Download formats
		$content->formats = array
		(
			'zip' => 'Zip Archive'
		);

		if (empty($_POST))
		{
			// Fake POST data, for validation
			$_POST = array
			(
				'group' => 'standard',
				'format' => 'zip',
				'languages' => array
				(
					'en_US' => '1'
				)
			);

			// Do not validate fake POST data
			$validate = FALSE;
		}
		else
		{
			$validate = TRUE;
		}

		// Load validation
		$this->load->library('validation');

		// Set rules
		$this->validation->set_rules(array
		(
			'group'     => 'required[2,12]|in_array['.implode(',', array_keys($groups)).']',
			'format'    => 'required[2,3]|in_array['.implode(',', array_keys($content->formats)).']',
			'languages' => 'required[5]|in_array['.implode(',', array_keys($content->languages)).']'
		));

		if ($validate AND $this->validation->run())
		{
			// Get current directory for return
			$return_dir = getcwd();

			// Change to the trunk directory
			chdir('kohana_trunk');

			// Initialize a new archive
			$archive = new Archive($this->validation->format);

			// Add group files
			foreach($groups[$this->validation->group] as $path)
			{
				$archive->add($path, FALSE);
			}

			// Add language dirs
			foreach($this->validation->languages as $lang)
			{
				$archive->add('system/i18n/'.$lang, FALSE);
			}

			// Add language files
			foreach($group_langs[$this->validation->group] as $file)
			{
				foreach($this->validation->languages as $lang)
				{
					$archive->add('system/i18n/'.$lang.'/'.$file.EXT, FALSE);
				}
			}

			if ($vendor_files = $this->validation->vendor)
			{
				// Add vendor directory
				$archive->add('system/vendor', FALSE);

				foreach($vendor_files as $name)
				{
					// Add vendor files
					$archive->add('system/vendor/'.$content->vendors[$name]['file']);
				}
			}

			// Force a download of the archive
			$archive->download('Kohana_v'.KOHANA_VERSION.'.zip');

			// Return to the original directory
			chdir($return_dir);

			// Increase the counter
			file_put_contents('application/cache/counter.txt', file_get_contents('application/cache/counter.txt') + 1);

			// Do this to prevent the template from trying to render and fucking up the download
			$this->auto_render = FALSE;
			return;
		}

		// Add content to view
		$this->template->set('content', $content);
	}

	protected function _build($paths, $langs = 'en_US', $prefix = '')
	{
		$files = array();
		foreach($paths as $dir => $file)
		{
			if (is_numeric($dir))
			{
				// Add file
				$files[] = $prefix.$file.EXT;
			}
			else
			{
				if ($dir === 'i18n')
				{
					// Add i18n dir
					$files[] = $prefix.$dir.'/';

					// Add the language name to the dir
					$dir .= '/'.$lang;
				}

				// Add the current dir to the package
				$files[] = $prefix.$dir.'/';

				if ($dir === 'core')
				{
					foreach(Kohana::list_files('core') as $file)
					{
						$files[] = substr($file, strlen(DOCROOT));
					}
				}
				elseif (is_array($file))
				{
					if (empty($file))
						continue;

					// Recursion, for files in subdirs
					$files = array_merge($files, $this->_build($file, $lang, $prefix.$dir.'/'));
				}
			}
		}
		return $files;
	}

} // End Download_Controller