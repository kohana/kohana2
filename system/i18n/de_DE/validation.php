<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	// Class errors
	'error_format'  => 'Ihre Nachricht muss {message} enthalten.',
	'invalid_rule'  => 'Ungültige Gültigkeitsprüfung benutzt: %s',

	// General errors
	'unknown_error' => 'Unbekannter Fehler bei der Gültigkeitsprüfung von dem Feld %s aufgetreten.',
	'required'      => 'Das Feld %s ist erforderlich.',
	'min_length'    => 'Das Feld %s muss mindestens %d Zeichen lang sein.',
	'max_length'    => 'Das Feld %s muss %d oder weniger Zeichen enthalten.',
	'exact_length'  => 'Das Feld %s muss genau %d Zeichen enthalten.',
	'in_array'      => 'Das Feld %s muss ausgewählt werden.',
	'matches'       => 'Das Feld %s mit dem Feld %s übereinstimmen.',
	'valid_url'     => 'Das Feld %s muss eine gültige URL beinhalten, die mit %s:// startet.',
	'valid_email'   => 'Das Feld %s muss eine gültige E-Mailadresse beinhalten.',
	'valid_ip'      => 'Das Feld %s muss eine gültige IP-Adresse beinhalten.',
	'valid_type'    => 'Das Feld %s darf nur aus %s Zeichen bestehen.',
	'range'         => 'Das Feld %s muss zwischen festgelegten Bereichen sein.',
	'regex'         => 'Das Feld %s entspricht nicht einer akzeptierten Eingabe.',
	'depends_on'    => 'Das Feld %s hängt vom Feld %s ab.',

	// Upload errors
	'user_aborted'  => 'Die Datei %s wurde beim Hochladen abgebrochen.',
	'invalid_type'  => 'Die Datei %s entspricht nicht den erlaubten Dateitypen.',
	'max_size'      => 'Die Datei %s ist zu groß. Die maximale Größe beträgt %s.',
	'max_width'     => 'Die Datei %s überschreitet die maximal erlaubte Breite von %spx.',
	'max_height'    => 'Die Datei %s überschreitet die maximal erlaubte Höhe von %spx.',
);
