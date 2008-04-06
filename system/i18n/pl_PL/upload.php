<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'userfile_not_set'   => 'Nie można znaleźć zmiennej o nazwie %s.',
	'file_exceeds_limit' => 'Wysyłany plik przekroczył wartość dozwoloną w głównym pliku konfiguracyjnym PHP',
	'file_partial'       => 'Plik został wysłany tylko częściowo',
	'no_file_selected'   => 'Nie wybrano żadnego pliku do wysłania',
	'invalid_filetype'   => 'Typ pliku który próbowano wysłać, nie jest dozwolonym typem.',
	'invalid_filesize'   => 'Rozmiar wysyłanego pliku przekracza dozwoloną wartość (maksymalnie %s)',
	'invalid_dimensions' => 'Wymiar pliku graficznego który próbowano wysłać przekracza dopuszczalną wartość (maksymalnie %spx)',
	'destination_error'  => 'Podczas przenoszenia wysyłanego pliku w miejsce docelowe, wystąpił błąd.',
	'no_filepath'        => 'Docelowa ścieżka nie jest ścieżką prawidłową.',
	'no_file_types'      => 'Nie zdefiniowano dozwolonych typów plików.',
	'bad_filename'       => 'Taka nazwa pliku który próbujesz wysłać już istnieje na serwerze.',
	'not_writable'       => 'Nie posiadasz prawa zapisu do docelowego katalogu %s.',
	'error_on_file'      => 'Błąd podczas zapisywania %s:',
	// Error code responses
	'set_allowed'        => 'Dla bezpieczeństwa, proszę wybrać typy plików dozwolonych do wysyłania',
	'max_file_size'      => 'Dla bezpieczeństwa, proszę nie używać MAX_FILE_SIZE do kontrolowania maksymalnej wielkości wysyłanych plików.',
	'no_tmp_dir'         => 'Nie można znaleźć tymczasowego katalogu do zapisu.',
	'tmp_unwritable'     => 'Nie można zapisać do tymczasowego katalogu %s',
);
