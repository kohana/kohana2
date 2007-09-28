<?php defined('SYSPATH') or die('No direct access allowed.');

class str {

	public static function reduce_slashes($str)
	{
		return preg_replace('#(?<!:)//+#', '/', $str);
	}

	public static function random($type = FALSE, $len = 8)
	{
		$type = ($type == FALSE) ? 'alnum' : strtolower($type);

		if ($type == 'unique')
		{
			$str = md5(uniqid(mt_rand()));
		}
		else
		{
			$pool = '';
			switch ($type)
			{
				case 'alnum':
					$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				case 'numeric':
					$pool = '0123456789';
				break;
				case 'nozero':
					$pool = '123456789';
				break;
			}

			$str = '';
			$max = strlen($pool)-1;

			for ($i=0; $i < $len; $i++)
			{
				$str .= substr($pool, rand(0, $max), 1);
			}
		}

		return $str;
	}

	public static function alternator()
	{
		static $i;

		if (func_num_args() == 0)
		{
			$i = 0;
			return '';
		}

		$args = func_get_args();

		return $args[($i++ % count($args))];
	}

} // End str Class