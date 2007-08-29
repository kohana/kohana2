<?php
/**
* Kohana User Guide Menu
* Topics in Categories (Eg, General, Libraries)
* Articles in Sections (Eg, Installation)
* Libraries and Helpers are in alphabetic order
* Other Categories are in logical order
*/
$menus = array
(
	'Kohana' => array
	(
		'Requirements',
		'Downloads',
		'Installation',
		'About',
		'Links'
	),
	'General' => array
	(
		'Definitions',
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
		'Cache',
		'Controller',
		'Database',
		'Encryption',
		'Input',
		'Loader',
		'Model',
		'Pagination',
		'Router',
		'Session',
		'URI',
		'View'
	),
	'Helpers' => array
	(
		'File',
		'Html',
		'Url'
	)
);
?>
<ul>
<?php

foreach($menus as $category => $menu):

	$active = (strtolower($category) == $active_category) ? ' active' : '';

?>
<li class="first<?= $active ?>"><span><?= $category ?></span><ul>
<?php

	foreach($menu as $section):

		$before = (strtolower($section) == $active_section) ? '<em>&laquo;</em> ' : '&laquo; ';

?>
<li><?= $before.html::anchor(strtolower('user_guide/'.$category.'/'.$section), $section) ?></li>
<?php

	endforeach;

?>
</ul></li>
<?php

endforeach;

?>
</ul>