<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Kohana - The Swift PHP Framework
 *
 *  License:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */

/**
 * Handles loading of site resources (CSS, JS, images) using Views.
 * By default it is assumed that your media files will be stored in
 * `application/views/media`.
 *
 * Usage:
 *  `http://example.com/index.php/media/css/styles.css`
 */
class Media_Controller extends Controller {

	protected $use_cache = FALSE;
	protected $cache_lifetime;

	protected $pack_css = FALSE;
	protected $pack_js = FALSE;

	public function __construct()
	{
		parent::__construct();

		$cache = Config::item('media.cache');
		$this->use_cache = ($cache > 0);

		if (is_int($cache))
		{
			$this->cache_lifetime = $cache;
		}
		else
		{
			$this->cache_lifetime = config::item('cache.lifetime') OR $this->cache_lifetime = 1800;
		}

		if ($this->use_cache AND ! isset($this->cache)) 
		{
			$this->load->library('cache');
		}

		$this->pack_css = (bool) Config::item('media.pack_css');
		$this->pack_js = Config::item('media.pack_js');

		if ($this->pack_js === TRUE)
		{
			$this->pack_js = 'Normal';
		}
	}
	
	public function css($querystr) {
		// find all the individual files 
		$files = explode("+", $querystr);

		$mimetype = config::item('mimes.css');
		$mimetype = (isset($mimetype[0])) ? $mimetype[0] : 'text/stylesheet';

		$this->use_cache AND $data = $this->cache->get('media.css.'.$querystr);

		if ( ! isset($data) OR empty($data))
		{
			$data = '';
			foreach ($files as $orig_filename) {
				$filename = $orig_filename;
				if (substr($filename, -4) == ".css") 
				{
					$filename = substr($filename, 0, -4);
				}
				
				try
				{
					$view = new View('media/css/'.$filename, null, 'css');
				}
				catch (Kohana_Exception $exception)
				{
					// try to load the file as a php view (eg, file.css.php) 
					try
					{
						$view = new View('media/css/'.$orig_filename);
						
					}
					catch (Kohana_Exception $exception)
					{
						// not found
						unset($view);
					}
				}

				if (isset($view)) {
					$filedata = $view->render();
					
					($this->pack_css) and $filedata = $this->_css_compress($filedata);
					
					$data .= $filedata;
				}
				else
				{	
					$data .= "\n/**** stylesheet ".$filename." not found ****/\n\n\n";
				}
			}
			
			($this->use_cache) and $this->cache->set('media.css.'.$querystr, $data, array('media'), $this->cache_lifetime);
		}

		$mimetype AND header('Content-type: '.$mimetype);
		echo $data;
	}

	public function js($orig_filename) {
		$filename = $orig_filename;
		if (substr($filename, -3) == '.js')
		{
			$filename = substr($filename, 0, -3);
		}

		$mimetype = Config::item('mimes.js');
		$mimetype = (isset($mimetype[0])) ? $mimetype[0] : 'text/javascript';


		$this->use_cache AND $data = $this->cache->get('media.js.'.$filename);

		if ( ! isset($data) OR empty($data)) 
		{
			try
			{
				$view = new View('media/js/'.$filename, NULL, 'js');
			}
			catch (Kohana_Exception $exception)
			{
				// Try to load the file as a php view (eg, file.js.php) 
				try
				{
					$view = new View('media/js/'.$orig_filename);
				}
				catch (Kohana_Exception $exception)
				{
					// Not found
					unset($view);
				}
			}

			if (isset($view)) 
			{
				$data = $view->render();

				if ($this->pack_js) 
				{
					$packer = new JavaScriptPacker($data, $this->pack_js);
					$data = $packer->pack();
				}

				($this->use_cache) and $this->cache->set('media.js.'.$filename, $data, array('media'), $this->cache_lifetime);
			} 
			else 
			{
				$data = '/* script not found */';
			}
		}

		$mimetype AND header('Content-type: '.$mimetype);
		echo $data;
	}

	public function _default()
	{
		$type = $this->uri->segment(2);
		$filename = $this->uri->segment(3);
		// TODO: finish this for generic types
		/* issues: getting View to work with any types of files */
		
		try
		{
			$view = new View('media/'.$type.'/'.$filename);
		}
		catch (Kohana_Exception $exception)
		{
			Event::run('system.404');
		}
	}

	// Based on http://www.ibloomstudios.com/articles/php_css_compressor/
	public function _css_compress($data)
	{
		// Remove comments
		$data = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $data);

		// Remove tabs, spaces, newlines, etc.
		$data = preg_replace('/\s+/s', ' ', $data);
		$data = str_replace
		(
			array(' {', '{ ', ' }', '} ', ' +', '+ ', ' >', '> ', ' :', ': ', ' ;', '; ', ' ,', ', ', ';}'),
			array('{',  '{',  '}',  '}',  '+',  '+',  '>',  '>',  ':',  ':',  ';',  ';',  ',',  ',',  '}' ),
			$data
		);

		// Remove empty CSS declarations
		$data = preg_replace('/[^{}]++\{\}/', '', $data);

		return $data;
	}

} // End Media_Controller
