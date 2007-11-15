<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	// Class errors
	'error_format'  => 'Your error message string must contain the string {message} .',
	'invalid_rule'  => 'Invalid validation rule used: %s',

	// General errors
	'unknown_error' => 'Unknown validation error while validating the %s field.',
	'required'      => 'The %s field is required.',
	'min_length'    => 'The %s field must be at least %d characters long.',
	'max_length'    => 'The %s field must be %d characters or less.',
	'exact_length'  => 'The %s field must be exactly %d characters.',
	'in_array'      => 'The %s field must be selected from the options listed.',
	'matches'       => 'The %s field must match the %s field.',
	'valid_url'     => 'The %s field must contain a valid URL, starting with %s://.',
	'valid_email'   => 'The %s field must contain a valid email address.',
	'valid_ip'      => 'The %s field must contain a valid IP address.',
	'valid_type'    => 'The %s field must only contain %s characters.',
	'range'         => 'The %s field must be between specified ranges.',
	'regex'         => 'The %s field does not match accepted input.',
	'depend_on'     => 'The %s field is depend on the %s field.',

	// Upload errors
	'user_aborted'  => 'The %s file was aborted during upload.',
	'invalid_type'  => 'The %s file is not an allowed file type.',
	'max_size'      => 'The %s file you uploaded was too large. The maximum size allowed is %s.',
	'max_width'     => 'The %s file has a maximum allowed width of %s is %spx.',
	'max_height'    => 'The %s file has a maximum allowed image height of %s is %spx.',
);
