<h1>Browse classes by type</h1>
<?php
uksort($classes, 'strnatcasecmp');

// Pre-sort types
$sorted_classes = array(
	'Controllers' => array(),
	'Libraries'   => array(),
	'Helpers'     => array(),
	'Models'      => array(),
	'Exceptions'  => array()
);

foreach ($classes as $class => $file):

	$first_letter = substr($class, 0, 1);
	if (substr($class, 0, 11) == 'Controller_')
	{
		$sorted_classes['Controllers'][] = $class;
	}
	else if (substr($class, 0, 6) == 'Model_')
	{
		$sorted_classes['Models'][] = $class;
	}
	else if (substr($class, -10) == '_Exception')
	{
		$sorted_classes['Exceptions'][] = $class;
	}
	else if ($first_letter >= 'A' && $first_letter <= 'Z')
	{
		$sorted_classes['Libraries'][] = $class;
	}
	else if ($first_letter >= 'a' && $first_letter <= 'z')
	{
		$sorted_classes['Helpers'][] = $class;
	}
	else
	{
		echo Kohana::debug($class);
	}

endforeach;

foreach ($sorted_classes as $type => $type_classes):

	echo '<h2>'.$type.'</h2>';
	foreach ($type_classes as $class):

		echo html::anchor('docs/api/class/' . $class, $class) . '<br />';

	endforeach;

endforeach;
