<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'Pro Seitenaufruf kann es nur eine Instanz von Kohana geben.',
	'uncaught_exception'    => 'Unerwarteter Fehler vom Typ %s: %s in %s in Zeile %s',
	'invalid_method'        => 'Ungültige Methode <tt>%s</tt> aufgerufen in <tt>%s</tt>.',
	'cannot_write_log'      => 'Ihre Einstellung log.directory in der Konfiguration verweist nicht auf ein beschreibbares Verzeichnis.',
	'resource_not_found'    => '%s <tt>%s</tt> konnte nicht gefunden werden.',
	'invalid_filetype'      => 'Die Dateiendung <tt>.%s</tt> ist in Ihrer View-Konfiguration nicht vorhanden.',
	'no_default_route'      => 'Erstellen Sie bitte eine Standardroute <tt>config/routes.php</tt>.',
	'no_controller'         => 'Kohana gelang es nicht einen Controller zu finden, um diesen Aufruf zu verarbeiten: %s',
	'page_not_found'        => 'Die aufgerufene Seite, <tt>%s</tt>, konnte nicht gefunden werden.',
	'stats_footer'          => 'Seite geladen in {execution_time} Sekunden bei {memory_usage} Speichernutzung. Generiert von Kohana v{kohana_version}.',
	'error_file_line'       => '<tt>%s <strong>[%s]:</strong></tt>',
	'stack_trace'           => 'Stack Trace',
	'generic_error'         => 'Die Abfrage konnte nicht abgeschlossen werden.',
	'errors_disabled'       => 'Sie können zur <a href="%s">Startseite</a> zurück kehren oder es <a href="%s">erneut versuchen</a>.',

	// Resource names
	'controller'            => 'Der Controller',
	'helper'                => 'Der Helfer',
	'library'               => 'Die Bibliothek',
	'driver'                => 'Der Treiber',
	'model'                 => 'Das Modell',
	'view'                  => 'Die Ansicht',
);