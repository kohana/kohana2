<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'getimagesize_missing'    => 'De Image library vereist de <tt>getimagesize</tt> PHP functie en die is niet beschikbaar op dit systeem.',
	'driver_not_supported'    => 'De %s Image driver werd niet gevonden.',
	'unsupported_method'      => 'De huidige Image driver ondersteunt volgende transformatie niet: %s.',
	'file_not_found'          => 'De opgegeven afbeelding, %s, werd niet gevonden. Controleer a.u.b. eerst of afbeeldingen bestaan via de <tt>file_exists</tt> functie voordat je ze begint te bewerken.',
	'type_not_allowed'        => 'De opgegeven afbeelding, %s, is geen toegestaan afbeeldingstype.',
	'invalid_width'           => 'De breedte die je opgaf, %s, is ongeldig.',
	'invalid_height'          => 'De hoogte die je opgaf, %s, is ongeldig.',
	'invalid_dimensions'      => 'De afmetingen die je opgaf voor %s zijn ongeldig.',
	'invalid_master'          => 'De master dim die je opgaf, is ongeldig.',
	'invalid_flip'            => 'De spiegelrichting die je opgaf, is ongeldig.',

	// ImageMagick specific messages
	'imagemagick' => array
	(
		'not_found' => 'De opgegeven ImageMagick directory bevat een vereist programma niet: %s.',
	),

	// GD specific messages
	'gd' => array
	(
		'requires_v2' => 'De Image library vereist GD2. Kijk op http://php.net/gd_info voor meer informatie.',
	),
);
