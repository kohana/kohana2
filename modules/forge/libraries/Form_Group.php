<?php defined('SYSPATH') or die('No direct script access.');

class Form_Group_Core extends Forge {

	protected $data = array
	(
		'type'  => 'group',
		'class' => 'group',
		'label' => '',
		'message' => ''
	);

	public function __construct($class = 'group')
	{
		$this->data['class'] = $class;
	}

	public function __get($key)
	{
		if ($key == 'type')
		{
			return $this->data['type'];
		}
		return parent::__get($key);
	}

	public function label($val = NULL)
	{
		if ($val === NULL)
		{
			if ($label = $this->data['label'])
			{
				return $this->data['label'];
			}
		}
		else
		{
			$this->data['label'] = ($val === TRUE) ? ucwords(inflector::humanize($this->data['label'])) : $val;
			return $this;
		}
	}

	public function message($val = NULL)
	{
		if ($val === NULL)
		{
			return $this->data['message'];
		}
		else
		{
			$this->data['message'] = $val;
			return $this;
		}
	}

	public function html()
	{
		// No Sir, we don't want any html today thank you
		return;
	}
}