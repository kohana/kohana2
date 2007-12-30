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
			$this->user = new User_Model($user);

			if ($this->user->id AND $this->user->has_role('developer'))
			{
				// Load profiler
				$profiler = new Profiler;
			}
		}

		if ($this->uri->segment(2) !== 'login' AND $this->uri->segment(2) !== 'log_out')
		{
			// User must be logged in
			is_object($this->user) and $this->user->has_role('developer') or url::redirect('admin/login');

			Event::add_before('system.post_controller', array($this, '_display'), array($this, '_add_breadcrumb'));
		}
	}

	public function _add_breadcrumb()
	{
		$crumbs = View::factory('admin/breadcrumb')
			->set('crumbs', html::breadcrumb())
			->render();

		$this->template->content = $crumbs.$this->template->content;
	}

	public function index()
	{
		// Send them to the right page
		url::redirect('admin/dashboard');
	}

	public function login()
	{
		// Create the login form
		$form = new Forge(NULL, $this->template->title = 'Developer Login');
		$form->input('username')->label(TRUE)->rules('required|length[2,32]');
		$form->password('password')->label(TRUE)->rules('required|length[2,64]');
		$form->submit('Login');

		if ($form->validate() AND $data = $form->as_array())
		{
			// Load Auth and the user
			$auth = new Auth;
			$user = new User_Model($data['username']);

			// Make sure the user is valid and attempt a login
			if ($user->id AND $auth->login($user, $data['password']))
			{
				// Hooray!
				url::redirect('admin/dashboard');
			}
		}

		// Load content
		$this->template->content = $form->html();
	}

	public function log_out()
	{
		$auth = new Auth;
		$auth->logout(TRUE);

		url::redirect('admin/login');
	}

	public function dashboard()
	{
		$this->template->title = 'Dashboard';

		$content = new View('admin/menu');
		$content->actions = array
		(
			'manage_users',
			'manage_video_tutorials',
			'log_out',
		);

		$this->template->content = $this->session->get_once('message').$content->render();
	}

	public function manage_users($id = FALSE)
	{
		if ($id === FALSE)
		{
			$this->template->title = 'Manage Users';

			$users = array();
			foreach (ORM::factory('user')->find(ALL) as $user)
			{
				// Create a list of all users
				$users[$user->username] = $user->username;
			}

			$this->template->content = View::factory('admin/edit_list')
				->set('new', 'Add a new user')
				->set('items', $users)
				->set('edit_action', 'admin/manage_users')
				->set('delete_action', 'admin/delete_user');
		}
		else
		{
			// Reset the id for new users
			($id === 'new') and $id = FALSE;

			// Load the user
			$user = new User_Model($id);

			$roles = array();
			foreach (ORM::factory('role')->find(ALL) as $role)
			{
				// Create a checklist option array
				$roles[$role->name] = array($role->name, $user->has_role($role->id));
			}

			// Create user editing form
			$form = new Forge(NULL, $this->template->title = ($user->username ? 'Edit '.$user->username : 'New User'));
			$form->input('username')->label(TRUE)->rules('required|length[2,32]')->value($user->username);
			$form->input('email')->label(TRUE)->rules('required|length[4,127]|valid_email')->value($user->email);
			$form->password('password')->label(TRUE)->rules('length[4,64]');
			$form->password('passconf')->label('Confirm')->matches($form->password);
			$form->checklist('roles')->label(TRUE)->options($roles);
			$form->submit('Save');

			if ($id === FALSE)
			{
				// New users must have a password
				$form->password->rules('+required');
			}

			if ($form->validate() AND $data = $form->as_array())
			{
				// Extract the roles from the data
				$set_roles = arr::remove('roles', $data);

				if (empty($data['passconf']))
				{
					// Do not reset the password to nothing
					unset($data['password'], $data['passconf']);
				}

				foreach ($data as $key => $val)
				{
					// Set new values
					$user->$key = $val;
				}

				// Save the user and set the message
				$user->save() and $this->session->set_flash('message', '<p><strong>Success!</strong> User saved successfully.</p>');

				foreach (array_diff($user->roles, $set_roles) as $role)
				{
					// Remove roles that were unchecked
					$user->remove_role($role);
				}

				foreach (array_diff($set_roles, $user->roles) as $role)
				{
					// Add new roles
					$user->add_role($role);
				}

				// Redirect the the dashboard
				url::redirect('admin/dashboard');
			}

			$this->template->content = $form->html();
		}
	}

	public function delete_user($id = FALSE)
	{
		// Confirmation
		$confirm = $this->input->get('confirm');

		// Load the user
		$user = new User_Model($id);

		if ($confirm === 'no' OR $user->id == 0)
		{
			// Go back the to the management page
			url::redirect('admin/manage_users');
		}

		// Set the template title
		$this->template->title = 'Delete '.$user->username.'?';

		if ($user->id AND $confirm === 'yes')
		{
			// Delete the user
			$user->delete();

			// Go back to the user management
			url::redirect('admin/manage_users');
		}

		$this->template->content = View::factory('admin/confirm')->set('action', 'admin/delete_user/'.$id);
	}

	public function manage_video_tutorials($id = FALSE)
	{
		if ($id === FALSE)
		{
			$this->template->title = 'Manage Users';

			$list = View::factory('admin/edit_list')
				->set('new', 'Add a new video')
				->set('edit_action', 'admin/manage_video_tutorials')
				->set('delete_action', 'admin/delete_video_tutorial')
				->bind('items', $items);

			foreach (ORM::factory('video_tutorial')->find(ALL) as $tutorial)
			{
				// Create a list of all tutorials
				$items[$tutorial->video] = $tutorial->title;
			}

			$this->template->content = $list->render();
		}
		else
		{
			($id === 'new') and $id = FALSE;

			// Load tutorial
			$tutorial = new Video_Tutorial_Model($id);

			$form = new Forge(NULL, $this->template->title = ($tutorial->id ? 'Update Tutorial' : 'New Tutorial'));
			$form->input('title')->label(TRUE)->rules('required|length[4,64]')->value($tutorial->title);
			$form->input('author')->label(TRUE)->rules('required|length[4,64]')->value($tutorial->author);
			$form->input('copyright')->label(TRUE)->rules('required|length[4]|valid_digit')->value($tutorial->copyright);
			$form->input('video')->label('File')->rules('required|length[2,127]')->value($tutorial->video);
			$form->input('width')->label(TRUE)->rules('required|length[2,3]|valid_digit')->value($tutorial->width);
			$form->input('height')->label(TRUE)->rules('required|length[2,3]|valid_digit')->value($tutorial->height);
			$form->submit('Save');

			if ($form->validate())
			{
				foreach($form->as_array() as $key => $val)
				{
					// Set object data
					$tutorial->$key = $val;
				}

				if ($tutorial->save())
				{
					// Set the message
					$this->session->set_flash('message', '<p><strong>Success!</strong> Tutorial was saved successfully.</p>');
				}

				// Go back to dashboard
				url::redirect('admin/dashboard');
			}

			// Set content
			$this->template->content = $form->html();
		}
	}

} // End Admin