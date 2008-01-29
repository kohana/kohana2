<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
	'getimagesize_missing'    => 'La librería &8220;Image&8221; requiere la función PHP <tt>getimagesize</tt>, que no parece estar disponible en tu instalación.',
	'driver_not_supported'    => 'No se encuentra el driver, %s, requerido por la librería Image.',
	'unsupported_method'      => 'El driver que has elegido en la configuración no soporta el tipo de transformación %s.',
	'file_not_found'          => 'La imagen especificada, %s no se ha encontrado. Por favor, verifica que existe utilizando <tt>file_exists</tt> antes de manipularla.',
	'type_not_allowed'        => 'El tipo de imagen especificado, %s, no es un tipo de imagen permitido.', 

	// ImageMagick specific messages
	'imagemagick' => array
	(
	'not_found' => 'El directorio de ImageMagick especificado, no contiene el programa requrido, %s.', 
	),

	// GD specific messages
	'gd' => array
	(
	'requires_v2' => 'La linrería &8220;Image&8221; requiere GD2. Por favor, lee http://php.net/gd_info para más información.',
	),
);
