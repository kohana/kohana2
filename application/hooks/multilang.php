<?php defined('SYSPATH') or die('No direct script access.');

// Find TLD extension
preg_match('/kohanaphp\.(.+)$/', $_SERVER['SERVER_NAME'], $tld);

// Get TLD from match
$tld = empty($tld[1]) ? NULL : $tld[1];

// TLD => lang
$langs = array
(
	'es' => 'es_ES',
	'nl' => 'nl_NL',
	// 'de' => 'de_DE',
	// 'fr' => 'fr_FR',
	// 'pl' => 'pl_PL',
);

if ( ! empty($tld) AND isset($langs[$tld]))
{
	// Set the language
	Config::set('locale.language', $langs[$tld]);
}

// Clean up
unset($langs, $tld);