<?php
/**
 * Auth module demo controller. This controller should NOT be used in production.
 * It is for demonstration purposes only!
 *
 * $Id$
 *
 * @package    Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Controller_Auth extends Controller_Template {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	// Use the default Kohana template
	public $template = 'kohana/template';

	// Currently logged in user
	protected $user;

	public function __construct()
	{
		parent::__construct();

		// Load sessions, to support logins
		$this->session = Session::instance();

		if (Auth::instance()->logged_in())
		{
			// Set the current user
			$this->user = $_SESSION['auth_user'];
		}
	}

	public function index()
	{
		// Display the install page
		$this->template->title   = 'Auth Module Installation';
		$this->template->content = View::factory('auth/install')
			->bind('sql', $sql)
			->bind('result', $result);

		// Load installation SQL
		$sql = View::factory('auth/install', NULL, 'sql')->render();

		// Load validation
		$post = Validation::factory($_POST)
			->pre_filter('trim')
			->add_rules('query', 'required');

		if ($post->validate())
		{
			try
			{
				// Run the query
				Database::instance()->query($post['query']);

				// Go to the creation page
				url::redirect('auth/installed');
			}
			catch (Kohana_Database_Exception $e)
			{
				// Set the result to the exception
				$result = $e;
			}
		}
	}

	public function installed()
	{
		if (request::referrer() !== 'auth')
		{
			// Do not allow non-referrered requests
			url::redirect('auth');
		}

		$this->template->title = 'Installation Sucessful!';
		$this->template->content = View::factory('auth/installed');
	}

	public function create()
	{
		$this->template->title = 'Create User';
		$this->template->content = View::factory('auth/create_user')
			->bind('post', $post)
			->bind('errors', $errors);

		// Will be converted into a Validation object
		$post = $_POST;

		// Create a new user
		$user = ORM::factory('user');

		// Give the user login privileges
		$user->add(ORM::factory('role', 'login'));

		// Validate and save the new user
		if ($user->validate($post, TRUE))
		{
			// Log in now
			Auth::instance()->login($user, $post['password']);

			// Redirect to the logged_in page
			url::redirect('auth/logged_in');
		}

		$errors = $post->errors('form_user');
	}

	public function edit($id = NULL)
	{

	}

	public function delete($id = NULL)
	{
		$user = ORM::factory('user', $id);

		// If the user does not exist, redirect
		$user->loaded or url::redirect('auth/logged_in');

		if (is_object($this->user) AND $user->id === $this->user->id)
		{
			// Log the user out, their account will no longer exist
			Auth::instance()->logout();
		}

		// Delete the user
		$user->delete();

		url::redirect('auth/logged_in');
	}

	public function logged_in()
	{
		if ( ! is_object($this->user))
		{
			// No user is currently logged in
			url::redirect('auth/login');
		}

		$this->template->title = 'User Properties';
		$this->template->content = View::factory('auth/user_info')
			->bind('user', $this->user);
	}

	public function login()
	{
		$this->template->title = 'Login';
		$this->template->content = View::factory('auth/login')
			->bind('post', $post)
			->bind('errors', $errors);

		$post = Validation::factory($_POST)
			->pre_filter('trim')
			->add_rules('username', 'required', 'length[4,127]')
			->add_rules('password', 'required');

		if ($post->validate())
		{
			$user = ORM::factory('user', $post['username']);

			if ( ! $user->loaded)
			{
				// The user could not be located
				$post->add_error('username', 'not_found');
			}
			elseif (Auth::instance()->login($user, $post['password']))
			{
				// Successful login
				url::redirect('auth/logged_in');
			}
			else
			{
				// Incorrect password
				$post->add_error('password', 'incorrect');
			}
		}

		$errors = $post->errors('form_login');
	}

	public function logout()
	{
		Auth::instance()->logout();

		url::redirect('auth/login');
	}

} // End Auth Controller