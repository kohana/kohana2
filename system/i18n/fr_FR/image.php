<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'getimagesize_missing'    => 'La librairie d\'image requiert la function PHP <tt>getimagesize</tt>. Celle-ci n\'est pas supporté par votre installation.',
	'driver_not_supported'    => 'Le pilote d\'image %s n\'existe pas.',
	'unsupported_method'      => 'Le pilote configuré ne supporte pas la transformation d\'image %s.',
	'file_not_found'          => 'L\'image spécifié %s n\'a pas été trouvé. Merci de vérifier que l\'image existe bien avec la fonction <tt>file_exists</tt> avant sa manipulation.',
	'type_not_allowed'        => 'L\'image spécifié %s n\'est pas d\'un type autorisé.',
	'invalid_width'           => 'La largeur que vous avez spécifié, %s, est invalide.',
	'invalid_height'          => 'La hauteur que vous avez spécifié, %s, est invalide.',
	'invalid_dimensions'      => 'Les dimensions spécifiés pour %s ne sont pas valides.',
	'invalid_master'          => 'La dimension principale (master dim) n\'est pas valide.',
	'invalid_flip'            => 'La direction de rotation spécifié n\'est pas valide.',

	// ImageMagick specific messages
	'imagemagick' => array
	(
		'not_found' => 'Le répertoire ImageMagick spécifié ne contient pas le programme requis %s.',
	),


	// GD specific messages
	'gd' => array
	(
		'requires_v2' => 'La librairie d\'image requiert GD2. Veuillez consulter http://php.net/gd_info pour de plus amples informations.',
	),

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
