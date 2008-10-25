<?php

// Recreate method declaration
$declaration = implode('', array
(
	$abstract   ? html::anchor('http://www.php.net/manual/language.oop5.abstract.php', 'abstract').' ' : '',
	$final      ? html::anchor('http://www.php.net/manual/language.oop5.final.php', 'final').' ' : '',
	html::anchor('http://www.php.net/manual/language.oop5.visibility.php', $visibility).' ',
	$static     ? html::anchor('http://www.php.net/manual/language.oop5.static.php', 'static').' ' : '',
	'function ',
	$name
));

$declaration = $name.'(';

$params = array();
foreach ($parameters as $i => $param):

	$param_dec = '$'.$param['name'];

	if (array_key_exists('default', $param))
	{
		$param_dec .= ' = '.$param['default'];
	}
	$params[] = $param_dec;

endforeach;

$declaration .= implode(', ', $params);
$declaration .= ')';
?>
<div class="method">
<h2 id="<?php echo $name ?>"><?php echo $declaration ?></h2>
<div class="details">
<?php echo Markdown(htmlentities($comment)); ?>
<dl>
<?php if ( ! empty($parameters)): ?>

	<dt>Parameters:</dt>
	<dd>
		<table>
		<?php

		foreach ($parameters as $parameter):

			echo '<tr><td>';
			echo isset($parameter['type']) ? Kohana_Kodoc::humanize_type($parameter['type']) : '';
			echo '</td><td>';
			echo '$', $parameter['name'];
			echo '</td><td>';
			echo isset($parameter['comment']) ? htmlentities($parameter['comment']) : '';
			echo '</td></tr>';

		endforeach;

		?>
		</table>
	</dd>
	<?php

endif;

if ( ! empty($return)):

	?>
	<dt>Return:</dt>
	<dd>
	<?php
	echo $return['type'];
	echo empty($return['comment']) ? '' : ' - '.htmlentities($return['comment']);
	?>
	</dd>
	<?php

endif;

if ( ! empty($throws)):

	?>
	<dt>Exceptions:</dt>
	<?php
	foreach ($throws as $exception):

		?>
		<dd>
		<?php
		echo $exception['type'];
		echo empty($exception['comment']) ? '' : ' - '.htmlentities($exception['comment']);
		?>
		</dd>
		<?php

	endforeach;

endif;

foreach ($tags as $tag => $vals):

	?>
	<dt><?php echo ucfirst($tag) ?>:</dt>
	<?php
	foreach ($vals as $val):

		?>
		<dd>
		<?php echo htmlentities($val) ?>
		</dd>
		<?php

	endforeach;

endforeach;

?>

	</dl>
</div>

<?php
if ( ! empty($example)):

	?>
	<div class="example">
		<strong>Example:</strong>
		<code class="php"><?php	echo htmlentities($example) ?></code>
	</div>
	<?php

endif;
?>

</div>
