<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo $code ?></title>
	<style type="text/css">
	body { font-size: 90%; font-family: sans-serif; line-height: 160%; background: #eee; }
	#framework_error { text-align: center; background: #fff; }
	#framework_error h1 { padding: 0.2em 1em; margin: 0; font-size: 0.9em; font-weight: normal; text-transform: uppercase; background: #cff292; color: #911; }
	#framework_error p { padding: 1em; margin: 0; }
	</style>
</head>
<body>
	<div id="framework_error" style="width:24em;margin:50px auto;">
		<h1><?php echo html::specialchars($code) ?></h1>
		<p style="text-align:center"><?php echo $error ?></p>
	</div>
</body>
</html>