<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'Il ne peut y avoir qu\'une instance de Kohana par page.',
	'uncaught_exception'    => 'Uncaught %s: %s dans le fichier %s à la ligne %s',
	'invalid_method'        => 'La méthode <tt>%s</tt> appelée dans <tt>%s</tt> est invalide.',
	'cannot_write_log'      => 'Le répertoire spécifié dans votre fichier de configuration pour le fichier de log ne pointe pas vers un répertoire accessible en écriture.',
	'resource_not_found'    => 'La ressource %s, <tt>%s</tt>, n\'a pas été trouvée.',
	'invalid_filetype'      => 'Le type de ficher demandé, <tt>.%s</tt>, n\'est pas autorisé dans le fichier de configuration des vues (view configuration file).',
	'no_default_route'      => 'Aucune route par défaut n\a été définie. Veuillez la spécifer dans le fichier <tt>config/routes.php</tt>.',
	'no_controller'         => 'Kohana n\'a pu déterminer aucun controlleur pour effectuer la requête: %s.',
	'page_not_found'        => 'La page demandée <tt>%s</tt> n\'a pu être trouvée.',
	'stats_footer'          => 'Chargé en {execution_time} secondes, {memory_usage} de mémoire utilisée. Généré par Kohana v{kohana_version}.',
	'error_file_line'       => '<tt>%s <strong>[%s]:</strong></tt>',
	'stack_trace'           => 'Stack Trace',
	'generic_error'         => 'Impossible de terminer la requête.',
	'errors_disabled'       => 'Vous pouvez aller sur la <a href="%s">page d\'accueil</a> ou <a href="%s">essayer encore</a>.',

	// Drivers
	'driver_implements'     => 'Le driver %s de la librairie %s doit implémenter l\'interface %s.',
	'driver_not_found'      => 'Le driver %s de la librairie %s est introuvable.',

	// Resource names
	'controller'            => 'controller',
	'helper'                => 'helper',
	'library'               => 'library',
	'driver'                => 'driver',
	'model'                 => 'model',
	'view'                  => 'view',
);
