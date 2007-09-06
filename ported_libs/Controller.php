<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
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
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana Application Controller Class
 *
 * This class object is the super class the every library in
 * Kohana will be assigned to.
 *
 * @package		Kohana
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/general/controllers.html
 */
class Core_Controller extends Kohana {

	var $_scaffolding = FALSE;
	var $_scaff_table = FALSE;

	/**
	 * Constructor
	 *
	 * Calls the initialize() function
	 */
	public function __construct()
	{
		parent::Kohana();
		$this->_initialize();
		Log::add('debug', 'Controller Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize
	 *
	 * Assigns all the bases classes loaded by the front controller to
	 * variables in this class.  Also calls the autoload routine.
	 *
	 * @access	private
	 * @return	void
	 */
	private function _initialize()
	{
		// Assign all the class objects that were instantiated by the
		// front controller to local class variables so that Core can be
		// run as one big super object.
		$classes = array
		(
			'config'    => 'Config',
			'input'     => 'Input',
			'benchmark' => 'Benchmark',
			'uri'       => 'URI',
			'output'    => 'Output',
			'lang'      => 'Language'
		);

		foreach ($classes as $var => $class)
		{
			$this->$var = load_class($class);
		}

		// In PHP 5 the Loader class is run as a discreet
		// class.  In PHP 4 it extends the Controller
		if (KOHANA_IS_PHP5)
		{
			$this->load = load_class('Loader');
			$this->load->_autoloader();
		}
		else
		{
			$this->_autoloader();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Run Scaffolding
	 *
	 * @access	private
	 * @return	void
	 */
	private function _scaffolding()
	{
		if ($this->_scaffolding === FALSE OR $this->_scaff_table === FALSE)
		{
			show_404('Scaffolding unavailable');
		}

		$method = ( ! in_array($this->uri->segment(3), array('add', 'insert', 'edit', 'update', 'view', 'delete', 'do_delete'), TRUE)) ? 'view' : $this->uri->segment(3);

		require_once(BASEPATH.'scaffolding/Scaffolding'.EXT);
		$scaff = new Scaffolding($this->_scaff_table);
		$scaff->$method();
	}


}
// END _Controller class
?>