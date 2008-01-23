<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Form helper class.
 *
 * $Id$
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class form_Core {

	/**
	 * Generates an opening HTML form tag.
	 *
	 * @param   string  form action attribute
	 * @param   array   extra attributes
	 * @param   array   hidden fields to be created immediately after the form tag
	 * @return  string
	 */
	public static function open($action = '', $attr = array(), $hidden = NULL)
	{
		// Make sure that the method is always set
		empty($attr['method']) and $attr['method'] = 'post';

		if ($attr['method'] != 'post' AND $attr['method'] != 'get')
		{
			// If the method is invalid, use post
			$attr['method'] = 'post';
		}

		if (empty($action) OR ! is_string($action))
		{
			// Use the current URL as the default action
			$action = url::site(Router::$current_uri);
		}
		elseif (strpos($action, '://') === FALSE)
		{
			// Make the action URI into a URL
			$action = url::site($action);
		}

		// Form opening tag
		$form = '<form action="'.$action.'"'.self::attributes($attr).'>'."\n";

		// Add hidden fields immediate after opening tag
		empty($hidden) or $form .= self::hidden($hidden);

		return $form;
	}

	/**
	 * Generates an opening HTML form tag that can be used for uploading files.
	 *
	 * @param   string  form action attribute
	 * @param   array   extra attributes
	 * @param   array   hidden fields to be created immediately after the form tag
	 * @return  string
	 */
	public static function open_multipart($action, $attr = array(), $hidden = array())
	{
		// Set multi-part form type
		$attr['enctype'] = 'multipart/form-data';

		return self::open($action, $attr, $hidden);
	}

	/**
	 * Generates hidden form fields.
	 * You can pass a simple key/value string or an associative array with multiple values.
	 *
	 * @param   string|array  input name (string) or key/value pairs (array)
	 * @param   string        input value, if using an input name
	 * @return  string
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

	/**
	 * Creates an HTML form input tag. Defaults to a text type.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
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

		// Form elements should have the same id as name
		if ( ! empty($extra))
		{
			// Make sure there is 1 space before extra
			$extra = ' '.ltrim($extra);
		}

		// For safe form data
		$data['value'] = html::specialchars($data['value']);

		return '<input'.self::attributes($data).$extra.' />';
	}

	/**
	 * Creates a HTML form password input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function password($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'password';

		return self::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form upload input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function upload($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'file';

		return self::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form textarea tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function textarea($data, $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		// Use the value from $data if possible, or use $value
		$value = isset($data['value']) ? $data['value'] : $value;

		// Value is not part of the attributes
		unset($data['value']);

		if ( ! empty($extra))
		{
			// Make sure there is 1 space before extra
			$extra = ' '.ltrim($extra);
		}

		return '<textarea'.self::attributes($data).$extra.'>'.html::specialchars($value).'</textarea>';
	}

	/**
	 * Creates an HTML form select tag, or "dropdown menu".
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   array         select options, when using a name
	 * @param   string        option key that should be selected by default
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function dropdown($data, $options = array(), $selected = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		if ( ! empty($extra))
		{
			// Make sure there is 1 space before extra
			$extra = ' '.ltrim($extra);
		}

		$input = '<select'.self::attributes($data).$extra.'>'."\n";
		foreach ($options as $key => $val)
		{
			if (is_array($val))
			{
				$input .= '<optgroup label="'.$key.'">'."\n";
				foreach ($val as $inner_key => $inner_val)
				{
					$sel = ($selected === $inner_key) ? ' selected="selected"' : '';
					$input .= '<option value="'.$inner_key.'"'.$sel.'>'.$inner_val.'</option>'."\n";
				}
				$input .= '</optgroup>'."\n";
			}
			else
			{
				$sel = ($selected === $key) ? ' selected="selected"' : '';
				$input .= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>'."\n";
			}
		}
		$input .= '</select>';

		return $input;
	}

	/**
	 * Creates an HTML form checkbox input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   boolean       make the checkbox checked by default
	 * @param   string        a string to be attached to the end of the attributes
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

		return self::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form radio input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   boolean       make the radio selected by default
	 * @param   string        a string to be attached to the end of the attributes
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

		return self::input($data, $value, $extra);
	}

	/**
	 * Creates an HTML form submit input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
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

	/**
	 * Creates an HTML form button input tag.
	 *
	 * @param   string|array  input name or an array of HTML attributes
	 * @param   string        input value, when using a name
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
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

		if ( ! empty($extra))
		{
			// Make sure there is 1 space before extra
			$extra = ' '.ltrim($extra);
		}

		return '<button'.self::attributes($data).$extra.'>'.html::specialchars($value).'</button>';
	}

	/**
	 * Closes an open form tag.
	 *
	 * @param   string  string to be attached after the closing tag
	 * @return  string
	 */
	public static function close($extra = '')
	{
		return '</form>'."\n".$extra;
	}

	/**
	 * Creates an HTML form label tag.
	 *
	 * @param   string|array  label "for" name or an array of HTML attributes
	 * @param   string        label text or HTML
	 * @param   string        a string to be attached to the end of the attributes
	 * @return  string
	 */
	public static function label($data = '', $text = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			if (strpos($data, '[') !== FALSE)
			{
				$data = preg_replace('/\[.*\]/', '', $data);
			}

			$data = empty($data) ? array() : array('for' => $data);
		}

		if ( ! empty($extra))
		{
			// Make sure there is 1 space before extra
			$extra = ' '.ltrim($extra);
		}

		return '<label'.self::attributes($data).$extra.'>'.$text.'</label>';
	}

	/**
	 * Sorts a key/value array of HTML attributes, putting form attributes first,
	 * and returns an attribute string.
	 *
	 * @param   array   HTML attributes array
	 * @return  string
	 */
	public static function attributes($attr)
	{
		if ( ! empty($attr['name']) AND strpos($attr['name'], '[') === FALSE AND empty($attr['id']))
		{
			$attr['id'] = $attr['name'];
		}

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