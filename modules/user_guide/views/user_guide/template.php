<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//<?php echo strtoupper(Config::item('core.locale')) ?>" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo Config::item('core.locale') ?>" lang="<?php echo Config::item('core.locale') ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo Kohana::lang('user_guide.title') ?></title>

<?php

echo html::stylesheet(array
(
	'user_guide/css/layout',
	'user_guide/css/prettify'
), TRUE)

?>

<?php

echo html::script(array
(
	'user_guide/js/jquery',
	'user_guide/js/plugins',
	'user_guide/js/prettify',
	'user_guide/js/effects'
), TRUE)

?>

</head>
<body>
<div id="container">

<!-- @start Menu -->
<div id="menu">
<ul>
<?php

foreach(Kohana::lang('user_guide.menu') as $category => $menu):

	$active = (strtolower($category) == $active_category) ? ' active' : '';

?>
<li class="first<?php echo $active ?>"><span><?php echo $category ?></span><ul>
<?php

	foreach($menu as $section):

		$active = (strtolower($section) == $active_section) ? 'lite' : '';

?>
<li class="<?php echo $active ?>"><?php echo html::anchor(strtolower('user_guide/'.$category.'/'.$section), $section) ?></li>
<?php

	endforeach;

?>
</ul></li>
<?php

endforeach;

?>
</ul>
</div>
<!-- @end Menu -->
<!-- @start Body -->
<div id="body">
<?php echo $content ?>
</div>
<!-- @end Body -->
<!-- @start Footer -->
<div id="footer"><p id="copyright"><?php echo sprintf(Kohana::lang('user_guide.copyright'), date('Y')) ?></p></div>
<!-- @end Footer -->

</div>
</body>
</html>