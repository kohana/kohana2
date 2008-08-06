<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $title ?></title>

<style type="text/css">
html { background: #83c018; }
body { width: 700px; margin: 2em auto; background: transparent url('<?php echo url::base(FALSE).'kohana.png' ?>') center 0 no-repeat; font-size: 76%; font-family: Arial, Verdana, sans-serif; color: #111; line-height: 1.5; }
div, h2, a, p, code, pre, ul { font-family: inherit; color: inherit; padding: 0; margin: 0; text-align: baseline; text-decoration: none; }
h2 { padding: 200px 0 0; }
p { padding: 0 0 0.6em; }
a { text-decoration: underline; }
ul { list-style: none; padding: 1em 0; }
ul li { display: inline; padding-right: 1em; }
ul li:before { content: '» '; }
pre { margin: 1em 0; padding: 1em; background: #fff; border: solid 0.6em #ddd; white-space: pre-wrap; }
pre,
code { font-family: fixed; }
.box { padding: 1em 1em 2em; background: #eee; }
.intro { padding: 1em 0; font-size: 1.2em; }
.copyright { margin-top: 1em; text-align: center; font-size: 0.8em; text-transform: uppercase; color: #44640b; }
</style>

</head>
<body>

<h2><?php echo $title ?></h2>
<?php echo $content ?>

<p class="copyright">Rendered in {execution_time} seconds, using {memory_usage} of memory<br/>Copyright &copy;2007-2008 Kohana Team</p>

</body>
</html>