<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
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
 * Scaffolding Class
 *
 * Provides the Scaffolding framework
 *
 * @package		Kohana
 * @subpackage	Scaffolding
 * @author		Rick Ellis
 * @link		http://www.codeigniter.com/user_guide/general/scaffolding.html
 */
class Scaffolding {

	var $CORE;
	var $current_table;
	var $base_url = '';
	var $lang     = array();

	function Scaffolding($db_table)
	{
		$this->CORE =& get_instance();

		$this->CORE->load->database('', FALSE, TRUE);
		$this->CORE->load->library('pagination');

		// Turn off caching
		$this->CORE->db->cache_off();

		/**
		 * Set the current table name
		 * This is done when initializing scaffolding:
		 * $this->load->scaffolding('table_name')
		 *
		 */
		$this->current_table = $db_table;

		/**
		 * Set the path to the "view" files
		 * We'll manually override the "view" path so that
		 * the load->view function knows where to look.
		 */

		$this->CORE->load->_ci_view_path = BASEPATH.'scaffolding/views/';

		// Set the base URL
		$this->base_url = $this->CORE->config->site_url().'/'.$this->CORE->uri->segment(1).$this->CORE->uri->slash_segment(2, 'both');
		$this->base_uri = $this->CORE->uri->segment(1).$this->CORE->uri->slash_segment(2, 'leading');

		// Set a few globals
		$data = array(
			'image_url' => $this->CORE->config->system_url().'scaffolding/images/',
			'base_uri'  => $this->base_uri,
			'base_url'  => $this->base_url,
			'title'     => $this->current_table);

		$this->CORE->load->vars($data);

		// Load the language file and create variables
		$this->lang = $this->CORE->load->language('scaffolding', '', TRUE);
		$this->CORE->load->vars($this->lang);

		//  Load the helper files we plan to use
		$this->CORE->load->helper(array('url', 'form'));

		log_message('debug', 'Scaffolding Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * "Add" Page
	 *
	 * Shows a form representing the currently selected DB
	 * so that data can be inserted
	 *
	 * @access	public
	 * @return	string	the HTML "add" page
	 */
	function add()
	{
		$data = array(
			'title'  =>  ( ! isset($this->lang['scaff_add'])) ? 'Add Data' : $this->lang['scaff_add'],
			'fields' => $this->CORE->db->field_data($this->current_table),
			'action' => $this->base_uri.'/insert');

		$this->CORE->load->view('add', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Insert the data
	 *
	 * @access	public
	 * @return	void	redirects to the view page
	 */
	function insert()
	{
		if ($this->CORE->db->insert($this->current_table, $_POST) === FALSE)
		{
			$this->add();
		}
		else
		{
			redirect($this->base_uri.'/view/');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * "View" Page
	 *
	 * Shows a table containing the data in the currently
	 * selected DB
	 *
	 * @access	public
	 * @return	string	the HTML "view" page
	 */
	function view()
	{
		// Fetch the total number of DB rows
		$total_rows = $this->CORE->db->count_all($this->current_table);

		if ($total_rows < 1)
		{
			return $this->CORE->load->view('no_data');
		}

		// Set the query limit/offset
		$per_page = 20;
		$offset = $this->CORE->uri->segment(4, 0);

		// Run the query
		$query = $this->CORE->db->get($this->current_table, $per_page, $offset);

		// Now let's get the field names
		$fields = $this->CORE->db->list_fields($this->current_table);

		// We assume that the column in the first position is the primary field.
		$primary = current($fields);

		// Pagination!
		$this->CORE->pagination->initialize(array(
			'base_url'       => $this->base_url.'/view',
			'total_rows'     => $total_rows,
			'per_page'       => $per_page,
			'uri_segment'    => 4,
			'full_tag_open'  => '<p>',
			'full_tag_close' => '</p>'));

		$data = array(
			'title'    =>  ( ! isset($this->lang['scaff_view'])) ? 'View Data' : $this->lang['scaff_view'],
			'query'    => $query,
			'fields'   => $fields,
			'primary'  => $primary,
			'paginate' => $this->CORE->pagination->create_links());

		$this->CORE->load->view('view', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * "Edit" Page
	 *
	 * Shows a form representing the currently selected DB
	 * so that data can be edited
	 *
	 * @access	public
	 * @return	string	the HTML "edit" page
	 */
	function edit()
	{
		if (FALSE === ($id = $this->CORE->uri->segment(4)))
		{
			return $this->view();
		}

		// Fetch the primary field name
		$primary = $this->CORE->db->primary($this->current_table);

		// Run the query
		$query = $this->CORE->db->getwhere($this->current_table, array($primary => $id));

		$data = array(
			'title'  =>  ( ! isset($this->lang['scaff_edit'])) ? 'Edit Data' : $this->lang['scaff_edit'],
			'fields' => $query->field_data(),
			'query'  => $query->row(),
			'action' => $this->base_uri.'/update/'.$this->CORE->uri->segment(4));

		$this->CORE->load->view('edit', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Update
	 *
	 * @access	public
	 * @return	void	redirects to the view page
	 */
	function update()
	{
		// Fetch the primary key
		$primary = $this->CORE->db->primary($this->current_table);

		// Now do the query
		$this->CORE->db->update($this->current_table, $_POST, array($primary => $this->CORE->uri->segment(4)));

		redirect($this->base_uri.'/view/');
	}

	// --------------------------------------------------------------------

	/**
	 * Delete Confirmation
	 *
	 * @access	public
	 * @return	string	the HTML "delete confirm" page
	 */
	function delete()
	{
		if ( ! isset($this->lang['scaff_del_confirm']))
		{
			$message = 'Are you sure you want to delete the following row: '.$this->CORE->uri->segment(4);
		}
		else
		{
			$message = $this->lang['scaff_del_confirm'].' '.$this->CORE->uri->segment(4);
		}

		$data = array(
			'title'   => ( ! isset($this->lang['scaff_delete'])) ? 'Delete Data' : $this->lang['scaff_delete'],
			'message' => $message,
			'no'      => anchor(array($this->base_uri, 'view'), ( ! isset($this->lang['scaff_no'])) ? 'No' : $this->lang['scaff_no']),
			'yes'     => anchor(array($this->base_uri, 'do_delete', $this->CORE->uri->segment(4)), ( ! isset($this->lang['scaff_yes'])) ? 'Yes' : $this->lang['scaff_yes']));

		$this->CORE->load->view('delete', $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Delete
	 *
	 * @access	public
	 * @return	void	redirects to the view page
	 */
	function do_delete()
	{
		// Fetch the primary key
		$primary = $this->CORE->db->primary($this->current_table);

		// Now do the query
		$this->CORE->db->where($primary, $this->CORE->uri->segment(4));
		$this->CORE->db->delete($this->current_table);

		header('Refresh:0;url='.site_url(array($this->base_uri, 'view')));
		exit;
	}

}
?>