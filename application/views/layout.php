<?php

// HTML lang
$lang = substr(Kohana::config('locale.language.0'), 0, 2);

// Base URL
$base_url = url::base();

// Page suffix
$suffix = Kohana::config('core.url_suffix');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//<?php echo strtoupper($lang) ?>"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
<title><?php echo $title ?> &ndash; <?php echo Kohana::lang('layout.subtitle');?></title>
<meta name="keywords" content="Kohana, PHP 5, MVC, framework, strict, XHTML, UTF-8, utf8, database abstraction, mysql, CodeIgniter, events, sessions, secure, lighweight, extensible, easy to extend, easy to use, forums, api, OOP, object oriented" />
<meta name="description" content="<?php echo Kohana::lang('layout.description');?>" />
<?php

echo html::stylesheet(array
(
	'media/css/reset',
	'media/css/common',
	'media/css/web',
	'media/css/print',
), array
(
	'all',
	'all',
	'screen',
	'print'
));

/* Browser-specific stylesheets */
switch (Kohana::user_agent('browser'))
{
	case 'Safari':
	echo html::stylesheet('media/css/webkit');
	break;
	case 'Firefox':
	case 'Shiira':
	case 'Camino':
	echo html::stylesheet('media/css/mozilla');
	break;
}
?>
<?php
echo html::script(array
(
	'media/js/jquery',
	'media/js/plugins',
	'media/js/effects'
))
?>
<?php /* Kohana 2.2 Teaser */ if (time() < strtotime('2008-08-09')): ?>
<style type="text/css">
#logo { background-image: url(<?php echo url::base() ?>media/img/kohana080808.png); }
</style>
<?php endif ?>
</head>
<body>
<!-- Start Language Picker -->
<ul id="languages">
<?php foreach (Kohana::config('locale.tlds') as $tld => $i18n): ?>
<li><a class="<?php if ($tld === TLD) echo 'active' ?>" href="<?php echo preg_replace('/(?<=kohanaphp\.)[a-z.]+/', $tld, $base_url, 1), Router::$current_uri, $suffix, Router::$query_string ?>"><img alt="<?php echo $i18n ?>" src="<?php echo $base_url, 
'media/img/flags/', $i18n, '.png' ?>" width="16" height="11" /></a></li>
<?php endforeach ?>
</ul>
<!-- End Language Picker -->
<!-- Start Developer Menu -->
<div id="developer">
<span><?php echo Kohana::lang('layout.developers');?></span>
<ul>
<li><?php echo html::anchor('http://trac.kohanaphp.com/', Kohana::lang('layout.menu_trac')) ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/timeline', Kohana::lang('layout.menu_timeline')) ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/browser/trunk', Kohana::lang('layout.menu_source')) ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/report/1', Kohana::lang('layout.menu_tickets')) ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/wiki/BeADev', Kohana::lang('layout.menu_join')) ?></li>
<li><?php echo html::anchor('http://www.kohanajobs.com/', Kohana::lang('layout.menu_jobs')) ?></li>
</ul>
</div>
<!-- End Developer Menu -->
<!-- Start Header -->
<div id="header">
<!-- Start Logo -->
<h1 id="logo">Kohana</h1>
<!-- End Logo -->
</div>
<!-- End Header -->
<!-- Start Main Menu -->
<div id="menu" class="clearfix">
<ul>
<?php foreach($menu as $link => $title): ?>
<li<?php if (strpos(Router::$current_uri, $link) === 0): ?> class="active"<?php endif; ?>><?php echo html::anchor($link, $title) ?></li>
<?php endforeach; ?>
</ul>
</div>
<!-- End Main Menu -->
<!-- Start Body -->
<div id="body" class="clearfix">
<!-- Start Content -->
<div id="content-wrapper">
<div id="content">
<?php echo $content ?>
</div>
</div>
<!-- End Content -->
<!-- Start Sidebar -->
<div id="sidebar">
<!-- Start Download -->
<div id="download">
<?php echo html::anchor('download', Kohana::lang('layout.download_link')) ?>
</div>
<!-- End Download -->
<!-- Start Sidebar Body -->
<div id="sidebody">
<?php echo $sidebar ?>
</div>
<!-- End Sidebar Body -->
</div>
<!-- End Sidebar -->
</div>
<!-- End Body -->
<div id="footer">
<strong>&copy;2007–<?php echo date('Y') ?> <?php echo Kohana::lang('layout.copyright');?></strong>
<span class="stats"><?php echo Kohana::lang('layout.stats');?></span>
</div>
<!-- Stats -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("UA-4300382-1");
pageTracker._initData();
pageTracker._trackPageview();
</script>
</body>
</html>