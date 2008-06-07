<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'Наличие более, чем одного экземпляра Kohana, в пределах одного запроса страницы, невозможно.',
	'uncaught_exception'    => 'Не пойманное %s: %s в файле %s, на строке %s',
	'invalid_method'        => 'Вызов метода <tt>%s</tt> из файла <tt>%s</tt> невозможен.',
	'log_dir_unwritable'    => 'Параметр log.directory указывает на директорию без возможности записи файлов.',
	'resource_not_found'    => 'Запрошенный %s, <tt>%s</tt>, не найден.',
	'invalid_filetype'      => 'Запрошенный тип файла, <tt>.%s</tt>, не разрешён конфигурацией видов.',
	'no_default_route'      => 'Установите путь по умолчанию в файле <tt>config/routes.php</tt>.',
	'no_controller'         => 'Kohana не удалось найти контроллер для обработки этого запроса: %s',
	'page_not_found'        => 'Запрошенная страница, <tt>%s</tt>, не найдена.',
	'stats_footer'          => 'Загружено за {execution_time} секунд, используя {memory_usage} памяти. Сгенерировано в Kohana v{kohana_version}.',
	'error_file_line'       => '<tt>%s <strong>[%s]:</strong></tt>',
	'stack_trace'           => 'Стек вызовов',
	'generic_error'         => 'Ошибка при обработке запроса',
	'errors_disabled'       => 'Вы можете вернуться на <a href="%s">начальную страницу</a> или <a href="%s">попробовать ещё раз</a>.',

	// Resource names
	'controller'            => 'контроллер',
	'helper'                => 'помощник',
	'library'               => 'библиотека',
	'driver'                => 'драйвер',
	'model'                 => 'модель',
	'view'                  => 'вид',
);
