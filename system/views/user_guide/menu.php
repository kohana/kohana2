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
		'Configuration',
		'Controllers',
		'Models',
		'Views'
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

foreach($menus as $section => $menu):

?>
<li><?= url::anchor(strtolower('user_guide/'.$section), $section) ?><ul>
<?php

	foreach($menu as $link):

?>
<li><?= url::anchor(strtolower('user_guide/'.$section.'/'.$link), $link) ?></li>
<?php

	endforeach;

?>
</ul></li>
<?php

endforeach;

?>
</ul>