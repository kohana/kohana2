<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * The form helper class provides convenience methods for creating
 * forms and form elements.
 *
 * [!!] This helper does not validate or filter submission data!
 *
 * For all in one form creation and validation, you can peruse the
 * many offerrings in Kohana's addon module repository.
 *
 * @link http://dev.kohanaphp.com/projects
 *
 * ##### Complete Example
 *
 * This example assumes the form is being built within a view
 * file.
 *
 *     // Assuming this url: http://localhost/kohana/welcome/index
 *     <?=form::open('welcome/index', array('method' => 'post'));?>
 *     <table>
 *       <tr>
 *         <td>
 *           <?=form::label(array('for' => 'username'), 'Username:');?>
 *         </td>
 *         <td>
 *           <?=form::input(array('name' => 'username', 'id' => 'username'));?>
 *         </td>
 *       </tr>
 *       <tr>
 *         <td>
 *           <?=form::label(array('for' => 'passphrase'), 'Passphrase:');?>
 *         </td>
 *         <td>
 *           <?=form::password(array('name' => 'passphrase', 'id' => 'passphrase'));?>
 *         </td>
 *       </tr>
 *       <tr>
 *         <td>
 *           <?=form::button(array('type' => 'submit'), 'Login');?>
 *         </td>
 *       </tr>
 *     </table>
 *     </form>
 *
 * Note the manual use of `</form>`! There is no magic in closing a
 * form ;-)
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class form_Core {

	/**
	 * This method generates an opening HTML **form** element with any
	 * additional attributes provided.
	 *
	 * Leaving the first function argument `null` opens the form with
	 * the current URI as its action attribute value. To alter any of
	 * the default attributes produced by this method, supply a valid
	 * URL action path (or `null` for the current URL), and an array of
	 * attribute => value pairs.
	 *
	 * ###### Example
	 *     
	 *     // Default, assuming this url: http://localhost/kohana/welcome/index
	 *     echo form::open();
	 *     
	 *     // Output:
	 *     <form action="/kohana/index.php/welcome/index" method="post">
	 *     
	 *     // With attributes
	 *     echo form::open('controller/action', array('method' => 'post'));
	 *     
	 *     // Output:
	 *     <form action="/kohana/index.php/controller/action" method="post">
	 *
	 * @param   string  $action   Form action url path
	 * @param   array   $attr     Array of additional attribute => value pairs
	 * @param   array   $hidden   Hidden fields to be created immediately after the form tag
	 * @param   string  $protocol Non-default protocol, eg: https
	 * @return  string
	 */
	public static function open($action = NULL, $attr = array(), $hidden = NULL, $protocol = NULL)
	{
		// Make sure that the method is always set
		empty($attr['method']) and $attr['method'] = 'post';

		if ($attr['method'] !== 'post' AND $attr['method'] !== 'get')
		{
			// If the method is invalid, use post
			$attr['method'] = 'post';
		}

		if ($action === NULL)
		{
			// Use the current URL as the default action
			$action = url::site(Router::$complete_uri, $protocol);
		}
		elseif (strpos($action, '://') === FALSE)
		{
			// Make the action URI into a URL
			$action = url::site($action, $protocol);
		}

		// Set action
		$attr['action'] = $action;

		// Form opening tag
		$form = '<form'.form::attributes($attr).'>'."\n";

		// Add hidden fields immediate after opening tag
		empty($hidden) or $form .= form::hidden($hidden);

		return $form;
	}

	/**
	 * This method generates an opening HTML **form** element that can be used for
	 * uploading files.
	 *
	 * This method is identical in use to the [form::open] method, it
	 * simply adds `multipart/form-data` as a value to the *enctype*
	 * attribute.
	 *
	 * ###### Example
	 *     
	 *     // Default, assuming this url: http://localhost/kohana/welcome/index
	 *     echo form::open_multipart();
	 *     
	 *     // Output:
	 *     <form action="/kohana/index.php/" method="post" enctype="multipart/form-data">
	 *     
	 *     // With attributes
	 *     echo form::open_multipart('controller/action', array('method' => 'post'));
	 *     
	 *     // Output:
	 *     <form action="/kohana/index.php/controller/action" method="post" enctype="multipart/form-data">
	 *
	 * @param   string  $action Form action url path
	 * @param   array   $attr   Array of additional attribute => value pairs
	 * @param   array   $hidden Hidden fields to be created immediately after the form tag
	 * @return  string
	 */
	public static function open_multipart($action = NULL, $attr = array(), $hidden = array())
	{
		// Set multi-part form type
		$attr['enctype'] = 'multipart/form-data';

		return form::open($action, $attr, $hidden);
	}

	/**
	 * This method generates an HTML **input** element with a type attribute value of
	 * *hidden*.
	 * 
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::hidden('closet', 'gnome');
	 *     
	 *     // Output:
	 *     <input type="hidden" name="closet" value="gnome"  />
	 *
	 * @param   mixed   $data  Input name or an array of attribute => value pairs
	 * @param   string  $value Input value, when using a name
	 * @param   string  $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function hidden($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'hidden';

		return form::input($data, $value, $extra);
	}

	/**
	 * This method generates an HTML **input** element with a type attribute
	 * value of *text*.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::input('base', 'are belong to us');
	 *     
	 *     // Output:
	 *     <input type="text" name="base" value="are belong to us"  />
	 *     
	 *     // With additional attributes
	 *     echo form::input(array('name' => 'base', 'id' => 'base'), 'are belong to us');
	 *     
	 *     // Output:
	 *     <input type="text" id="base" name="base" value="are belong to us"  />
	 *
	 * @param   mixed  $data  Input name or an array of attribute => value pairs
	 * @param   string $value Input value, when using a name
	 * @param   string $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function input($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Type and value are required attributes
		$data += array
		(
			'type'  => 'text',
			'value' => $value
		);

		return '<input'.form::attributes($data).' '.$extra.' />';
	}

	/**
	 * This method generates an HTML **input** element with a type attribute
	 * value of *password*.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::password('passphrase');
	 *     
	 *     // Output:
	 *     <input type="password" name="passphrase" value="" />
	 *     
	 *     // With additional attributes
	 *     echo form::password(array('name' => 'passphrase', 'id' => 'passphrase'));
	 *     
	 *     // Output:
	 *     <input type="password" id="passphrase" name="passphrase"  value=""  />
	 *
	 * @param   mixed  $data  Input name or an array of attribute => value pairs
	 * @param   string $value Input value, when using a name
	 * @param   string $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function password($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';

		return form::input($data, $value, $extra);
	}

	/**
	 * This method generates an HTML **input** element with a type attribute
	 * value of *file*.
	 * 
	 * [!!] Don't forget that you need a multipart form to do file uploads!
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::upload('mugshot');
	 *     
	 *     // Output:
	 *     <input type="file" name="mugshot" value="" />
	 *     
	 *     // With additional attributes
	 *     echo form::upload(array('name' => 'mugshot', 'id' => 'mugshot'));
	 *     
	 *     // Output:
	 *     <input type="file" id="mugshot" name="mugshot" value="" />
	 *
	 * @param   mixed  $data  Input name or an array of attribute => value pairs
	 * @param   string $value Input value, when using a name
	 * @param   string $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function upload($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';

		return form::input($data, $value, $extra);
	}

	/**
	 * This method generates an HTML **textarea** element.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * The fourth function argument takes as its value a boolean and
	 * toggles whether the existing entities should be encoded. Its
	 * default is `TRUE`.
	 *
	 * ###### Example
	 *     
	 *     // A simple textarea
	 *     echo form::textarea('comment');
	 *     
	 *     // Output:
	 *     <textarea name="mugshot" rows="" cols="" ></textarea>
	 *     
	 *     // With additional attributes
	 *     echo form::textarea(array('name' => 'comment', 'id' => 'comment', 'cols' => 40, 'rows' => 10), 'Enter your comment here...');
	 *     
	 *     // Output:
	 *     <textarea id="comment" name="comment" rows="10" cols="40" >Enter your comment here...</textarea>
	 * 
	 * @param   mixed   $data          Input name or an array of attribute => value pairs
	 * @param   string  $value         Input value, when using a name
	 * @param   string  $extra         A string to be attached to the end of the attributes
	 * @param   boolean $double_encode Encode existing entities
	 * @return  string
	 */
	public static function textarea($data, $value = '', $extra = '', $double_encode = TRUE)
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if ( ! isset($data['rows']))
		{
			$data['rows'] = '';
		}

		if ( ! isset($data['cols']))
		{
			$data['cols'] = '';
		}

		// Use the value from $data if possible, or use $value
		$value = isset($data['value']) ? $data['value'] : $value;

		// Value is not part of the attributes
		unset($data['value']);

		return '<textarea'.form::attributes($data, 'textarea').' '.$extra.'>'.htmlspecialchars($value, ENT_QUOTES, Kohana::CHARSET, $double_encode).'</textarea>';
	}

	/**
	 * This method generates an HTML **select** element (dropdown menu).
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value either a string
	 * or array; if provided a string, it will match that value with
	 * the *value* attribute of a **property** element and assign the
	 * *selected* attributed. However, if an array is provided, it will
	 * consider the **select** element to be a multiselect (by
	 * assigning the *multiple* attribute to the **select** element)
	 * and map the array of given values to the *value* attribute of
	 * the **option** elements. 
	 *
	 * The fourth function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::dropdown('state', array('CA' => 'California', 'AZ' => 'Arizona'), 'AZ');
	 *     
	 *     // Output:
	 *     <select name="state">
	 *       <option value="CA">California</option>
	 *       <option value="AZ" selected="selected">Arizona</option>
	 *     </select>
	 * 
	 * @param   mixed   $data     Input name or an array of attribute => value pairs
	 * @param   array   $options  Select options, when using a name
	 * @param   mixed   $selected Option key(s) that should be selected by default
	 * @param   string  $extra    A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function dropdown($data, $options = NULL, $selected = NULL, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}
		else
		{
			if (isset($data['options']))
			{
				// Use data options
				$options = $data['options'];
			}

			if (isset($data['selected']))
			{
				// Use data selected
				$selected = $data['selected'];
			}
		}

		if (is_array($selected))
		{
			// Multi-select box
			$data['multiple'] = 'multiple';
		}
		else
		{
			// Single selection (but converted to an array)
			$selected = array($selected);
		}

		$input = '<select'.form::attributes($data, 'select').' '.$extra.'>'."\n";
		foreach ((array) $options as $key => $val)
		{
			// Key should always be a string
			$key = (string) $key;

			if (is_array($val))
			{
				$input .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $inner_key => $inner_val)
				{
					// Inner key should always be a string
					$inner_key = (string) $inner_key;

					$sel = in_array($inner_key, $selected) ? ' selected="selected"' : '';
					$input .= '<option value="'.$inner_key.'"'.$sel.'>'.htmlspecialchars($inner_val, ENT_QUOTES, Kohana::CHARSET, FALSE).'</option>'."\n";
				}
				$input .= '</optgroup>'."\n";
			}
			else
			{
				$sel = in_array($key, $selected) ? ' selected="selected"' : '';
				$input .= '<option value="'.$key.'"'.$sel.'>'.htmlspecialchars($val, ENT_QUOTES, Kohana::CHARSET, FALSE).'</option>'."\n";
			}
		}
		$input .= '</select>';

		return $input;
	}

	/**
	 * This method generates an HTML **input** element with a type attribute
	 * value of *checkbox*.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a boolean and
	 * toggles whether the *selected* attribute is applied to the element.
	 *
	 * The fourth function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::checkbox('god_mode', 'Yes');
	 *     
	 *     // Output:
	 *     <input type="checkbox" name="god_mode" value="Yes"  />
	 *     
	 *     // A selected checkbox
	 *     echo form::checkbox('god_mode', 'Yes', TRUE);
	 *     
	 *     // Output:
	 *     echo form::checkbox('god_mode', 'Yes', checked="checked");
	 *
	 * @param   mixed   $data    Input name or an array of attribute => value pairs
	 * @param   string  $value   Input value, when using a name
	 * @param   boolean $checked Toggle the checked attributed
	 * @param   string  $extra   A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function checkbox($data, $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'checkbox';

		if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
		{
			$data['checked'] = 'checked';
		}
		else
		{
			unset($data['checked']);
		}

		return form::input($data, $value, $extra);
	}

	/**
	 * This method generates an HTML **input** element with a type attribute
	 * value of *radio*.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a boolean and
	 * toggles whether the *selected* attribute is applied to the element.
	 *
	 * The fourth function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     // A typical gender poll
	 *     echo form::radio('gender', 'male', TRUE);
	 *     echo form::radio('gender', 'female');
	 *     
	 *     // Output:
	 *     <input type="radio" name="gender" value="male" checked="checked"  />
	 *     <input type="radio" name="gender" value="female"  />
	 *
	 * @param   mixed   $data    Input name or an array of attribute => value pairs
	 * @param   string  $value   Input value, when using a name
	 * @param   boolean $checked Toggle the checked attributed
	 * @param   string  $extra   A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function radio($data = '', $value = '', $checked = FALSE, $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'radio';

		if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
		{
			$data['checked'] = 'checked';
		}
		else
		{
			unset($data['checked']);
		}

		return form::input($data, $value, $extra);
	}

	/**
	 * This method generates an HTML **input** element with a type
	 * attribute value of *submit*.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * Note: using [form::button] to generate a submission button is
	 * semantically preferred over using a submission input.
	 *
	 * ###### Example
	 *     
	 *     echo form::submit('', 'Submit');
	 *     
	 *     // Output:
	 *     <input type="submit" value="Submit"  />
	 *
	 * @param   mixed  $data  Input name or an array of attribute => value pairs
	 * @param   string $value Input value, when using a name
	 * @param   string $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function submit($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if (empty($data['name']))
		{
			// Remove the name if it is empty
			unset($data['name']);
		}

		$data['type'] = 'submit';

		return form::input($data, $value, $extra);
	}

	/**
	 * This method generates an HTML **button** element.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *name* attribute. An array will be used as attribute =>
	 * value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the element's *value* attribute.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     // A submit button example
	 *     echo form::button(array('type' => 'submit'), 'Login'));
	 *     
	 *     // Output:
	 *     <button type="submit" >Login</button>
	 *
	 * @param   mixed  $data  Input name or an array of attribute => value pairs
	 * @param   string $value Input value, when using a name
	 * @param   string $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function button($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if (empty($data['name']))
		{
			// Remove the name if it is empty
			unset($data['name']);
		}

		if (isset($data['value']) AND empty($value))
		{
			$value = arr::remove('value', $data);
		}

		return '<button'.form::attributes($data, 'button').' '.$extra.'>'.$value.'</button>';
	}

	/**
	 * This method generates an HTML **label** element.
	 *
	 * The first function argument takes as its value either a string
	 * or array; if provided a string it will use that as the value
	 * for the *for* attribute which must have a corresponding element
	 * with an *id* attribute of the same value. An array will be used
	 * as attribute => value pairs in the element.
	 *
	 * The second function argument takes as its value a string and is
	 * used for the label's display text or inner HTML.
	 *
	 * The third function argument takes as its value a string and is
	 * appended within the element after attributes are applied.
	 *
	 * ###### Example
	 *     
	 *     echo form::label('username', 'Username: ');
	 *     echo form::input(array('name' => 'username', 'id' => 'username'));
	 *     
	 *     // Output:
	 *     <label for="username" >Username: </label>
	 *     <input type="text" id="username" name="username" value=""  />
	 * 
	 * @param   mixed  $data  Label "for" value or an array of attribute => value pairs
	 * @param   string $text  Label text or HTML
	 * @param   string $extra A string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function label($data = '', $text = NULL, $extra = '')
	{
		if ( ! is_array($data))
		{
			if (is_string($data))
			{
				// Specify the input this label is for
				$data = array('for' => $data);
			}
			else
			{
				// No input specified
				$data = array();
			}
		}

		if ($text === NULL AND isset($data['for']))
		{
			// Make the text the human-readable input name
			$text = ucwords(inflector::humanize($data['for']));
		}

		return '<label'.form::attributes($data).' '.$extra.'>'.$text.'</label>';
	}

	/**
	 * This method sorts an attribute => value array of HTML
	 * attributes, putting form attributes first, and returning an
	 * attribute string.
	 *
	 * ###### Example
	 *     
	 *     echo Kohana::debug(form::attributes(array('value' => 'Ronald', 'id' => 'username', 'class' => 'login')));
	 *     
	 *     // Output:
	 *     (string)  id="username" value="Ronald" class="login"
	 *
	 * @param   array   $attr HTML attributes array
	 * @return  string
	 */
	public static function attributes($attr)
	{
		if (empty($attr))
			return '';

		$order = array
		(
			'action',
			'method',
			'type',
			'id',
			'name',
			'value',
			'src',
			'size',
			'maxlength',
			'rows',
			'cols',
			'accept',
			'tabindex',
			'accesskey',
			'align',
			'alt',
			'title',
			'class',
			'style',
			'selected',
			'checked',
			'readonly',
			'disabled'
		);

		$sorted = array();
		foreach ($order as $key)
		{
			if (isset($attr[$key]))
			{
				// Move the attribute to the sorted array
				$sorted[$key] = $attr[$key];

				// Remove the attribute from unsorted array
				unset($attr[$key]);
			}
		}

		// Combine the sorted and unsorted attributes and create an HTML string
		return html::attributes(array_merge($sorted, $attr));
	}

} // End form
