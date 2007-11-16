<h2>Built-in Model Validation <span>By Jeremy Bush, &copy; 2007</span></h2>
<p>Please note that this tutorial assumes that the user has a bit of experience setting up Kohana 2 websites.</p>
<p>This tutorial level is Advanced.</p>
<h3>Introduction</h3>
<p>In this tutorial, we will set up a User Model, complete with "fake" ORM and built in validation. I have started moving all my validation needs into my models, so that I could clear up the clutter in my controllers and make everything more logical.</p>
<p>When you hear the 'M' in MVC, the M stands for Model. It is meant to model actual "things" (usually stored in a database, although they could also model static objects, like <?=html::anchor('tutorials/custom_model_behavior', 'circles and physics equations')?> ;)). This should include performing sanity checks on it's input before it tries to modify itself. This is the reason you should put validation checks in your model. From a MVC architecture standpoint, it "just makes sense."</p>
<p>To start we will create a model with our basica variables. The variables will match our database columns.</p>
<h4>application/models/user.php</h4>
<?php

echo geshi_highlight('class User_Model extends Model
{
	protected $id;
	protected $username;
	protected $password;
	protected $email;
	protected $address;
	protected $city;
	protected $state;
	protected $zip;
	protected $last_login;

}', 'php', NULL, TRUE)

?>
<p>Since we want validation inside the model, lets declare some variables to test and hold that information:</p>
<?php

echo geshi_highlight('class User_Model extends Model
{
	protected $id;
	protected $username;
	protected $password;
	protected $email;
	protected $address;
	protected $city;
	protected $state;
	protected $zip;
	protected $last_login;

	private $rules = array(\'id\' =>       array(\'name\' => \'User ID\',
	                                           \'rules\' => \'\',
	                                           \'valid\' => FALSE),
	                       \'username\' => array(\'name\' => \'Username\',
	                                           \'rules\' => \'required[3,25]|alpha_numeric\',
	                                           \'valid\' => FALSE),
	                       \'password\' => array(\'name\' => \'Password\',
	                                           \'rules\' => \'required[3,25]\',
	                                           \'valid\' => FALSE),
	                       \'email\' =>    array(\'name\' => \'Email\',
	                                           \'rules\' => \'valid_email|required\',
	                                           \'valid\' => FALSE),
	                       \'zip\' =>      array(\'name\' => \'ZIP Code\',
	                                           \'rules\' => \'numeric|required\',
	                                           \'valid\' => FALSE));

	public $validation;
	private $validated = FALSE;

	// Magic functions
	public function __construct()
	{
		$this->validation = new Validation();
	}', 'php', NULL, TRUE)

?>
<p>This will define our validation rules and fields.</p>
<p>We want our model to have some psuedo ORM capbilities. This will include easily setting and fetching private fields, inserting/saving/fetching record sets, as well as deleting database rows. Next we will set up some magic php functions to get/set data in our model.</p>
<?php

echo geshi_highlight('public function __set($key, $val)
	{
		// Make sure this key and value is valid
		if (isset($this->$key))
		{
			$this->validation->set_rules(array($key => $value), $this->rules[$key][\'rules\'], $this->rules[$key][\'name\']);
			if (!$this->validation->run())
				return FALSE;

			// You win at life!
			$this->$key = $val;
			$this->rules[$key][\'valid\'] = TRUE;

			// See if the whole thing is validated
			foreach ($this->rules as $key => $rule)
			{
				// If anything isnt validated, just return success
				if ($rule[\'valid\'] == FALSE)
					return TRUE;
			}
			// Otherwise set validated and return
			$this->validated = TRUE;
			return TRUE;
		}

		return FALSE;
	}
	
	public function __get($key)
	{
		if (isset($this->$key))
			return $this->$key;

		return FALSE;
	}', 'php', NULL, TRUE)
?>
<p>As you can see in our __set() function, we are using our validation rules we defined above to make sure any data that is being set is actually sane data. We are also checking to see if all the keys have been validated, and if so, set our global validated check to TRUE.</p>
<p>It might also be nice if we could set object properties with a big array instead of one at a time with __set(). Wil will make a set_fields() method for this:</p>
<?php

echo geshi_highlight('public function set_fields($input)
	{
		$data = array();
		$rules = array();
		$fields = array();
		$new_input = array();

		foreach ($this->rules as $key => $value)
		{
			//silently ignore invalid fields
			$data[$key] = @$input[$key];
			$rules[$key] = $this->rules[$key][\'rules\'];
			$fields[$key] = $this->rules[$key][\'name\'];
			
			if (isset($data[$key]) and isset($input[$key]))
				$new_input[$key] = $data[$key];
		}

		$this->validation->set_rules($data, $rules, $fields);

		if ($this->validation->run())
		{
			// Only set valid the keys that were inputed
			foreach ($new_input as $key => $value)
			{
				$this->$key = $value;
				$this->rules[$key][\'valid\'] = TRUE;
			}

			// Check to see if everything is validated
			foreach ($this->rules as $key => $rule)
			{
				// If anything isnt validated, just return success
				if ($rule[\'valid\'] == FALSE)
					return TRUE;
			}

			// Otherwise set validated and return
			$this->validated = TRUE;
			return TRUE;
		}

		return FALSE;
	}', 'php', NULL, TRUE)
?>
<p>Here you will see that it is basically a big, giant version of __set(), with some additional checks.</p>
<h3>Controller Code example</h3>
<p>To use this model, we can write some code like this:</p>
<?php

echo geshi_highlight('
$this->user = new User_Model();

$this->user->set_fields($this->input->post());

if ($rows = $this->user->save())
{
	// Display some success page
}
else
{
	// Display the form again with some errors
	$view = $this->load->view(\'form\', array(\'validation\' => $this->user->validation));
}

', 'php', NULL, TRUE)
?>
<h3>Conclusion</h3>
<p>As you can see in this brief tutorial, putting your validation into your model can clean up your controller code in great ways.</p>
<p>I have provided a whole <?=html::anchor('tutorials/model_validation_example', 'example class')?> for you to play around with, and you can always email me at <?=html::mailto('jeremy.bush@kohanaphp.com')?>.</p>
<p>I always appreciate any and all feedback!</p>