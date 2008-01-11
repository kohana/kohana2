<?php defined('SYSPATH') or die('No direct script access.');

class Auth_Controller extends Controller {

	protected $inputs = array
	(
		'email' => array
		(
			'label' => 'Email Address',
			'rules' => 'required[6,127]|valid_email'
		),
		'username' => array
		(
			'label' => 'Username',
			'rules' => 'required[4,32]'
		),
		'password' => array
		(
			'label' => 'Password',
			'type'  => 'password',
			'rules' => 'required[5,40]'
		),
		'submit' => array
		(
			'type' => 'submit',
			'value' => 'Create New User'
		)
	);

	public function __construct()
	{
		parent::__construct();

		// Load some libraries
		foreach(array('profiler', 'auth', 'session') as $lib)
		{
			$class = ucfirst($lib);
			$this->$lib = new $class();
		}
	}

	function index()
	{
		// Display the install page
		echo new View('auth/install');
	}

	function create()
	{
		$form = $this->load->model('form', TRUE)
			->title('Create User')
			->action('auth/create')
			->inputs($this->inputs);

		if ($form->validate())
		{
			// Create new user
			$user = new User_Model;

			if ( ! $user->username_exists($this->input->post('username')))
			{
				foreach($form->data() as $key => $val)
				{
					// Set user data
					$user->$key = $val;
				}

				if ($user->save() AND $user->add_role('login'))
				{
					// Redirect to the login page
					url::redirect('auth/login');
				}
			}
		}

		// Display the form
		echo $form->build();
	}

	function login()
	{
		// Get inputs
		$inputs = $this->inputs;

		// Change the submit button
		$inputs['submit']['value'] = 'Attempt Login';

		// Remove email, we don't need it
		unset($inputs['email']);

		if ( ! $this->session->get('user_id'))
		{
			// Create the login form
			$form = $this->load->model('form', TRUE)
				->title('Test Login')
				->action('auth/login')
				->inputs($inputs);

			if ($form->validate())
			{
				// Load the user
				$user = new User_Model($form->data('username'));

				// Attempt a login
				if ($this->auth->login($user, $form->data('password')))
				{
					echo "<h4>Login Success!</h4>";
					echo "<p>Your roles are:</p>";
					echo Kohana::debug($this->session->get('roles'));
				}
				else
				{
					echo "<h4>Login Failed!</h4>";
				}
			}
		}

		if ($this->session->get('user_id'))
		{
			$form = $this->load->model('form', TRUE)
				->title('Logout')
				->action('auth/logout')
				->inputs(array
				(
					'logout' => array
					(
						'type' => 'submit',
						'value' => 'Logout'
					)
				));
		}

		// Display the form
		echo $form->build();
	}

	function logout()
	{
		// Load auth and log out
		$auth = new Auth();
		$auth->logout(TRUE);

		// Redirect back to the login page
		url::redirect('auth/login');
	}

} // End Auth_Controller