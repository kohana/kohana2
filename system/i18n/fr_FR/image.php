<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'driver_not_supported'    => 'Le pilote d\'image %s n\'existe pas.',
	
	// CI's Image_lib stuff below
	'source_image_required'   => 'Vous devez spécifier une image source dans vos préférences.',
	'gd_required'             => 'La librairie GD est requise pour cette fonctionnalité.',
	'gd_required_for_props'   => 'Votre serveur doit supporter la librairie GD pour déterminer les propriétés de l\'image.',
	'unsupported_imagecreate' => 'Votre serveur ne dispose pas de la fonction GD nécessaire pour traiter ce type d\'image.',
	'gif_not_supported'       => 'Le format GIF est souvent inutilisable du fait de restrictions de licence. Il est préférable d\'utiliser le format JPG ou PNG.',
	'jpg_not_supported'       => 'Le format JPG n\'est pas supporté.',
	'png_not_supported'       => 'Le format PNG n\'est pas supporté.',
	'jpg_or_png_required'     => 'Le protocole de redimensionnement spécifié dans vos préférences ne fonctionne qu\'avec les formats d\'image JPG ou PNG.',
	'copy_error'              => 'Une erreur est survenue lors du remplacement du fichier. Veuillez vérifier les permissions d\'écriture de votre répertoire.',
	'rotate_unsupported'      => 'Votre serveur ne supporte apparemment pas la rotation d\'images.',
	'libpath_invalid'         => 'Le chemin d\'accès à votre librairie de traitement d\'image n\'est pas correct. Veuillez indiquer le chemin correct dans vos préférences.',
	'image_process_failed'    => 'Le traitement de l\'image a échoué. Veuillez vérifier que votre serveur supporte le protocole choisi et que le chemin d\'accès à votre librairie de traitement d\'image est correct.',
	'rotation_angle_required' => 'Un angle de rotation doit être indiqué pour effectuer cette transformation sur l\'image.',
	'writing_failed_gif'      => 'Image GIF ',
	'invalid_path'            => 'Le chemin d\'accès à l\'image est incorrect.',
	'copy_failed'             => 'Le processus de copie d\'image a échoué.',
	'missing_font'            => 'Impossible de trouver une police de caractères utilisable.'
);