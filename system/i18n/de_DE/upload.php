<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'userfile_not_set'   => 'Die POST-Variable %s konnte nicht gefunden werden.',
	'file_exceeds_limit' => 'Die hochgeladene Datei überschreitet die maximal erlaubte Größe, die in der PHP-Konfigurationsdatei eingetragen ist.',
	'file_partial'       => 'Die Datei wurde nur zum Teil hochgeladen.',
	'no_file_selected'   => 'Sie haben keine Datei zum Hochladen ausgewählt.',
	'invalid_filetype'   => 'Der Dateityp, den Sie versuchen hochzuladen, ist nicht erlaubt.',
	'invalid_filesize'   => 'Die Datei, die Sie versuchen hochzuladen, ist größer, als die erlaubte Größe (%s).',
	'invalid_dimensions' => 'Das Bild, das Sie versuchen hochzuladen, überschreitet die maximale Höhe oder Breite (%s).',
	'destination_error'  => 'Beim Versuch die hochgeladene Datei zum Zielort zu verschieben ist ein Fehler aufgetreten.',
	'no_filepath'        => 'Der Pfad für hochgeladene Dateien scheint ungültig zu sein.',
	'no_file_types'      => 'Sie haben keine erlaubten Dateitypen festgelegt.',
	'bad_filename'       => 'Der Dateiname ist schon auf dem Server vorhanden.',
	'not_writable'       => 'Das Verzeichnis für hochgeladene Dateien, %s, ist nicht beschreibbar.',
	'error_on_file'      => 'Fehler beim Hochladen von %s:',
	// Error code responses
	'set_allowed'        => 'Aus Sicherheitsgründen müssen Sie die Dateitypen, die hochgeladen werden dürfen, festlegen.',
	'max_file_size'      => 'Benutzen Sie bitte aus Sicherheitsgründen nicht MAX_FILE_SIZE, um die maximale Größe der Hochgeladenen Bilder zu überprüfen.',
	'no_tmp_dir'         => 'Es konnte kein temproräres Verzeichnis zum Beschreiben gefunden werden.',
	'tmp_unwritable'     => 'Das Verzeichnis %s ist nicht beschreibbar.'
);
