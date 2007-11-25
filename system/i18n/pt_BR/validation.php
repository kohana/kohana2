<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	// Class errors
	'error_format'  => 'Your error message string must contain the string {message} .',
	'invalid_rule'  => 'Regra de validacão inválida: %s',

	// General errors
	'unknown_error' => 'Unknown validation error while validating the %s field.',
	'required'      => 'O campo %s é obrigatório.',
	'min_length'    => 'O campo %s deve conter pelo menos %d caractere(s) de tamanho.',
	'max_length'    => 'O campo %s deve ter pelo menos %d caractere(s).',
	'exact_length'  => 'O campo %s deve conter exatamente %d caractere(s).',
	'in_array'      => 'O campo %s deve conter must be selected from the options listed.',
	'matches'       => 'O campo %s deve ser igual ao campo %s.',
	'valid_url'     => 'O campo %s deve conter uma URL válida, iniciando-se com %s://.',
	'valid_email'   => 'O campo %s deve conter um endereco de email válido.',
	'valid_ip'      => 'O campo %s deve conter um endereco de IP válido.',
	'valid_type'    => 'O campo %s deve conter apenas %s caractere(s).',

	// Upload errors
	'user_aborted'  => 'O arquivo %s foi abortado durante o upload.',
	'invalid_type'  => 'O arquivo %s is not an allowed file type.',
	'max_size'      => 'The %s file you uploaded was too large. The maximum size allowed is %s.',
	'max_width'     => 'The %s file has a maximum allowed width of %s is %spx.',
	'max_height'    => 'The %s file has a maximum allowed image height of %s is %spx.',
);
