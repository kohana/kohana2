<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeignitor.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Kohana Form Helper
 *
 * @package     Kohana
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Rick Ellis
 * @link        http://www.codeigniter.com/user_guide/helpers/form_helper.html
 */

class form {

	/**
	 * Form Declaration
	 *
	 * Creates the opening portion of the form.
	 *
	 * @access  public
	 * @param   string  the URI segments of the form destination
	 * @param   array   a key/value pair of attributes
	 * @param   array   a key/value pair hidden data
	 * @return  string
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
			$action = Router::$current_uri;
		}

		// Form opening tag
		$form = '<form action="'.url::site($action).'"'.self::attributes($attr).'>'."\n";

		// Add hidden fields
		if (is_array($hidden) AND count($hidden > 0))
		{
			$form .= self::hidden($hidden);
		}

		return $form;
	}

	// ------------------------------------------------------------------------

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

		return html::attributes($sorted);
	}

	// ------------------------------------------------------------------------

	/**
	 * Form Declaration - Multipart type
	 *
	 * Creates the opening portion of the form, but with "multipart/form-data".
	 *
	 * @access  public
	 * @param   string  the URI segments of the form destination
	 * @param   array   a key/value pair of attributes
	 * @param   array   a key/value pair hidden data
	 * @return  string
	 */
	public static function open_multipart($action = '', $attr = array(), $hidden = array())
	{
		// Set multi-part form type
		$attr['enctype'] = 'multipart/form-data';

		return self::open($action, $attr, $hidden);
	}

	// ------------------------------------------------------------------------

	/**
	 * Hidden Input Field
	 *
	 * Generates hidden fields.  You can pass a simple key/value string or an associative
	 * array with multiple values.
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
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

	// ------------------------------------------------------------------------

	/**
	 * Text Input Field
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   string
	 * @return  string
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

	// ------------------------------------------------------------------------

	/**
	 * Password Field
	 *
	 * Identical to the input public static function but adds the "password" type
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   string
	 * @return  string
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

	// ------------------------------------------------------------------------

	/**
	 * Upload Field
	 *
	 * Identical to the input public static function but adds the "file" type
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   string
	 * @return  string
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

	// ------------------------------------------------------------------------

	/**
	 * Textarea field
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   string
	 * @return  string
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

	// ------------------------------------------------------------------------

	/**
	 * Drop-down Menu
	 *
	 * @access  public
	 * @param   mixed
	 * @param   array
	 * @param   string
	 * @param   string
	 * @return  string
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
			$sel = ($selected != $key) ? '' : ' selected="selected"';

			$input .= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>'."\n";
		}
		$input .= '</select>';

		return $input;
	}

	// ------------------------------------------------------------------------

	/**
	 * Checkbox Field
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   bool
	 * @param   string
	 * @return  string
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

	// ------------------------------------------------------------------------

	/**
	 * Radio Button
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   bool
	 * @param   string
	 * @return  string
	 */
	public static function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
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

	// ------------------------------------------------------------------------

	/**
	 * Submit Button
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public static function form_submit($data = '', $value = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array('name' => $data);
		}

		$data['type'] = 'submit';

		return self::input($data, $value, $extra);
	}

	// ------------------------------------------------------------------------

	/**
	 * Button Input
	 *
	 * @access  public
	 * @param   mixed
	 * @param   string
	 * @param   string
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
			$value = $data['value'];
			unset($data['value']);
		}

		return '<button'.self::attributes($data).$extra.'>'.html::specialchars($value).'</button>';
	}

	// ------------------------------------------------------------------------

	/**
	 * Form Close Tag
	 *
	 * @access  public
	 * @return  string
	 */
	public static function close($extra = '')
	{
		return '</form>'."\n".$extra;
	}

	// ------------------------------------------------------------------------

	/**
	 * Form Label
	 *
	 * @access  public
	 * @param   string
	 * @return  string
	 */
	public static function label($data = '', $text = '', $extra = '')
	{
		if ( ! is_array($data))
		{
			$data = array
			(
				'for' => $data
			);
		}

		return '<label'.self::attributes($data).$extra.'>'.html::specialchars($text).'</label>';
	}

} // End form class