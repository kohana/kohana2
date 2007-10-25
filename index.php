<?php
/*
 * File: index.php
 *  This file acts as the "front controller" to your application. You can
 *  configure your application and system directories here, as well as error
 *  reporting and error display.
 *
 * Kohana Source Code:
 *  author    - Kohana Team
 *  copyright - (c) 2007 Kohana Team
 *  license   - <http://kohanaphp.com/license.html>
 *
 * Credits:
 *  - Kohana was originally a fork of CodeIgniter <http://codeigniter.com> (c) 2006 Ellis Labs
 *  - XSS cleaning from popoon <http://www.popoon.org> (c) 2001-2006 Bitflux GmbH
 *  - HTML cleaning from <http://htmlpurifier.org> (c) 2006-2007 Edward Z. Yang
 */

/*
 * About: Error Reporting
 *  Set the error reporting level. By default, Kohana will ignore E_NOTICE
 *  messages. Unless you have a special need, E_ALL is a good level for
 *  error reporting.
 */
error_reporting(E_ALL);

/*
 * About: Display Errors
 *  Enable or disable error display. During development, it is very helpful
 *  to display errors. However, errors can give away information about your
 *  application. For greater security, it is recommended that you disable
 *  the displaying of errors in production.
 */
ini_set('display_errors', TRUE);

/*
 * About: Application Directory
 *  Kohana website application directory. Most of your controllers, models, and
 *  views will by placed in this directory. This directory must contain the
 *  <config/config.php> file.
 */
$kohana_application = 'application';

/*
 * About: System Directory
 *  Kohana core resources, includes libraries, drivers, language files, helpers,
 *  and library-related views.
 */
$kohana_system = 'system';

/*
 * About: Filename Extension
 *  If you have to rename all of the .php files distributed with Kohana to a
 *  different filename, you set the new extension here. Most people will never
 *  use this option.
 */
define('EXT', '.php');

/*
 * PLEASE DO NOT EDIT BELOW THIS LINE, unless you understand the repercussions!
 * ----------------------------------------------------------------------------
 * $Id$
 */
$docroot = pathinfo(str_replace('\\', '/', realpath(__FILE__)));

// Define the docroot
define('KOHANA',  $docroot['basename']);
define('DOCROOT', $docroot['dirname'].'/');

// Define application and system paths
define('APPPATH', str_replace('\\', '/', realpath($kohana_application)).'/');
define('SYSPATH', str_replace('\\', '/', realpath($kohana_system)).'/');

// Clean up
unset($docroot, $kohana_application, $kohana_system);

(is_dir(APPPATH) AND is_dir(APPPATH.'/config')) or die
(
	'Your <code>$kohana_application</code> directory does not exist. '.
	'Set a valid <code>$kohana_application</code> in <tt>index.php</tt> and refresh the page.'
);

(is_dir(SYSPATH) AND file_exists(SYSPATH.'/core/'.'Bootstrap'.EXT)) or die
(
	'Your <code>$kohana_system</code> directory does not exist. '.
	'Set a valid <code>$kohana_system</code> in <tt>index.php</tt> and refresh the page.'
);

require_once SYSPATH.'core/Bootstrap'.EXT;