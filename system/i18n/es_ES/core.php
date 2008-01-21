<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
    'there_can_be_only_one' => 'Solo puede haber una instancia de Kohana por cada página.',
    'uncaught_exception'    => '%s no capturada: %s en el archivo %s, linea %s',
    'invalid_method'        => 'Método inválido <tt>%s</tt> llamado en <tt>%s</tt>.',
    'cannot_write_log'      => 'Tu configuración del &8220;log.directory&8221; no apunta a un directorio con permiso de escritura.',
    'resource_not_found'    => 'El fichero con nombre %s, <tt>%s</tt>, no pudo ser encontrado.',
    'invalid_filetype'      => 'El tipo de fichero solicitado, <tt>.%s</tt>, no esta permitido en la configuración de tus vistas.',
    'no_default_route'      => 'Por favor, especifica la ruta en <tt>config/routes.php</tt>.',
    'no_controller'         => 'Kohana no pudo determinar un controlador para procesar: %s',
    'page_not_found'        => 'La página que solicitase, <tt>%s</tt>, no se encuentra.',
    'stats_footer'          => 'Cargado en {execution_time} segundos, usando {memory_usage} de memoria. Generado con Kohana v{kohana_version}.',
    'error_message'         => 'Ocurrio un error en la linea <strong>%s</strong> de <strong>%s</strong>.',
    'stack_trace'           => 'Stack Trace',
    // Resource names
    'controller'            => 'controlador',
    'helper'                => 'helper',
    'library'               => 'librería',
    'driver'                => 'driver',
    'model'                 => 'modelo',
    'view'                  => 'vista'
);
