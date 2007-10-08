<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<title>Kohana: Swift, Secure, and Small PHP5 Framework</title>

<?php

echo html::stylesheet(array
(
	'media/css/reset',
	'media/css/layout'
))

?>

</head>
<body>
<div id="container">


<!-- Start Developer Menu -->
<div id="developer">
<span>Developers:</span>
<ul>
<li><?php echo html::anchor('http://kohanaphp.com/trac', 'Trac') ?></li>
<li><?php echo html::anchor('http://kohanaphp.com/trac/timeline', 'Timeline') ?></li>
<li><?php echo html::anchor('http://kohanaphp.com/trac/browser/trunk', 'Browse Source') ?></li>
<li><?php echo html::anchor('http://kohanaphp.com/trac/report/1', 'Tickets') ?></li>
<li><?php echo html::anchor('developer/join', 'Join') ?></li>
</ul>
</div>
<!-- End Developer Menu -->

<!-- Start Header -->
<div id="header" class="clearfix">

<!-- Start Logo -->
<h1 id="logo">Kohana</h1>
<!-- End Logo -->

<!-- Start Main Menu -->
<div id="menu" class="clearfix">
<ul>
<?php foreach($menu as $link => $title): ?>
<li<?php if ($link == $page): ?> class="active"<?php endif; ?>><?php echo html::anchor($link, $title) ?></li>
<?php endforeach; ?>
</ul>
</div>
<!-- End Main Menu -->

</div>
<!-- End Header -->

<!-- Start Body -->
<div id="body">
<?php echo $content ?>

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

<div id="footer" class="clearfix"><strong>&copy;2007 Kohana Team. All rights reserved.</strong> Powered by Kohana v{kohana_version}. Rendered in {execution_time} seconds.</div>

</div>
</body>
</html>