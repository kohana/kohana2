<?php defined('SYSPATH') or die('No direct access allowed.');

$lang = array
(
	'there_can_be_only_one' => 'Може да има само една инстанца на Kohana при еден циклус.',
	'uncaught_exception'    => 'Uncaught %s: %s во датотека %s, линија %s',
	'invalid_method'        => 'Повикана е погрешна метода <tt>%s</tt> во <tt>%s</tt>.',
	'cannot_write_log'      => 'Твојот log.directory config елемент не поинтира во директориум во кој може да се пишува.',
	'resource_not_found'    => 'Побараниот %s, <tt>%s</tt>, не е пронајден.',
	'invalid_filetype'      => 'Побараниот тип на датотека, <tt>.%s</tt>, не е дозволен во view конфигурационата датотека.',
	'no_default_route'      => 'Подесете ја default рутата во <tt>config/routes.php</tt>.',
	'no_controller'         => 'Kohana не пронајде контролер за да го процесира ова барање: %s',
	'page_not_found'        => 'Страната која ја побаравте, <tt>%s</tt>, не е пронајдена.',
	'stats_footer'          => 'Вчитано за {execution_time} секунди, употребено {memory_usage} меморија. Креирано со Kohana v{kohana_version}.',
	'error_message'         => 'Грешка во <strong>линија %s</strong> во <strong>%s</strong>.',
	'stack_trace'           => 'Stack Trace',
	'generic_error'         => 'Барањето Не Може Да Биде Извршено',
	'errors_disabled'       => 'Можете да отидете на <a href="%s">home page</a> или да <a href="%s">пробате повторно</a>.',

	// Resource names
	'controller'            => 'controller',
	'helper'                => 'helper',
	'library'               => 'library',
	'driver'                => 'driver',
	'model'                 => 'model',
	'view'                  => 'view',
);