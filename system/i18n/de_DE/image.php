<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'getimagesize_missing'    => 'Die Bildbibliothek versucht die PHP-Funktion <tt>getimagesize</tt> zu benutzen, die aber nicht bestandteil ihrer PHP-Installation ist',
	'driver_not_supported'    => 'Der Bildtreiber %s konnte nicht gefunden werden',
	'unsupported_method'      => 'Der Bildtreiber, den Sie benutzen, unterstützt nicht die %s-Bildtransformation.',
	'file_not_found'          => 'Das angegebene Bild %s konnte nicht gefunden werden. Stellen Sie bitte sicher, dass das Bild existiert. Benutzen Sie hierzu die Funktion <tt>file_exists</tt>.',
	'type_not_allowed'        => 'Das angegebene Bild %s ist kein erlaubter Bildtyp.',
	'invalid_width'           => 'Die von Ihnen festgelegte Bildbreite, %s, ist ungültig.',
	'invalid_height'          => 'Die von Ihnen festgelegte Bildhöhe, %s, ist ungültig.',
	'invalid_dimensions'      => 'Das festgelegte Format für %s ist ungültig.',
	'invalid_master'          => 'Die festgelegte Master-Dimension ist ungültig.',
	'invalid_flip'            => 'Die festgelegte Richtung der Spiegelung ist ungültig.',

	// ImageMagick specific messages
	'imagemagick' => array
	(
		'not_found' => 'Das festgelegte ImageMagic-Verzeichnis enthält nicht das benötigte Programm %s.',
	),

	// GD specific messages
	'gd' => array
	(
		'requires_v2' => 'Die Bildbibliothek erfordert GD2. Sehen Sie sich die Seite http://php.net/gd_info an für weitere Informationen.',
	),
);
