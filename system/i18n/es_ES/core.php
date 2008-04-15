<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
	'there_can_be_only_one' => 'Solo puede haber una instancia de Kohana por cada página.',
	'uncaught_exception'    => '%s no capturada: %s en el archivo %s, linea %s',
	'invalid_method'        => 'Método inválido <tt>%s</tt> llamado en <tt>%s</tt>.',
	'cannot_write_log'      => 'Tu configuración del &8220;log.directory&8221; no apunta a un directorio con permiso de escritura.',
	'resource_not_found'    => 'El fichero de <tt>%s</tt> con nombre %s, no pudo ser encontrado.',
	'invalid_filetype'      => 'El tipo de fichero solicitado, <tt>.%s</tt>, no esta permitido en la configuración de tus vistas.',
	'view_set_filename'     => 'Tienes que definir el nombre de la vista antes de llamar al metodo render',
	'no_default_route'      => 'Por favor, especifica la ruta en <tt>config/routes.php</tt>.',
	'no_controller'         => 'Kohana no pudo determinar un controlador para procesar: %s',
	'page_not_found'        => 'La página que solicitase, <tt>%s</tt>, no se encuentra.',
	'stats_footer'          => 'Cargado en {execution_time} segundos, usando {memory_usage} de memoria. Generado con Kohana v{kohana_version}.',
	'error_file_line'       => 'Error en <strong>%s</strong> linea: <strong>%s</strong>.',
	'stack_trace'           => 'Stack Trace',
	'generic_error'         => 'Imposible completar la solicitud',
	'errors_disabled'       => 'Puedes volver a la <a href="%s">página de inico</a> o <a href="%s">volver a intentarlo</a>.', 

	// Resource names
	'controller'            => 'controlador',
	'helper'                => 'helper',
	'library'               => 'librería',
	'driver'                => 'driver',
	'model'                 => 'modelo',
	'view'                  => 'vista',
);
