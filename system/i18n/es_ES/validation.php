<?php defined('SYSPATH') or die('No direct script access.'); 

$lang = array
(
    // Class errors
    'error_format'  => 'La plantilla de mensaje de error, debe contener la expresión {message} .',
    'invalid_rule'  => 'La regla de validación usada es invalida: %s',
    // General errors
    'unknown_error' => 'Error de validación desconocido al comprobar el campo %s.',
    'required'      => 'El campo %s es obligatorio.',
    'min_length'    => 'El campo %s debe tener un mínimo de %d caracteres.',
    'max_length'    => 'El campo %s debe tener un máximo de %d caracteres.',
    'exact_length'  => 'El campo %s debe tener exactamente %d caracteres.',
    'in_array'      => 'El campo %s debe ser seleccionado de las opciones listadas.',
    'matches'       => 'El campo %s debe conincidir con el campo %s.',
    'valid_url'     => 'El campo %s debe contener una url valida, empezando con %s://.',
    'valid_email'   => 'El campo %s debe contener una dirección de email valida.',
    'valid_ip'      => 'El campo %s debe contener una dirección IP valida.',
    'valid_type'    => 'El campo %s debe contener unicamente %s.',
    'range'         => 'El campo %s debe estar entre los rangos especificados.',
    'regex'         => 'El campo %s no coincide con los datos aceptados.',
    'depends_on'    => 'El campo %s depende del campo %s.',
    // Upload errors
    'user_aborted'  => 'El envio del archivo %s fue abortado antes de completarse.',
    'invalid_type'  => 'El archivo %s no es un tipo de archivo permitido.',
    'max_size'      => 'El archivo %s que estabas enviando es muy grande. El tamaño maximo es %s.',
    'max_width'     => 'El archivo %s debe tener como ancho maximo %s, y tiene %spx.',
    'max_height'    => 'El archivo %s debe tener como alto maximo %s, y tiene %spx.',
    // Field types                                                                                                                                                     
    'alpha'         => 'caracteres del alfabeto',
    'alpha_dash'    => 'caracteres del alfabeto, guiones y subrayado',
    'digit'         => 'digitos',
    'numeric'       => 'caracteres numéricos'
);