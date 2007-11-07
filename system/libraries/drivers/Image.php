<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Interface: Image_Driver
 *  Image API Driver
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 */
interface Image_Driver {

	/*
	 * Method: version
	 *  Returns the driver version.
	 *
	 * Returns:
	 *  The driver version.
	 */
	public function version();

} // End Image_Driver Interface