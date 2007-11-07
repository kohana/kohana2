<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Class: Image_Gd_Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
class Image_Gd_Driver implements Image_Driver {

	public function __construct()
	{
		Log::add('debug', 'Image GD Driver Initialized');
	}

	public function version()
	{
		return 'GD '.current(gd_info());
	}

}