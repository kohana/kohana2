<?php
/**
 * Kohana Documentation Controller.
 *
 * $Id$
 *
 * @package    Documentation
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Controller_Kohana_Documentation extends Controller_Template {

	public $template = 'kohana_docs/template';

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	/**
	 * Index page.
	 */
	public function index()
	{
		$this->template->set(array
		(
			'title'       => Kohana::lang('kohana_docs.title'),
			'page_id'   => 'documentation',
			'breadcrumbs' => array(),
			'menu'        => '',
			'sidebar'     => '',
			'content'     => View::factory('kohana_docs/index')->render()
		));
	}

	/**
	 * User guide page.
	 *
	 * @param  string  language
	 */
	public function user_guide($language)
	{
		// Get path
		$segments = URI::instance()->segments(3);
		$path = implode('/', $segments);

		// Redirect to contents for current language
		if (empty($path))
		{
			url::redirect('docs/user_guide/'.$language.'/contents');
		}

		$page = new Kohana_Page($language, $path);

		// Breadcrumbs
		$breadcrumbs = array('docs/user_guide/'.$language.'/contents' => 'User Guide');
		$breadcrumb_path = '';
		foreach ($segments as $segment)
		{
			$breadcrumb_path .= '/'.$segment;
			$url = Kohana_Page::url($breadcrumb_path);
			$breadcrumbs[$url] = ucfirst($segment);
		}

		// Create page content
		$menu = $page->menu();
		$sidebar = $page->sidebar();
		$navigation = $page->navigation_links();

		try
		{
			// Display output
			$this->template->set(array
			(
				'language'    => $language,
				'title'       => Kohana::lang('kohana_docs.user_guide.title'),
				'page_id'   => 'user-guide',
				'breadcrumbs' => $breadcrumbs,
				'menu'        => View::factory('kohana_docs/sidebar', array('items' => $menu))->render(),
				'sidebar'     => View::factory('kohana_docs/sidebar', array('items' => $sidebar))->render(),
				'content'     => $page->render().View::factory('kohana_docs/navigation', $navigation)->render()
			));
		}
		catch (Kohana_Exception $exception)
		{
			Event::run('system.404');
		}
	}

	/**
	 * Browse API pages.
	 *
	 * @param  string  sort type
	 */
	public function browse_api($sort)
	{
		// Breadcrumbs
		$breadcrumbs = array('docs/api/browse' => 'API');

		// Create menu
		$menu['Sort'] = array
		(
			url::site('docs/api/browse/name')   => 'By Name',
			url::site('docs/api/browse/folder') => 'By Folder',
			url::site('docs/api/browse/type')   => 'By Type'
		);

		$classes = Kohana_Kodoc::list_classes();

		$this->template->set(array
		(
			'title'       => Kohana::lang('kohana_docs.api.title'),
			'page_id'   => 'api',
			'breadcrumbs' => $breadcrumbs,
			'menu'        => View::factory('kohana_docs/sidebar', array('items' => $menu))->render(),
			'sidebar'     => '',
			'content'     => View::factory('kohana_docs/api/browse_' . $sort, array('classes' => $classes))->render()
		));
	}

	/**
	 * View class API page.
	 *
	 * @param  string  class to view
	 */
	public function api($class)
	{
		if (empty($class)) url::redirect('docs/api/browse');

		$classes = Kohana_Kodoc::list_classes();

		// Get class file path, removing suffix if class is extendable
		$temp_class = (substr($class, -5) == '_Core') ? substr($class, 0, -5) : $class;

		if ( ! isset($classes[$temp_class]))
		{
			exit('Class does not exist');
		}

		$path = $classes[$temp_class];

		// Load Kodoc
		$kodoc = new Kohana_Kodoc($path);

		$docs = $kodoc->get($class);

		if ($docs === FALSE)
		{
			exit('Class does not exist');
		}

		// Breadcrumbs
		$breadcrumbs = array(
			'docs/api/browse' => 'API',
			'docs/api/class/'.$docs['name'] => $docs['name']
		);

		// Create menu
		$menu = array();
		foreach ($classes as $class2 => $file)
		{
			$url = url::site('docs/api/class/'.$class2);
			$menu['Classes'][$url] = $class2;
		}

		// Create sidebar
		$sidebar = array();

		// Add methods to sidebar
		foreach ($docs['methods'] as $method)
		{
			$type = $method['static'] ? 'Static ' : '';
			$type .= ucfirst($method['visibility']).' Methods';
			$url = '#'.$method['name'];
			$sidebar[$type][$url] = $method['name'];
		}

		// Add links to sidebar
		if (isset($docs['tags']['link']))
		{
			foreach ($docs['tags']['link'] as $link)
			{
				list ($url, $text) = explode(' ', $link, 2);
				$sidebar['Links'][$url] = $text;
			}
		}

		// Load markdown
		require Kohana::find_file('vendor', 'Markdown');

		$this->template->set(array
		(
			'title'       => Kohana::lang('kohana_docs.api.title'),
			'page_id'   => 'api',
			'breadcrumbs' => $breadcrumbs,
			'menu'        => View::factory('kohana_docs/sidebar', array('items' => $menu))->render(),
			'sidebar'     => View::factory('kohana_docs/sidebar', array('items' => $sidebar))->render(),
			'content'     => View::factory('kohana_docs/api/class', $docs)->render()
		));
	}

	/**
	 * Output a javascript file.
	 *
	 * @param  string  javascript filename
	 */
	public function js($filename)
	{
		header('Content-Type: text/javascript');

		$this->media('js', $filename);
	}

	/**
	 * Output a CSS file.
	 *
	 * @param  string  css filename
	 */
	public function css($filename)
	{
		header('Content-Type: text/css');

		$this->media('css', $filename);
	}

	/**
	 * Output an image.
	 *
	 * @param  string  image filename
	 */
	public function img($filename)
	{
		// Get file path info
		$pathinfo = pathinfo($filename);
		$filename = $pathinfo['filename'];
		$ext = $pathinfo['extension'];

		// Make sure file type is allowed
		$allowed_types = array('jpg', 'jpeg', 'png', 'gif');
		if ( ! in_array($ext, $allowed_types))
		{
			exit('/* type not allowed */');
		}

		View::factory('kohana_docs/media/img/'.$filename, NULL, $ext)->render(TRUE);
	}

	/**
	 * Output media file to the browser.
	 *
	 * @param  string  media type
	 * @param  string  filename
	 */
	private function media($type, $filename)
	{
		$this->auto_render = false;

		try
		{
			$info = pathinfo($filename);
			View::factory('kohana_docs/media/'.$type.'/'.$info['filename'], NULL, $info['extension'])->render(TRUE);
		}
		catch (Kohana_Exception $exception)
		{
			print '/* script not found */';
		}
	}

} // End Kohana_Documentation Controller
