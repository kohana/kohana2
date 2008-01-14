<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<title><?php echo $title ?> &ndash; Kohana: Swift, Secure, and Small PHP 5 Framework</title>

<meta name="keywords" content="Kohana, PHP 5, MVC, framework, strict, XHTML, UTF-8, utf8, database abstraction, mysql, CodeIgniter, events, sessions, secure, lighweight, extensible, easy to extend, easy to use, forums, api, OOP, object oriented" />
<meta name="description" content="Kohana is a PHP 5 framework that uses the Model View Controller architectural pattern. It aims to be secure, lightweight, and easy to use." />

<?php

echo html::stylesheet(array
(
	'media/css/reset',
	'media/css/common',
	'media/css/layout',
	'media/css/print',
), array
(
	'all',
	'all',
	'screen',
	'print'
))

?>

<?php

echo html::script(array
(
	'media/js/jquery',
	'media/js/plugins',
	'media/js/effects'
)) 

?>

</head>
<body>
<!-- Start Developer Menu -->
<div id="developer">
<span>Developers:</span>
<ul>
<li><?php echo html::anchor('http://trac.kohanaphp.com/', 'Trac') ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/timeline', 'Timeline') ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/browser/trunk', 'Browse Source') ?></li>
<li><?php echo html::anchor('http://trac.kohanaphp.com/report/1', 'Tickets') ?></li>
<li><?php echo html::anchor('developer/join', 'Join') ?></li>
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
<li<?php if ($link == $this->uri->segment(1)): ?> class="active"<?php endif; ?>><?php echo html::anchor($link, $title) ?></li>
<?php endforeach; ?>
</ul>
</div>
<!-- End Main Menu -->

<!-- Start Body -->
<div id="body" class="clearfix">

<!-- Start Content -->
<div id="content-wrapper"><div id="content">
<?php echo $content ?>
</div></div>
<!-- End Content -->

<!-- Start Sidebar -->
<div id="sidebar">

<!-- Start Download -->
<div id="download">
<?php echo html::anchor('download', '<strong>Download</strong> Latest Version') ?>
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

<div id="footer"><strong>&copy;2007 Kohana Team. All rights reserved.</strong> <span class="stats">Powered by Kohana v{kohana_version}. Rendered in {execution_time} seconds, using {memory_usage} of memory.</span></div>

</div>
</body>
</html>