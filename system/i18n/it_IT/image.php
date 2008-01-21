<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'driver_not_supported'    => 'Il driver per immagini %s non esiste.',

	// CI's Image_lib stuff below
	'source_image_required'   => 'Specificare un\'immagine sorgente nelle preferenze.',
	'gd_required'             => 'La libreria GD è necessaria per questa funzionalità.',
	'gd_required_for_props'   => 'Per determinare le propietà delle immagini il server deve supportare la libreria GD',
	'unsupported_imagecreate' => 'Il server non supporta le funzioni GD necessarie a trattare questo tipo di immagine.',
	'gif_not_supported'       => 'Le restrizioni dovute alla licenza rendono il formato GIF spesso inutilizzabile. È preferibile l\'uso dei formati JPG o PNG.',
	'jpg_not_supported'       => 'Il formato JPG non è supportato.',
	'png_not_supported'       => 'Il formato PNG non è supportato',
	'jpg_or_png_required'     => 'Il protocollo di ridimensionamento specificato nelle preferenze funziona solo per i formati JPEG e PNG.',
	'copy_error'              => 'Si è verificato un errore durante la sostituzione del file. Accertarsi che la cartella abbia i permessi in scrittura.',
	'rotate_unsupported'      => 'Sembra che il server non supporti la rotazione delle immagini.',
	'libpath_invalid'         => 'Il percorso alla liblreria delle immagini non è corretto. Si prega di correggere il percorso nelle preferenze.',
	'image_process_failed'    => 'Image processing fallito. Verificare che il server supporti il protocollo scelto e che il percorso alla libreria delle immagini sia corretto.',
	'rotation_angle_required' => 'Un angolo di rotazione è necessario per ruotare l\'immagine.',
	'writing_failed_gif'      => 'Immagine GIF ',
	'invalid_path'            => 'Il percorso all\'immagine non è corretto',
	'copy_failed'             => 'La procedura di copia dell\'immagine è fallita.',
	'missing_font'            => 'Impossibile trovare un font utilizzabile.'
);
