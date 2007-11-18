<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	// Class errors
	'error_format'  => 'Votre message d\'erreur doit contenir la chaîne {message}.',
	'invalid_rule'  => 'La règle de validation %s est invalide',

	// General errors
	'unknown_error' => 'Erreur inconnue lors de la validation du champ %s.',
	'required'      => 'Le champ %s est requis.',
	'min_length'    => 'Le champ %s doit contenir au minimum %d charactères.',
	'max_length'    => 'Le champ %s ne peut contenir plus de %d charactères.',
	'exact_length'  => 'Le champ %s doit contenir exactement %d charactères.',
	'in_array'      => 'Vous devez sélectionner une option du champ %s parmis la liste proposée.',
	'matches'       => 'Le champ %s doit correspondre au champ %s.',
	'valid_url'     => 'Le champ %s doit contenir une URL valide et commencant par %s://',
	'valid_email'   => 'Le champ %s doit contenir une adresse email valide.',
	'valid_ip'      => 'Le champ %s doit contenir une adresse IP valide.',
	'valid_type'    => 'Le champ %s doit contenir seulement les charactères suivants: %s',
    'range'         => 'Le champ %s doit être situé dans la plage de valeurs spécifiée.',
	'regex'         => 'Le champ %s ne correspond pas aux valeurs acceptées.',
    'depends_on'    => 'Le champ %s est dépendant du champ %s.',

	// Upload errors
	'user_aborted'  => 'L\'envoi du fichier %s sur le serveur a échoué.',
	'invalid_type'  => 'Le type du fichier %s n\'est pas autorisé.',
	'max_size'      => 'Le fichier %s que vous tentez d\'envoyer est trop lourd. La taille maximale allouée est de %s',
	'max_width'     => 'Le fichier image %s doit avoir une taille maximale de %spx de large.',
	'max_height'    => 'Le fichier image %s doit avoir une taille maximale de %spx de haut.',
);
