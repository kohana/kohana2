<?php

$menus = array
(
	'Kohana' => array
	(
		'Requirements',
		'Installation',
		'About',
		'Links'
	),
	'General' => array
	(
		'Bootstrapping',
		'Configuration',
		'Libraries',
		'Controllers',
		'Models',
		'Views',
		'Helpers'
	),
	'Libraries' => array
	(
		'Session',
		'Cache',
		'Encryption'
	)
);
?>
<ul>
<?php

foreach($menus as $category => $menu):

	$class = (strtolower($category) == $active_category) ? ' class="active"' : '';

?>
<li<?= $class ?>><?= $category ?><ul>
<?php

	foreach($menu as $section):

		$before = (strtolower($section) == $active_section) ? '<em>&laquo;</em> ' : '&laquo; ';

?>
<li><?= $before.url::anchor(strtolower('user_guide/'.$category.'/'.$section), $section) ?></li>
<?php

	endforeach;

?>
</ul></li>
<?php

endforeach;

?>
</ul>