<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'Er kan maar één instantie van Kohana zijn per pagina oproep.',
	'uncaught_exception'    => 'Uncaught %s: %s in bestand %s op lijn %s',
	'invalid_method'        => 'Ongeldige method <tt>%s</tt> opgeroepen in <tt>%s</tt>.',
	'cannot_write_log'      => 'Je log.directory instelling in <tt>config/config.php</tt> verwijst niet naar een schrijfbare directory.',
	'resource_not_found'    => 'De opgevraagde %s, <tt>%s</tt>, kon niet gevonden worden.',
	'invalid_filetype'      => 'Het opgevraagde bestandstype, <tt>.%s</tt>, wordt niet toegestaan door het view configuratiebestand.',
	'no_default_route'      => 'Zet een default route in <tt>config/routes.php</tt>.',
	'no_controller'         => 'Kohana kon geen controller aanduiden om deze pagina te verwerken: %s',
	'page_not_found'        => 'De opgevraagde pagina, <tt>%s</tt>, kon niet gevonden worden.',
	'stats_footer'          => 'Geladen in {execution_time} seconden, met een geheugengebruik van {memory_usage}. Aangedreven door Kohana v{kohana_version}.',
	'error_file_line'       => '<tt>%s <strong>[%s]:</strong></tt>',
	'stack_trace'           => 'Stack Trace',
	'generic_error'         => 'Oproep kon niet afgewerkt worden',
	'errors_disabled'       => 'Ga naar de <a href="%s">homepage</a> of <a href="%s">probeer opnieuw</a>.',

	// Resource names
	'controller'            => 'controller',
	'helper'                => 'helper',
	'library'               => 'library',
	'driver'                => 'driver',
	'model'                 => 'model',
	'view'                  => 'view',
);