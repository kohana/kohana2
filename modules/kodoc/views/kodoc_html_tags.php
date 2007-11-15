<?php

$h = empty($h) ? '4' : $h;

if ( ! empty($title)):
?>
<h<?php echo ($h-1) ?>><?php echo $title ?></h<?php echo ($h-1) ?>>
<?php
endif;

foreach ($html as $heading => $tags):
	if (strtolower($heading) != 'class' AND strtolower($heading) != 'method'):
?>
<h<?php echo $h ?> id="<?php echo strtolower($heading) ?>"><?php echo $heading ?></h<?php echo $h ?>>
<?php
	endif;
	foreach ($tags as $tag => $val):
		switch($tag):
			// Paragraphs
			case 'p':
				$val = implode('</p>'."\n".'<p>', $val);

?>
<p><?php echo $val ?></p>
<?php
			break;
			// Definition lists
			case 'dl':
?>
<dl>
<?php foreach ($val as $dt => $dd): ?>
<dt><?php echo $dt ?></dt>
<dd><?php echo $dd ?></dd>
<?php endforeach; ?>
</dl>
<?php
			break;
			// Lists
			case 'ul': case 'ol':
?>
<<?php echo $tag ?>>
<?php foreach($val as $item): ?>
<li><?php echo $item ?></li>
<?php endforeach; ?>
</<?php echo $tag ?>>
<?php
			break;
		endswitch;
	endforeach;
endforeach;