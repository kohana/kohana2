<h1>Browse classes by folder</h1>
<?php
asort($classes);

$sorted_classes = array();
foreach ($classes as $class => $file):

	$file = pathinfo($file);
	$dir = Kohana_Kodoc::remove_docroot($file['dirname']);
	$sorted_classes[$dir][] = $class;

endforeach;

foreach ($sorted_classes as $dir => $classes):

	echo '<h2>'.$dir.'</h2>';
	foreach ($classes as $class):

		echo html::anchor('docs/api/class/' . $class, $class) . '<br />';

	endforeach;

endforeach;
