<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'Na jedno wywołanie strony można powołać tylko jedną instancję Kohany.',
	'uncaught_exception'    => 'Nieobsługiwany %s: %s w pliku %s w lini %s',
	'invalid_method'        => 'Nieprawidłowa metoda %s wywołana w %s.',
	'cannot_write_log'      => 'Katalog zapisu dziennika w konfiguracji, wskazuje na położenie tylko do odczytu.',
	'resource_not_found'    => 'Żądany %s, %s, Nie może zostać znaleziony.',
	'invalid_filetype'      => 'Żądany typ pliku, .%s, w konfiguracji widoków nie jest podany jako dozwolony.',
	'no_default_route'      => 'Proszę ustawić domyślny adres wywołania w config/routes.php.',
	'no_controller'         => 'Kohana nie była w stanie określić kontrolera obsługującego wywołanie: %s',
	'page_not_found'        => 'Wywołana strona, %s, nie może zostać znaleziona.',
	'stats_footer'          => 'Czas wywołania: {execution_time} sekund, użyto {memory_usage} MB pamięci. Wygenerowano przez Kohana v{kohana_version}.',
	'error_message'         => 'Wystąpił błąd w <strong>lini %s</strong> w pliku <strong>%s</strong>.',
	'stack_trace'           => 'Zrzut stosu (Stack Trace)',
	'generic_error'         => 'Nie można zakończyć żądania',
	'errors_disabled'       => 'Przejdź na <a href="%s">stronę główną</a> lub <a href="%s">spróbuj znowu</a>.',

	// Resource names
	'controller'            => 'kontroler',
	'helper'                => 'pomocnik',
	'library'               => 'biblioteka',
	'driver'                => 'sterownik',
	'model'                 => 'model',
	'view'                  => 'widok',
);