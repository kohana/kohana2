<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	// Class errors
	'error_format'  => 'La striga del messaggio d\'errore deve contenere {message} .',
	'invalid_rule'  => 'Regola di validazione usata non valida: %s',

	// General errors
	'unknown_error' => 'Errore sconosciuto durante la validazione del campo %s.',
	'required'      => 'Il campo %s è obbligatorio.',
	'min_length'    => 'Il campo %s deve essere lungo almeno %d caratteri.',
	'max_length'    => 'Il campo %s non deve superare i %d caratteri.',
	'exact_length'  => 'Il campo %s deve contenere esattamente %d caratteri.',
	'in_array'      => 'Il campo %s deve essere selezionato dalla lista delle opzioni.',
	'matches'       => 'Il campo %s deve coincidere con il campo %s.',
	'valid_url'     => 'Il campo %s deve contenere un URL valido.',
	'valid_email'   => 'Il campo %s deve contenere un indirizzo email valido.',
	'valid_ip'      => 'Il campo %s deve contenere un indirizzo IP valido.',
	'valid_type'    => 'Il campo %s deve contenere solo i caratteri %s.',
	'range'         => 'Il campo %s deve trovarsi negli intervalli specificati.',
	'regex'         => 'Il campo %s non coincide con i dati accettati.',
	'depends_on'    => 'Il campo %s dipende dal campo %s.',

	// Upload errors
	'user_aborted'  => 'Il caricamento del file %s è stato interrotto.',
	'invalid_type'  => 'Il file %s non è un tipo di file permesso.',
	'max_size'      => 'Il file %s è troppo grande. La massima dimensione consentita è %s.',
	'max_width'     => 'Il file %s deve avere una larghezza massima di %spx.',
	'max_height'    => 'Il file %s deve avere un\'altezza massima di %spx.',

	// Field types
	'alpha'         => 'alfabetico',
	'alpha_dash'    => 'alfabetico, trattino e sottolineato',
	'digit'         => 'cifra',
	'numeric'       => 'numerico',
);
