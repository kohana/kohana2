<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'driver_not_supported'    => 'Der %s-Bildtreiber existiert nicht.',
	
	// CI's Image_lib stuff below
	'source_image_required'   => 'Sie müssen das Quellbild in Ihren Einstellungen angeben.',
	'gd_required'             => 'Die GD Image Library wird für diese Funktion benötigt.',
	'gd_required_for_props'   => 'Ihr Server muss die GD Image Library bereitstellen, um Bildeigenschaften auslesen zu können.',
	'unsupported_imagecreate' => 'Ihr Server muss die GD Image Library bereitstellen, um ein Bild dieser Art bearbeiten zu können.',
	'gif_not_supported'       => 'GIF-Bilder werden oft aus lizenzrechtlichen Gründen nicht unterstützt. Sie müssen stattdessen JPEG- oder PNG-Bilder benutzen.',
	'jpg_not_supported'       => 'JPG-Bilder werden nicht unterstützt',
	'png_not_supported'       => 'PNG-Bilder werden nicht unterstützt',
	'jpg_or_png_required'     => 'Das eingestellte Protokoll, um Bilder in der Größe zu verändern, funktioniert nur mit Bildern der Art JPEG oder PNG.',
	'copy_error'              => 'Beim Ersetzen der datei ist ein Fehler aufgetretten. Stellen Sie bitte sicher, dass das Verzeichnis beschreibbar ist.',
	'rotate_unsupported'      => 'Bildrotation scheint von Ihrem server nicht unterstützt zu werden.',
	'libpath_invalid'         => 'Der Pfad zu Ihrem Bild ist nicht korrekt. Korrigieren Sie bitte den Pfad in den Bildeinstellungen.',
	'image_process_failed'    => 'Die Bearbeitung des Bildes ist fehlgeschlagen. Stellen Sie bitte sicher, dass Ihr Server das eingestellte Protokoll unterstützt und der Pfad zur Bildbibliothek korrekt eingestellt ist.',
	'rotation_angle_required' => 'Der Drehwinkel muss angegeben werden um das Bild drehen zu können.',
	'writing_failed_gif'      => 'GIF-Bild ',
	'invalid_path'            => 'Der Pfad zum Bild ist nicht korrekt.',
	'copy_failed'             => 'Der Kopiervorgang ist fehlgeschlagen.',
	'missing_font'            => 'Konnte die zu benutzende Schriftart nicht finden.'
);
