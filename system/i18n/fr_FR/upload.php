<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'userfile_not_set'   => 'Impossible de trouver la variable de type POST nommée %s.',
	'file_exceeds_limit' => 'Le fichier envoyé dépasse la taille maximale autorisée par votre fichier de configuration PHP.',
	'file_partial'       => 'Le fichier n\'a été que partiellement envoyé.',
	'no_file_selected'   => 'Vous n\'avez pas sélectionné de fichier à envoyer.',
	'invalid_filetype'   => 'Le type de fichier que vous tentez d\'envoyer n\'est pas autorisé.',
	'invalid_filesize'   => 'Le fichier que vous tentez d\'envoyer dépasse la taille limite autorisée (%s)',
	'invalid_dimensions' => 'L\'image que vous tentez d\'envoyer dépasse les valeurs maximales autorisées pour la hauteur ou la largeur (%s)',
	'destination_error'  => 'Une erreur est survenue lors du déplacement du fichier envoyé vers sa destination finale.',
	'no_filepath'        => 'Le chemin de destination semble invalide.',
	'no_file_types'      => 'Vous n\'avez pas spécifié les types de fichiers autorisés.',
	'bad_filename'       => 'Un fichier du même nom que celui que vous avez envoyé existe déjà sur le serveur.',
	'not_writable'       => 'Le répertoire de destination %s ne semble pas être accessible en écriture.',
	'error_on_file'      => 'Erreur lors de l\'envoi du fichier %s:',
	// Error code responses
	'set_allowed'        => 'Pour des raisons de sécurité, vous devez définir les types de fichiers autorisés à l\'envoi.',
	'max_file_size'      => 'Pour des raisons de sécurité, vous ne devriez pas utiliser MAX_FILE_SIZE pour contrôler la taille des fichiers envoyés.',
	'no_tmp_dir'         => 'Impossible de trouver un répertoire temporaire accessible en écriture.',
	'tmp_unwritable'     => 'Impossible d\'écrire dans le répertoire %s.'
);
