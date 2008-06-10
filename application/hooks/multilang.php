<?php defined('SYSPATH') or die('No direct script access.');

// Find TLD extension
preg_match('/kohanaphp\.(.+)$/', $_SERVER['SERVER_NAME'], $tld);

// TLD => lang
$langs = array
(
	'es' => 'es_ES',
	// 'nl' => 'nl_NL',
	// 'de' => 'de_DE',
	// 'fr' => 'fr_FR',
	// 'pl' => 'pl_PL',
);

if (isset($langs[$tld]))
{
	// Set the language
	Config::set('local.language', $langs[$tld]);
}

// Clean up
unset($langs, $tld);