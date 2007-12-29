<?php defined('SYSPATH') or die('No direct script access.');

class Admin_Controller extends Controller {

	protected $auto_render = TRUE;

	protected $user;

	public function __construct()
	{
		parent::__construct();

		if ($user = $this->session->get('user_id'))
		{
			// Fetch the user object
			$this->user = new User_Model((int) $user);

			if ($this->user->id AND $this->user->has_role('developer'))
			{
				// Load profiler
				$profiler = new Profiler;
			}
		}
	}

	public function index()
	{
		// Send them to the right page
		url::redirect('admin/login');
	}

	public function login()
	{
		$this->template->title = 'Developer Login';

		$content = new Form_Model();
		$content
			->title($this->template->title)
			->action('admin/login')
			->inputs(array
			(
				'user' => array
				(
					'rules' => array('username', 'trim|required[2,32]'),
				),
				'pass' => array
				(
					'type'  => 'password',
					'rules' => array('password', 'trim|required[2,64]'),
				),
				'go' => array
				(
					'type' => 'submit',
					'value' => 'Login'
				)
			));

		// Load content
		$this->template->set('content', $content->build());

		// Set username and password
		$username = $this->input->post('user');
		$password = $this->input->post('pass');

		if ($username AND $password)
		{
			// Load auth and the user
			$auth = new Auth;
			$user = new User_Model($username);

			// Attempt to log the user in
			if ($user->id AND $auth->login($user, $password))
			{
				// Hooray!
				url::redirect('admin/dashboard');
			}
		}
	}

	public function dashboard()
	{
		$this->template->title = 'Dashboard';
		$this->template->content = html::anchor('admin/add_video_tutorial', 'Add Video Tutorial').$this->session->get_once('message');
	}

	public function add_video_tutorial()
	{
		// User must be logged in
		is_object($this->user) and $this->user->has_role('developer') or url::redirect('admin/login');

		$form = new Forge(NULL, $this->template->title = 'Create Tutorial');
		$form->input('title')->label(TRUE)->rules('required|length[4,64]');
		$form->input('author')->label(TRUE)->rules('required|length[4,64]');
		$form->input('copyright')->label(TRUE)->rules('required|length[4]|valid_digit');
		$form->input('video')->label('File')->rules('required|length[2,127]');
		$form->input('width')->label(TRUE)->rules('required|length[2,3]|valid_digit');
		$form->input('height')->label(TRUE)->rules('required|length[2,3]|valid_digit');
		$form->submit('Save');

		if ($form->validate())
		{
			// Create new object
			$tutorial = new Video_Tutorial_Model;

			foreach($form->as_array() as $key => $val)
			{
				// Set object data
				$tutorial->$key = $val;
			}

			if ($tutorial->save())
			{
				// Set the message
				$this->session->set_flash('message', '<p><strong>Success!</strong> New tutorial has been created.</p>');
			}

			// Go back to dashboard
			url::redirect('admin/dashboard');
		}

		// Set content
		$this->template->content = $form->html();
	}

} // End Admin