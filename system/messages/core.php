<?php

$messages = array
(
	E_KOHANA             => array( 1, __('Framework Error'),   __('Please check the Kohana documentation for information about the following error.')),
	E_PAGE_NOT_FOUND     => array( 1, __('Page Not Found'),    __('The requested page was not found. It may have moved, been deleted, or archived.')),
	E_DATABASE_ERROR     => array( 1, __('Database Error'),    __('A database error occurred while performing the requested procedure. Please review the database error below for more information.')),
	E_RECOVERABLE_ERROR  => array( 1, __('Recoverable Error'), __('An error was detected which prevented the loading of this page. If this problem persists, please contact the website administrator.')),
	E_ERROR              => array( 1, __('Fatal Error'),       ''),
	E_USER_ERROR         => array( 1, __('Fatal Error'),       ''),
	E_PARSE              => array( 1, __('Syntax Error'),      ''),
	E_WARNING            => array( 1, __('Warning Message'),   ''),
	E_USER_WARNING       => array( 1, __('Warning Message'),   ''),
	E_STRICT             => array( 2, __('Strict Mode Error'), ''),
	E_NOTICE             => array( 2, __('Runtime Message'),   ''),
	'config'             => 'config file',
	'controller'         => 'controller',
	'helper'             => 'helper',
	'library'            => 'library',
	'driver'             => 'driver',
	'model'              => 'model',
	'view'               => 'view',
);