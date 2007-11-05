<?php defined('SYSPATH') or die('No direct script access.');

class Download_Controller extends Controller {

	protected $auto_render = TRUE;

	public function index()
	{
		$content = new View('pages/download');

		// Download formats
		$content->formats = array
		(
			'zip' => 'Zip Archive'
		);

		// Supported languages
		$content->languages = array
		(
			'en_US' => 'English (US)',
			'nl_NL' => 'Dutch'
		);

		// Minimal group
		$groups['minimal'] = array
		(
			'index',
			'application' => array
			(
				'config' => array
				(
					'config'
				),
				'controllers' => array(),
				'logs' => array(),
				'models' => array(),
				'views' => array()
			),
			'system' => array
			(
				'config' => array
				(
					'cookie',
					'locale',
					'log',
					'mimes',
					'routes',
					'session',
					'user_agents'
				),
				'core' => TRUE,
				'helpers' => array
				(
					'arr',
					'cookie',
					'form',
					'html',
					'security',
					'url',
					'valid'
				),
				'i18n' => array
				(
					'core',
					'errors',
					'session',
					'validation'
				),
				'libraries' => array
				(
					'drivers' => array
					(
						'Session',
						'Session_Cookie'
					),
					'Controller',
					'Input',
					'Loader',
					'Model',
					'Router',
					'Session',
					'URI',
					'User_agent',
					'Validation',
					'View'
				),
				'views' => array
				(
					'kohana_error_page'
				)
			)
		);

		// Standard Group
		$groups['standard'] = array_merge_recursive($groups['minimal'], array
		(
			'application' => array
			(
				'cache' => array(),
				'helpers' => array(),
				'hooks' => array(),
				'libraries' => array(),
			),
			'system' => array
			(
				'config' => array
				(
					'database',
					'encryption',
					'hooks',
					'pagination',
					'upload'
				),
				'helpers' => array
				(
					'date',
					'download',
					'feed',
					'inflector',
					'text'
				),
				'i18n' => array
				(
					'archive',
					'calendar',
					'database',
					'encrypt',
					'inflector',
					'pagination',
					'profiler'
				),
				'libraries' => array
				(
					'drivers' => array
					(
						'Archive',
						'Archive_Zip',
						'Database',
						'Database_Mysql',
						'Session_Database'
					),
					'Archive',
					'Calendar',
					'Database',
					'Encrypt',
					'Pagination',
					'Profiler'
				),
				'views' => array
				(
					'kohana_holiday',
					'kohana_profiler',
					'pagination' => TRUE
				)
			)
		));

		$zip = new Archive('zip');
		foreach($this->_build($groups['standard']) as $path)
		{
			$zip->add($path, FALSE);
		}
		$zip->download('Kohana.zip');
		die;

		// Add content to view
		$this->template->set('content', $content);
	}

	protected function _build($paths, $lang = 'en_US', $prefix = '')
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