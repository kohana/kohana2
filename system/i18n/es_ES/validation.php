<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
	// Class errors
	'error_format'  => 'Tu cadena de mensaje de error, debe contener la cadena {message} .',
	'invalid_rule'  => 'La regla de validación usada es invalida: %s',

	// General errors
	'unknown_error' => 'Error de validación desconocido mientras se validaba el campo %s.',
	'required'      => 'El campo %s es obligatorio.',
	'min_length'    => 'El campo %s debe tener al menos %d caracteres.',
	'max_length'    => 'El campo %s debe tener %d caracteres o menos.',
	'exact_length'  => 'El campo %s debe tener exactamente %d caracteres.',
	'in_array'      => 'El campo %s debe ser seleccionado de las opciones listadas.',
	'matches'       => 'El campo %s debe conincidir con el campo %s.',
	'valid_url'     => 'El campo %s debe contener una url válida, empezando con %s://.',
	'valid_email'   => 'El campo %s debe contener una dirección de email válida.',
	'valid_ip'      => 'El campo %s debe contener una dirección IP válida.',
	'valid_type'    => 'El campo %s debe contener unicamente %s caracteres.',
	'range'         => 'El campo %s debe estar entre los rangos especificados.',
	'regex'         => 'El campo %s no coincide con los datos aceptados.',
	'depends_on'    => 'El campo %s depende del campo %s.',

	// Upload errors
	'user_aborted'  => 'El envio del archivo %s fue abortado antes de completarse.',
	'invalid_type'  => 'El archivo %s no es un tipo de archivo permitido.',
	'max_size'      => 'El archivo %s que estabas subiendo es muy grande. El tamaño maximo es %s.',
	'max_width'     => 'El archivo %s debe tener como ancho maximo %s, y tiene %spx.',
	'max_height'    => 'El archivo %s debe tener como alto maximo %s, y tiene %spx.',

        // Field types                                                                                                                                                     
        'alpha'         => 'caracteres del alfabeto',
        'alpha_dash'    => 'caracteres del alfabeto, guiones y subrayado',
        'digit'         => 'digitos',
        'numeric'       => 'numéricos'
);
