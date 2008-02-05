<?php defined('SYSPATH') or die('No direct script access.');

class Download_Controller extends Controller {

	protected $auto_render = TRUE;

	public function index()
	{
		if ( ! file_exists(APPPATH.'cache/counter.txt'))
		{
			// Create a new counter
			file_put_contents(APPPATH.'cache/counter.txt', '0');
		}

		// Load content
		$content = new View('pages/download');

		// Kohana trunk
		$kohana_trunk = '/usr/home/wgilk/svn_checkout/kohana_trunk/';

		// Counter
		$content->counter = file_get_contents(APPPATH.'cache/counter.txt');

		// Pull date
		$content->sync_date = strtotime('2007/11/20');

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
				'system/config/hooks.php',
				'system/config/locale.php',
				'system/config/log.php',
				'system/config/mimes.php',
				'system/config/routes.php',
				'system/config/session.php',
				'system/config/user_agents.php',
				'system/core',
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
				'system/models',
				'system/views',
				'system/views/kohana_error_page.php'
			),
			'standard' => array
			(
				'application/helpers',
				'application/hooks',
				'application/libraries',
				'application/controllers/examples.php',
				'application/views/viewinview',
				'application/views/viewinview/container.php',
				'application/views/viewinview/footer.php',
				'application/views/viewinview/header.php',
				'system/config/database.php',
				'system/config/encryption.php',
				'system/config/pagination.php',
				'system/config/profiler.php',
				'system/config/upload.php',
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
				'system/models/form.php',
				'system/views/kohana_form.php',
				'system/views/kohana_holiday.php',
				'system/views/kohana_profiler.php',
				'system/views/pagination',
				'system/views/pagination/classic.php',
				'system/views/pagination/digg.php',
				'system/views/pagination/extended.php',
				'system/views/pagination/punbb.php'
			)
		);

		// Add core files
		foreach(glob($kohana_trunk.'system/core/*') as $file)
		{
			$groups['minimal'][] = substr($file, strrpos($file, 'system/'));
		}

		// Standard should have all the files that minimal does
		$groups['standard'] = array_merge($groups['minimal'], $groups['standard']);

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

		// Standard group should have all the files that minimal does
		$group_langs['standard'] = array_merge($group_langs['minimal'], $group_langs['standard']);

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
			'zh_CN' => 'Chinese',
			'fr_FR' => 'French',
			'nl_NL' => 'Dutch',
			'mk_MK' => 'Macedonian',
			'pl_PL' => 'Polish'
		);

		// Download formats
		$content->formats = array
		(
			'zip' => 'Zip Archive'
		);

		if (empty($_GET))
		{
			// Fake validation data, so that we can pre-fill the form
			$validate = array
			(
				'group' => 'standard',
				'format' => 'zip',
				'languages' => array
				(
					'en_US' => '1'
				)
			);
		}
		else
		{
			// Validate GET data
			$validate = $_GET;
		}

		// Load validation
		$this->load->library('validation', $validate);

		// Set rules
		$this->validation->set_rules(array
		(
			'group'     => 'required[2,12]|in_array['.implode(',', array_keys($groups)).']',
			'format'    => 'required[2,3]|in_array['.implode(',', array_keys($content->formats)).']',
			'languages' => 'required[5]|in_array['.implode(',', array_keys($content->languages)).']'
		));

		if ( ! empty($_GET) AND $this->validation->run())
		{
			// Set the cache id
			$cache_id = 'dl--'.sha1(serialize($_GET));

			// Attempt to fetch the archive from cache
			if (($cache = $this->cache->get($cache_id)) == FALSE)
			{
				// Get current directory for return
				$return_dir = getcwd();

				// Change to the trunk directory
				chdir($kohana_trunk);

				// Initialize a new archive
				$archive = new Archive($this->validation->format);

				// Add group files
				foreach($groups[$this->validation->group] as $path)
				{
					$archive->add($path);
				}

				// Add language dirs
				foreach($this->validation->languages as $lang)
				{
					$archive->add('system/i18n/'.$lang);
				}

				// Add language files
				foreach($group_langs[$this->validation->group] as $file)
				{
					foreach($this->validation->languages as $lang)
					{
						$archive->add('system/i18n/'.$lang.'/'.$file.EXT);
					}
				}

				if ($vendor_files = $this->validation->vendor)
				{
					// Add vendor directory
					$archive->add('system/vendor');

					foreach($vendor_files as $name)
					{
						// Add vendor files
						$archive->add('system/vendor/'.$content->vendors[$name]['file'], NULL, TRUE);
					}
				}

				// Create the archive and cache it
				$this->cache->set($cache_id, $cache = $archive->create(), array('download'));

				// Return to the original directory
				chdir($return_dir);
			}

			// Increase the counter
			file_put_contents(APPPATH.'cache/counter.txt', $content->counter + 1);

			// Do this to prevent the template from trying to render and fucking up the download
			$this->auto_render = FALSE;

			// Force a download of the archive
			return download::force('Kohana_v2.0_'.date('Y-m-d', $content->sync_date).'.zip', $cache);
		}

		// Set page title and content
		$this->template->set(array
		(
			'title'   => 'Download',
			'content' => $content
		));
	}

} // End Download_Controller