<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>Kohana User Guide</title>

<link type="text/css" rel="stylesheet" href="<?php echo url::base(TRUE) ?>user_guide/css/layout.css" />

<script type="text/javascript" src="<?php echo url::base(TRUE) ?>user_guide/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo url::base(TRUE) ?>user_guide/js/plugins.js"></script>
<script type="text/javascript" src="<?php echo url::base(TRUE) ?>user_guide/js/effects.js"></script>

</head>
<body>
<div id="container">

<div id="menu">
<?php echo $menu ?>
</div>

<!-- @start Body -->
<div id="body">
<?php echo $content ?>
</div>
<!-- @end Body -->

<!-- @start Footer -->
<div id="footer">
<p id="copyright">copyright (c) <?php echo date('Y') ?> Kohana Team :: All rights reserved :: Rendered in {execution_time} seconds</p>
</div>
<!-- @end Footer -->

</div>
</body>
</html>