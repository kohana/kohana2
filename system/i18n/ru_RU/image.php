<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'getimagesize_missing'    => 'Библиотека Image нуждается в функции <tt>getimagesize</tt>, недоступной в вашей инсталляции PHP.',
	'driver_not_supported'    => 'Запрошенный драйвер библиотеки Image, %s, не найден.',
	'unsupported_method'      => 'Указанный драйвер не поддерживает операцию "%s".',
	'file_not_found'          => 'Заданное изображение, %s, не найдено. Удостоверьтесь в наличии изображения функцией <tt>file_exists</tt> перед его обработкой.',
	'type_not_allowed'        => 'Заданное изображение, %s, не является разрешённым типом изображений.',
	'invalid_width'           => 'Заданная ширина, %s, некорректна.',
	'invalid_height'          => 'Заданная высота, %s, некорректна.',
	'invalid_dimensions'      => 'Заданный размер для %s некорректен.',
	'invalid_master'          => 'Заданная основная сторона некорректна.',
	'invalid_flip'            => 'Направление разворота некорректно.',

	// ImageMagick specific messages
	'imagemagick' => array
	(
		'not_found' => 'Директория ImageMagick не содержит запрошенную программу, %s.',
	),

	// GD specific messages
	'gd' => array
	(
		'requires_v2' => 'Библиотека Image нуждается в расширении GD2. Подробности на http://php.net/gd_info .',
	),
);
