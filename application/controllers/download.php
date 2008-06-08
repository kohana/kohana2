<?php defined('SYSPATH') or die('No direct script access.');

class Download_Controller extends Website_Controller {

	public $auto_render = TRUE;

	public function index()
	{
		if ( ! file_exists(APPPATH.'cache/counter.txt'))
		{
			// Create a new counter
			file_put_contents(APPPATH.'cache/counter.txt', '0');
		}

		// Load content
		$this->template
			->set('title', 'Download')
			->bind('content', $content);

		$content = View::factory('pages/download')
			->bind('download', $download)
			->bind('errors', $errors);

		// Release version, codename, and date
		$content->release_version = '2.1.2';
		$content->release_codename = 'Schneefeier';
		$content->release_date = strtotime('2008/06/08');

		// Counter
		$content->counter = file_get_contents(APPPATH.'cache/counter.txt');

		// Modules
		$content->modules = array
		(
			'Auth' => 'Simple authentication and user management. Uses <a href="http://doc.kohanaphp.com/libraries/orm">ORM</a> for models.',
			'Forge' => 'Object-oriented form generation and templating.',
			'Media' => 'Media caching, compression, and aggregation for CSS and Javascript files.',
			'Kodoc' => 'Dynamic self-generated documentation. (Beta!)',
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
			'en_GB' => 'English (UK)',
			'nl_NL' => 'Dutch',
			'fr_FR' => 'French',
			'de_DE' => 'German',
			'mk_MK' => 'Macedonian',
			'es_ES' => 'Spanish',
		);

		// Download formats
		$content->formats = array
		(
			'zip' => 'Zip Archive'
		);

		// Load validation
		$download = Validation::factory($_GET)
			->pre_filter('trim')
			->add_rules('modules.*', 'in_array['.implode(',', array_keys($content->modules)).']')
			->add_rules('format', 'required', 'length[2,3]', 'in_array['.implode(',', array_keys($content->formats)).']')
			->add_rules('vendors.*', 'length[1,64]', 'in_array['.implode(',', array_keys($content->vendors)).']')
			->add_rules('languages.*', 'required', 'length[5]', 'in_array['.implode(',', array_keys($content->languages)).']');

		if ( ! $download->submitted())
		{
			// Pre-filled data
			$download['format'] = 'zip';
			$download['languages'] = array('en_US' => 'en_US');
		}

		// These should always be arrays
		$download['modules'] = isset($download['modules']) ? (array) $download['modules'] : array();
		$download['vendors'] = isset($download['vendors']) ? (array) $download['vendors'] : array();

		if ($download->validate())
		{
			// Set the cache id
			$cache_id = 'dl--'.sha1(serialize($_GET));

			// Attempt to fetch the archive from cache
			if (($cache = $this->cache->get($cache_id)) == FALSE)
			{
				// Kohana release directory
				$source = IN_PRODUCTION
					? '/home/kohana/checkout/kohana_releases/'.$content->release_version.'/'
					: '/Volumes/Media/Sites/Kohana/releases/'.$content->release_version.'/';

				// Directory prefix that will be added to the archive as the base directory
				$prefix = 'Kohana_v'.$content->release_version.'/';

				// Initialize a new archive
				$archive = new Archive($download['format']);

				// Add the prefix directory, index.php, license, and logo
				$archive->add($source, $prefix, FALSE);
				$archive->add($source.'index.php', $prefix.'index.php');
				$archive->add($source.'Kohana License.html', $prefix.'Kohana License.html');
				$archive->add($source.'kohana.png', $prefix.'kohana.png');

				// Add application files
				$this->add_files($source, $prefix, 'application/', $archive);

				// Add the system directory
				$archive->add($source.'system', $prefix.'system', FALSE);
				foreach (glob($source.'system/*') as $file)
				{
					// Skip i18n directory, it's added manually
					if (($dir = substr($file, strrpos($file, '/') + 1)) === 'i18n' OR $dir === 'vendor')
						continue;

					// Add files
					$this->add_files($source, $prefix, 'system/'.$dir.'/', $archive);
				}

				foreach($download['languages'] as $lang)
				{
					// Add language files
					$this->add_files($source, $prefix, 'system/i18n/'.$lang.'/', $archive);
				}

				if ($module_files = $download['modules'])
				{
					// Add the modules directory
					$archive->add($source.'modules', $prefix.'modules');

					foreach ($module_files as $file)
					{
						// Add module files
						$this->add_files($source, $prefix, 'modules/'.strtolower($file).'/', $archive);
					}
				}

				if ($vendor_files = $download['vendors'])
				{
					// Add vendor directory
					$archive->add($source.'system/vendor', $prefix.'system/vendor');

					if ($key = array_search('Markdown', $vendor_files))
					{
						// This just a file, so we add it manually
						$archive->add($source.'system/vendor/Markdown.php', $prefix.'system/vendor/Markdown.php');

						// Remove it from the list
						unset($vendor_files[$key]);
					}

					foreach($vendor_files as $name)
					{
						// Add vendor files
						$this->add_files($source, $prefix, 'system/vendor/'.$content->vendors[$name]['file'].'/', $archive);
					}
				}

				// Create the archive and cache it
				$this->cache->set($cache_id, $cache = $archive->create(), array('download'));
			}

			// Increase the counter
			file_put_contents(APPPATH.'cache/counter.txt', $content->counter + 1);

			// Do this to prevent the template from trying to render and fucking up the download
			$this->auto_render = FALSE;

			// Force a download of the archive
			return download::force('Kohana_v'.$content->release_version.'.zip', $cache);
		}

		// Load errors
		$errors = $download->errors('form_errors');
	}

	protected function add_files($source, $prefix, $directory, Archive $archive)
	{
		// Open the directory
		$dir = opendir($source.$directory);

		// Loop through the directory and add each file
		while ($file = readdir($dir))
		{
			// Skip hidden directories
			if (substr($file, 0, 1) === '.')
				continue;

			// Add each file
			$archive->add($source.$directory.$file, $prefix.$directory.$file);

			if (is_dir($source.$directory.$file))
			{
				// Recursion!
				$this->add_files($source, $prefix, $directory.rtrim($file, '/').'/', $archive);
			}
		}
		closedir($dir);
	}

} // End Download_Controller