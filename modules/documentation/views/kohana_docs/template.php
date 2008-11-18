<?php

$lang = substr(Kohana_Config::get('locale.language.0'), 0, 2);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//<?php echo strtoupper($lang) ?>" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $title ?></title>

<?php

echo html::stylesheet('docs/css/styles', NULL, TRUE)

?>

</head>
<body id="<?php echo $page_id ?>">

<div id="header">
	<h1><?php echo $title ?></h1>
</div>

<div id="container" class="clearfix">

	<div id="breadcrumbs">
		<a href="<?php echo url::site('docs') ?>">Kohana Documentation</a>
		<?php

		$compiled_breadcrumbs = array();

		foreach ($breadcrumbs as $url => $title):

			$compiled_breadcrumbs[] = html::anchor($url, $title);

		endforeach;

		if ( ! empty($compiled_breadcrumbs)):

			echo ' &gt; '.implode(' &gt; ', $compiled_breadcrumbs);

		endif;

		?>
	</div>

	<div id="menu">
		<?php echo $menu ?>
	</div>

	<div id="body">
		<a id="top"></a>
		<?php echo $content ?>
	</div>

	<div id="sidebar">
		<?php echo $sidebar ?>
	</div>

</div>

<div id="footer">
	<?php echo sprintf(Kohana::lang('kohana_docs.copyright'), date('Y')) ?>
</div>

</body>
</html>
