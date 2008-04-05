<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
	'userfile_not_set'   => 'Impossibile trovare una variabile post chiamata %s.',
	'file_exceeds_limit' => 'Il file caricato eccede, in peso, il massimo valore consentito dalle impostazioni di PHP.',
	'file_partial'       => 'Il file è stato caricato parzialmente.',
	'no_file_selected'   => 'Non è stato selezionato un file da caricare.',
	'invalid_filetype'   => 'Il tipo di file che si sta cercando di caricare non è permesso.',
	'invalid_filesize'   => 'Il file che si sta cercando di caricare eccede le dimensioni massime consentite (%s).',
	'invalid_dimensions' => 'L\'immagine che si sta cercando di caricare eccede la massima altezza o larghezza (%s).',
	'destination_error'  => 'Si è verificato un problema durante lo spostamento del file caricato nella cartella di destinazione.',
	'no_filepath'        => 'Il percorso caricato non sembra essere valido.',
	'no_file_types'      => 'Non è stato specificato nessun tipo di file ammesso.',
	'bad_filename'       => 'Esiste già un file con lo stesso nome sul server.',
	'not_writable'       => 'La cartella di destinazione, %s, non sembra avere i permessi in scrittura.',
	'error_on_file'      => 'Errore di caricamento %s:',
	// Error code responses
	'set_allowed'        => 'Per motivi di sicurezza è opportuno definire quali tipi di file potranno essere caricati.',
	'max_file_size'      => 'Per motivi di sicurezza è opportuno non usare MAX_FILE_SIZE per cotrollare le dimensioni dei file da caricare.',
	'no_tmp_dir'         => 'Impossibile trovare una cartella temporanea accessibile in scrittura.',
	'tmp_unwritable'     => 'Impossibile scrivere nella cartella temporanea %s.'
);
