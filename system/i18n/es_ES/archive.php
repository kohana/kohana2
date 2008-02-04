<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
	'driver_not_supported' => 'No se encuentra el driver, %s, requerido por la librería Archive.',
	'driver_implements'    => 'El driver, %s, no implementa los metodos requeridos por la interface Archive_Driver.',
	'directory_unwritable' => 'El directorio en el que intentas guardar el archivo, %s, no tiene permiso de escritura. Cambia los permisos de escritura e intentalo de nuevo.',
	'filename_conflict'    => 'El archivo, %s, ya existe y no tiene permiso de escritura. Por favor, borra el archivo en conflicto e intentalo de nuevo.',
);