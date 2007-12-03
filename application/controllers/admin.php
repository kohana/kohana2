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
		// User must be logged in
		is_object($this->user) or url::redirect('admin/login');

		if ($this->user->has_role('developer'))
		{
			// Set title
			$this->template->title = 'Create Tutorial';

			// Form
			$form = new Form_Model();
			$form
				->title($this->template->title)
				->action('')
				->inputs(array
				(
					'title' => array
					(
						'label' => 'Title',
						'rules' => 'trim|required[4,64]',
					),
					'author' => array
					(
						'label' => 'Author',
						'rules' => 'trim|required[4,64]',
					),
					'copyright' => array
					(
						'label' => 'Copyright',
						'rules' => 'trim|required[4]|digit',
					),
					'video' => array
					(
						'label' => 'File',
						'rules' => 'trim|required[2,127]',
					),
					'width' => array
					(
						'label' => 'Width',
						'rules' => 'trim|required[2,3]|digit',
					),
					'height' => array
					(
						'label' => 'Height',
						'rules' => 'trim|required[2,3]|digit'
					),
					'save' => array
					(
						'type'  => 'submit',
						'value' => 'Save Tutorial'
					)
				));

			if ($form->validate())
			{
				// Create new object
				$tutorial = new Video_Tutorial_Model();

				foreach($form->data() as $key => $val)
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
			$this->template->content = $this->session->get('message').$form->build();
		}
		else
		{
			// ha
			url::redirect('admin/login');
		}
	}

} // End Admin