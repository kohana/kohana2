<h2><span>By Woody Gilk, &copy; 2007</span>Quick Form Generation</h2>

<p class="intro">Easily set up and validate forms with a <strong>custom model</strong> and <strong>view</strong>.</p>

<p>This tutorial will focus on showing you how to setup an easy and reusable form generator using models and views. The example here is for a user login form. This tutorial does not deal with database interaction, but only building, validating, and fetching the form data.</p>

<p class="note">This tutorial does not use the <abbr title="PHP Extension and Application Repository">PEAR</abbr> package <a href="http://pear.php.net/package/HTML_QuickForm2">HTML_QuickForm2</a>, but uses built-in Kohana functionality.</p>

<h4>The Model</h4>

<p>First, we need to create a model. Our model will allow us to set the form title and action, the Validation rules, and inputs. It will also handle validation automatically.</p>

<?php

echo geshi_highlight(
'<?php defined(\'SYSPATH\') or die(\'No direct script access.\');

class Form_Model extends Model {

	// Action attribute
	protected $action = \'\';

	// Title attribute
	protected $title  = \'\';

	// Input data
	protected $inputs = array();

	// Validation library
	protected $validation;

	// Validation status
	protected $status;

	public function __construct()
	{
		// Uncomment the following line if you want the database loaded:
		// parent::__construct();

		// Load validation
		$this->validation = new Validation();
	}

	/*
	 * Set the form action.
	 */
	public function action($uri)
	{
		$this->action = $uri;

		return $this;
	}

	/*
	 * Set the form title.
	 */
	public function title($title)
	{
		$this->title = $title;

		return $this;
	}

	/*
	 * Set validation rules.
	 */
	public function rules($rules)
	{
		$this->validation->set_rules($rules);

		return $this;
	}

	/*
	 * Set input data.
	 */
	public function inputs($inputs)
	{
		$this->inputs = array_merge($this->inputs, $inputs);

		return $this;
	}

	/*
	 * Run validation.
	 */
	public function validate()
	{
		if ($this->status === NULL AND ! empty($_POST))
		{
			// Run validation now
			$this->status = $this->validation->run();
		}

		return $this->status;
	}

	/*
	 * Returns the validated data.
	 */
	public function data($key = NULL)
	{
		if ($key === NULL)
		{
			return $this->validation->data_array;
		}
		else
		{
			return $this->validation->$key;
		}
	}

	/*
	 * Build the form and return it.
	 */
	public function build($template = \'generate_form\')
	{
		if ($this->status === NULL AND ! empty($_POST))
		{
			// Run validation now
			$this->status = $this->validation->run();
		}

		// Required data for the template
		$form = array
		(
			\'action\' => $this->action,
			\'title\'  => $this->title,
			\'inputs\' => array()
		);

		foreach($this->inputs as $name => $data)
		{
			// Error name
			$error = $name.\'_error\';

			// Append the value and error the the input, if it does not already exist
			$data += array
			(
				\'value\' => $this->validation->$name,
				\'error\' => $this->validation->$error
			);

			$form[\'inputs\'][$name] = $data;
		}

		return Kohana::instance()->load->view($template, $form);
	}

} // End Form_Model
', 'php', NULL, TRUE);

?>

<p>One thing to note about our model is that most of the methods use <tt>return $this;</tt>. This allows chaining:</p>

<?php

echo geshi_highlight(
'$form = $this->load->model(\'form\', TRUE)
	->title(\'User Login\')
	->action(\'user/login\');
', 'php', NULL, TRUE);

?>

<p>All of the form methods except <tt>validate</tt> and <tt>build</tt> can be chained together.</p>

<h4>The View</h4>

<p>In order to display our form, we need a view. By default, the model will load a view called <tt>generate_form</tt>, but you are free to change the default name, or use a custom template with the <tt>generate</tt> method.</p>

<?php

echo geshi_highlight(
'<?php echo form::open($action) ?>

<h4><?php echo $title ?></h4>

<ul>
<?php

foreach($inputs as $name => $data):

	// Generate label
	$label = empty($data[\'label\']) ? \'\' : form::label($name, arr::remove(\'label\', $data))."\n";

	// Generate error
	$error = arr::remove(\'error\', $data);

	// Set input name and id
	$data[\'name\'] = $data[\'id\'] = $name;

	if ( ! empty($data[\'options\']))
	{
		// Get options and selected
		$options  = arr::remove(\'options\', $data);
		$selected = arr::remove(\'selected\', $data);
		// Generate dropdown
		$input = form::dropdown($data, $options, $selected);
	}
	else
	{
		switch(@$data[\'type\'])
		{
			case \'textarea\':
				// Remove the type, textarea doesn\'t need it
				arr::remove(\'type\', $data);
				// Generate a textarea
				$input = form::textarea($data);
			break;
			case \'submit\':
				// Generate a submit button
				$input = form::button($data);
			break;
			default:
				// Generate a generic input
				$input = form::input($data);
			break;
		}
	}
?>
<li>
<?php echo $label.$input.$error; ?>
</li>
<?php endforeach; ?>
</ul>
<?php echo form::close() ?>
', 'php', NULL, TRUE);

?>

<h4>Putting It Together</h4>

<p>Now, let's create a simple user login method in a controller. For the sake of the tutorial, we will assume that your controller is called <tt>User_Controller</tt>.</p>

<?php

echo geshi_highlight(
'function login()
{
	// Load the Form model and set the properties
	$form = $this->load->model(\'form\', TRUE)
		// Set action
		->action(\'user/login\')
		// Set title
		->title(\'User Login\')
		// Set inputs
		->inputs(array(
			\'username\' => array
			(
				\'label\' => \'Username\',
			),
			\'password\' => array
			(
				\'label\' => \'Password\',
				\'type\'  => \'password\',
			),
			\'submit\' => array
			(
				\'type\'  => \'submit\',
				\'value\' => \'Login\'
			)
		))
		// Set validation rules
		->rules(array(
			\'username\' => \'trim|required[2,32]\',
			\'password\' => \'required[4,127]\'
		));

	// Attempt to validate the form
	if ($form->validate() === TRUE)
	{
		// NOTE: The following example uses a library called Auth, which is outside
		// the scope of this tutorial, and here for example purposes only.
		// You may also fetch all the form data using: $form->data();

		// Load authentication
		$this->load->library(\'auth\');

		// Check login
		if ($this->auth->login($form->data(\'username\'), $form->data(\'password\')))
		{
			// Redirect the user back to the home page now that they are logged in.
			// NOTE: url::redirect will immediately stop the execution of the page
			// and perform the redirect. You do not need to use return; or exit;
			url::redirect(\'\');
		}
	}

	// Load the site template with the form and display it
	$this->load->view(\'user_login\', array(\'form\' => $form->build()))->render(TRUE);
}
', 'php', NULL, TRUE);

?>

<p>That concludes the entire tutorial. From here, you can expand the Form model to handle database connections, or even extend the model with another model, to handle specific forms. You can modify the view to suit your preferred way of templating forms, or use a custom view for specific forms.</p>

<p>For more information about handling database interaction in your forms, I recommend looking at the <?php echo html::anchor('tutorials/model_validation', 'Model Validation') ?> tutorial.</p>

<h4>Questions or Comments?</h4>

<p>Feel free to send me questions and comments, my email address is <?php echo html::mailto('woody.gilk@kohanaphp.com') ?>.</p>
