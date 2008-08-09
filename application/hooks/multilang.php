<?php defined('SYSPATH') or die('No direct script access.');

// Find TLD extension
preg_match('/kohanaphp\.(.+)$/', $_SERVER['SERVER_NAME'], $tld);

// Get TLD from match
define('TLD', empty($tld[1]) ? NULL : $tld[1]);

// TLD => lang
$langs = Kohana::config('locale.tlds');

if (isset($langs[TLD]))
{
	// Set the language
	Kohana::config_set('locale.language.0', $langs[TLD]);
}

// Clean up
unset($langs);