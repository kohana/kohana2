<?php
// Create link to extended class only if it's a Kohana class
if ($extends):

	$extends_class = $extends;
	$classes = Kohana_Kodoc::list_classes();

	if (substr($extends_class, -5) == '_Core'):

		$extends_class = substr($extends_class, 0, -5);

	endif;

	if (isset($classes[$extends_class])):

		$extends_class = html::anchor('docs/api/class/'.$extends, $extends);

	endif;

endif;

// Re-create class declaration
$declaration = implode('', array
(
	$final      ? html::anchor('http://www.php.net/manual/language.oop5.final.php', 'final').' ' : '',
	$abstract   ? html::anchor('http://www.php.net/manual/language.oop5.abstract.php', 'abstract').' ' : '',
	$interface  ? html::anchor('http://www.php.net/manual/language.oop5.interfaces.php', 'interface').' ': '',
	'class ',
	$name,
	$extends    ? ' extends '.$extends_class : '',
	$implements ? ' implements '.implode(', ', $implements) : '',
	$extension ? ' (transparently extended)' : ''
));

?>

<h1>Class: <?php echo $name ?></h1>
<p><?php echo $declaration ?><br /> in <?php echo $file ?></p>

<?php

if ( ! empty($comment)):

	?>
	<div class="about"><?php echo Markdown(htmlentities($comment)) ?></div>
	<?php

endif;

if ( ! empty($constants)):

	?>
	<h2>Constants</h2>
	<ul>
	<?php

	foreach ($constants as $constant => $value):

		echo '<li>'.$constant.' = '.$value.'</li>';

	endforeach;

	?>
	</ul>
	<?php


endif;

if ( ! empty($properties)):

	?>
	<h2>Properties</h2>
	<ul>
	<?php

	$visibility = '';
	foreach ($properties as $property):

		if ($property['visibility'] != $visibility):

			$visibility = $property['visibility'];
			echo '<li><h3>'.ucfirst($visibility).'</h3></li>';

		endif;

		echo '<li>';
		echo empty($property['type']) ? '' : $property['type'].' - ';
		echo '$'.$property['name'];
		echo empty($property['comment']) ? '' : ' - '.htmlentities($property['comment']);
		echo '</li>';

	endforeach;

	?>
	</ul>
	<?php


endif;

if ( ! empty($methods)):

	?>
	<div class="methods">
		<?php
		// Sort by visibility
		$sorted_methods = array();
		foreach ($methods as $method):

			$type = $method['static'] ? 'Static ' : '';
			$type .= ucfirst($method['visibility']);
			$sorted_methods[$type][] = $method;

		endforeach;

		foreach ($sorted_methods as $visibility => $methods):

			echo '<h2>'.$visibility.' Methods</h2>';

			foreach ($methods as $method):

				echo new View('kohana_docs/api/method', $method);

			endforeach;

		endforeach;
		?>
	</div>
	<?php

endif;
?>
