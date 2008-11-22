<h1>Browse classes by name</h1>
<?php
$letter = '';
foreach ($classes as $class => $file):

	$first_letter = substr($class, 0, 1);

	if (strcasecmp($first_letter, $letter) !== 0):

		echo '<h2>'.strtoupper($first_letter).'</h2>';
		$letter = $first_letter;

	endif;

	echo html::anchor('docs/api/class/' . $class, $class).'<br />';

endforeach;
