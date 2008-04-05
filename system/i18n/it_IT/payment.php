<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
	'driver_not_supported'     => 'Il driver %s, richiesto dalla libreria Payment, non è stato trovato.',
	'driver_implements'        => 'Il driver %s, richiesto dalla libreria Payment, non implementa l\'interfaccia Payment_Driver.',
	'required'                 => 'Alcuni campi obbligatori non sono stati forniti: %s',
	'gateway_connection_error' => 'Si è verificato un errore durante la connessione alla piattaforma di pagamento. Se il problema persiste contattare l\'amministratore del sito.',
	'invalid_certificate'      => 'Certificato non valido: %s',
	'no_dlib'                  => 'Impossibile caricare la libreria dinamica: %s',
	'error'                    => 'Si è verificato un errore durante la transazione: %s',
);