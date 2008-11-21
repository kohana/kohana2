<?php
/**
 * Object Relational Mapping (ORM) "form" extension. Allows ORM objects to
 * create and return generic form views.
 *
 * $Id$
 *
 * @package    ORM
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class ORM_Form_Core extends ORM {

	// Form view
	protected $view = 'kohana/form';

	// Validation rules and callbacks
	protected $rules     = array();
	protected $callbacks = array();

	// Form inputs
	protected $inputs = array();

	/**
	 * Creates and returns a form view object based on the defined inputs,
	 * rules, and callbacks. ORM::validate is used as the validation method.
	 *
	 * Error i18n files follow the format: form_model_name, eg: User_Model would
	 * use form_user for errors.
	 *
	 * @param   array    values array to validate
	 * @param   boolean  TRUE to save the model, or a URI to redirect, on success
	 * @return  View
	 */
	public function form(array & $array, $save = FALSE)
	{
		$array = Validation::factory($array)
			->pre_filter('trim');

		foreach ($this->rules as $column => $rules)
		{
			foreach ($rules as $rule)
			{
				$array->add_rules($column, $rule);
			}
		}

		foreach ($this->callbacks as $column => $rules)
		{
			foreach ($rules as $rule)
			{
				$array->add_callbacks($column, $rule);
			}
		}

		// Load the form
		$form = View::factory($this->view)
			->set('action', Kohana_Request::$instance->request->current_uri)
			->set('cancel', Kohana_Request::$instance->request->current_uri)
			->set('attributes', array())
			->bind('inputs', $inputs)
			->bind('errors', $errors);

		if ( ! $this->validate($array, $save))
		{
			// Load errors
			$errors = $array->errors('form_'.$this->object_name);
		}

		$inputs = array();
		foreach ($this->inputs as $name => $data)
		{
			if (is_int($name))
			{
				$name = $data;
				$data = NULL;
			}
			else
			{
				if (isset($data['type']) AND $data['type'] === 'dropdown')
				{
					if (isset($data['options']) AND ! is_array($data['options']))
					{
						list ($model, $attr) = arr::callback_string($data['options']);

						// Generate a list of options
						$data['options'] = ORM::factory($model)->select_list($attr[0], $attr[1]);
					}

					if ( ! isset($data['selected']))
					{
						$data['selected'] = $array[$name];
					}
				}
				elseif (isset($data['type']) AND $data['type'] === 'upload')
				{
					// Form must be multi-part
					$attributes['enctype'] = 'multipart/form-data';
				}
				else
				{
					$data['value'] = $array[$name];
				}
			}

			if ( ! isset($data['name']))
			{
				// Set input name
				$data['name'] = $name;
			}

			if ( ! isset($data['title']))
			{
				// Set field title
				$data['title'] = ucfirst($name);
			}

			// Add the column to the inputs
			$inputs[arr::remove('title', $data)] = $data;
		}

		return $form;
	}

} // End ORM Form
