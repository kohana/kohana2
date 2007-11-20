<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: form
 *  Form helper class.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class form {

	/*
	 * Method: open
	 *  Generates an opening HTML form tag.
	 *
	 * Parameters:
	 *  action - form action attribute
	 *  attr   - extra attributes
	 *  hidden - hidden fields to be created immediately after the form tag
	 *
	 * Returns:
	 *  An HTML form tag.
	 */
	public static function open($action = '', $attr = array(), $hidden = array())
	{
		// Make sure that the method is always set
		$attr += array
		(
			'method' => 'post'
		);

		// Make sure that the method is valid
		$attr['method'] = ($attr['method'] == 'post') ? 'post' : 'get';

		// Default action is to use the current URI
		if ($action == '' OR ! is_string($action))
		{
			$action = url::site(Router::$current_uri);
		}
		elseif (strpos($action, '://') === FALSE)
		{
			$action = url::site($action);
		}
		
		// Form opening tag
		$form = '<form action="'.$action.'"'.self::attributes($attr).'>'."\n";

		// Add hidden fields
		if (is_array($hidden) AND count($hidden > 0))
		{
			$form .= self::hidden($hidden);
		}

		return $form;
	}

	/*
	 * Method: open_multipart
	 *  Generates an opening HTML form tag that can be used for uploading files.
	 *
	 * Parameters:
	 *  action - form action attribute
	 *  attr   - extra attributes
	 *  hidden - hidden fields to be created immediately after the form tag
	 *
	 * Returns:
	 *  An HTML form tag.
	 */
	public static function open_multipart($action = '', $attr = array(), $hidden = array())
	{
		// Set multi-part form type
		$attr['enctype'] = 'multipart/form-data';

		return self::open($action, $attr, $hidden);
	}

	/*
	 * Method: hidden
	 *  Generates hidden form fields.
	 *  You can pass a simple key/value string or an associative array with multiple values.
	 *
	 * Parameters:
	 *  data  - input name (string) or key/value pairs (array)
	 *  value - input value, if using an input name
	 *
	 * Returns:
	 *  One or more HTML hidden form input tags.
	 */
	public static function hidden($data, $value = '')
	{
		if ( ! is_array($data))
		{
			$data = array
			(
				$data => $value
			);
		}

		$input = '';
		foreach($data as $name => $value)
		{
			$attr = array
			(
				'type'  => 'hidden',
				'name'  => $name,
				'value' => $value
			);

			$input .= self::input($attr)."\n";
		}

		return $input;
	}

	/*
	 * Method: input
	 *  Creates an HTML form input tag. Defaults to a text type.
	 *
	 * Parameters:
	 *  data  - input name or an array of HTML attributes
	 *  value - input value, when using a name
	 *  extra - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form input tag.
	 */
	public static function input($data = '', $value = '', $extra = '')
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

		// Form elements should have the same id as name
		if ( ! isset($data['id']))
		{
			$data['id'] = $data['name'];
		}

		// For safe form data
		$data['value'] = html::specialchars($data['value']);

		return '<input'.self::attributes($data).$extra.' />';
	}

	/*
	 * Method: password
	 *  Creates a HTML form password input tag.
	 *
	 * Parameters:
	 *  data  - input name or an array of HTML attributes
	 *  value - input value, when using a name
	 *  extra - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form password input tag.
	 */
	public static function password($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';

		return self::input($data, $value, $extra);
	}

	/*
	 * Method: upload
	 *  Creates an HTML form upload input tag.
	 *
	 * Parameters:
	 *  data  - input name or an array of HTML attributes
	 *  value - input value, when using a name
	 *  extra - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form upload tag.
	 */
	public static function upload($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';

		return self::input($data, $value, $extra);
	}

	/*
	 * Method: textarea
	 *  Creates an HTML form textarea tag.
	 *
	 * Parameters:
	 *  data  - textarea name or an array of HTML attributes
	 *  value - textarea value, when using a name
	 *  extra - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form textarea tag.
	 */
	public static function textarea($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Use the value from $data if possible, or use $value
		$value = isset($data['value']) ? $data['value'] : $value;

		// Value is not part of the attributes
		unset($data['value']);

		return '<textarea'.self::attributes($data).'>'.html::specialchars($value).'</textarea>';
	}

	/*
	 * Method: dropdown
	 *  Creates an HTML form select tag, or "dropdown menu".
	 *
	 * Parameters:
	 *  data     - select name or an array of HTML attributes
	 *  options  - select options, when using a name
	 *  selected - option key that should be selected by default
	 *  extra    - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form select tag, with options.
	 */
	public static function dropdown($data = '', $options = array(), $selected = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$input = '<select '.self::attributes($data).$extra.'>'."\n";
		foreach ($options as $key => $val)
		{
			$sel = ($selected == $key) ? ' selected="selected"' : '';

			$input .= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>'."\n";
		}
		$input .= '</select>';

		return $input;
	}

	/*
	 * Method: checkbox
	 *  Creates an HTML form checkbox input tag.
	 *
	 * Parameters:
	 *  data    - input name or an array of HTML attributes
	 *  value   - input value, when using a name
	 *  checked - make the checkbox checked by default
	 *  extra   - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form checkbox tag.
	 */
	public static function checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
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

		return self::input($data, $value, $extra);
	}

	/*
	 * Method: radio
	 *  Creates an HTML form radio input tag.
	 *
	 * Parameters:
	 *  data    - input name or an array of HTML attributes
	 *  value   - input value, when using a name
	 *  checked - make the radio selected by default
	 *  extra   - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form radio tag.
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

		return self::input($data, $value, $extra);
	}

	/*
	 * Method: submit
	 *  Creates an HTML form submit input tag.
	 *
	 * Parameters:
	 *  data    - input name or an array of HTML attributes
	 *  value   - input value, when using a name
	 *  extra   - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form submit tag.
	 */
	public static function submit($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'submit';

		return self::input($data, $value, $extra);
	}

	/*
	 * Method: button
	 *  Creates an HTML form button input tag.
	 *
	 * Parameters:
	 *  data    - input name or an array of HTML attributes
	 *  value   - input value, when using a name
	 *  extra   - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form button tag.
	 */
	public static function button($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data += array
		(
			'type'  => 'button'
		);

		if (isset($data['value']))
		{
			$value = arr::remove('value', $data);
		}

		return '<button'.self::attributes($data).$extra.'>'.html::specialchars($value).'</button>';
	}

	/*
	 * Method: close
	 *  Closes an open form tag.
	 *
	 * Parameters:
	 *  extra - string to be attached after the closing tag
	 *
	 * Returns:
	 *  A closing HTML form tag.
	 */
	public static function close($extra = '')
	{
		return '</form>'."\n".$extra;
	}

	/*
	 * Method: label
	 *  Creates an HTML form label tag.
	 *
	 * Parameters:
	 *  data  - label "for" name or an array of HTML attributes
	 *  text  - label text or HTML
	 *  extra - a string to be attached to the end of the attributes
	 *
	 * Returns:
	 *  An HTML form submit tag.
	 */
	public static function label($data = '', $text = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			if (strpos($data, '[') !== FALSE)
			{
				$data = preg_replace('/\[(?:.+)?\]/', '', $data);
			}

			$data = array
			(
				'for' => $data
			);
		}

		return '<label'.self::attributes($data).$extra.'>'.$text.'</label>';
	}

	/*
	 * Method: attributes
	 *  Sorts a key/value array of HTML attributes, putting form attributes first,
	 *  and returns an attribute string.
	 *
	 * Parameters:
	 *  attr - HTML attributes array
	 *
	 * Returns:
	 *  An HTML attribute string.
	 */
	public static function attributes($attr)
	{
		$order = array
		(
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
		foreach($order as $key)
		{
			if (isset($attr[$key]))
			{
				$sorted[$key] = $attr[$key];
			}
		}

		$sorted = array_merge($sorted, $attr);

		return html::attributes($sorted);
	}

} // End form