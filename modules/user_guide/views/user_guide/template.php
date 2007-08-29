<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>Kohana User Guide</title>

<link type="text/css" rel="stylesheet" href="<?= url::base(TRUE) ?>user_guide/css/layout.css" />

<script type="text/javascript" src="<?= url::base(TRUE) ?>user_guide/js/jquery.js"></script>
<script type="text/javascript" src="<?= url::base(TRUE) ?>user_guide/js/plugins.js"></script>
<script type="text/javascript" src="<?= url::base(TRUE) ?>user_guide/js/effects.js"></script>

</head>
<body>
<div id="container">

<!-- @start Header -->
<div id="header">
<div id="menu">
<?= $menu ?>
</div>
</div>
<!-- @end Header -->

<!-- @start Body -->
<div id="body">
<?= $content ?>

<!-- @start Footer -->
<div id="footer">
<p id="copyright">copyright (c) 2007 Kohana Team :: All rights reserved :: Rendered in {execution_time} seconds</p>
</div>
<!-- @end Footer -->

</div>
<!-- @end Body -->

</div>
</body>
</html>