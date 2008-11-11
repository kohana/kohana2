<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title>Kohana Installation</title>

<style type="text/css">
body { width: 40em; margin: 0 auto; font-family: sans-serif; font-size: 90%; }

#tests table { border-collapse: collapse; width: 100%; }
	#tests table th,
	#tests table td { padding: 0.2em 0.4em; text-align: left; vertical-align: top; }
	#tests table th { width: 12em; font-weight: normal; font-size: 1.2em; }
	#tests table tr:nth-child(odd) { background: #eee; }
	#tests table tr.results { background: #efd; }
		#tests table tr.results th,
		#tests table tr.results td { padding: 0.6em 0.4em; }
	#tests table td.pass { color: #191; }
	#tests table td.fail { color: #911; }
</style>

</head>
<body>

<h1>Environment Tests</h1>

<p>The following tests have been run to determine if Kohana will work in your environment. If any of the tests have failed, consult the <a href="http://docs.kohanaphp.com/general/installation">documentation</a> for more information on how to correct the problem.</p>

<div id="tests">
<?php $failed = FALSE; ?>
<table cellspacing="0">
<tr>
<th>PHP Version</th>
<?php if (version_compare(PHP_VERSION, '5.2', '>=')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">Kohana requires PHP 5.2 or newer, this version is <?php echo PHP_VERSION ?>.</td>
<?php endif ?>
</tr>
<tr>
<th>System Files Exist</th>
<?php if (is_dir(SYSPATH) AND is_file(SYSPATH.'classes/kohana'.EXT)): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The <tt>system</tt> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>
<tr>
<th>Application Files Exist</th>
<?php if (is_dir(APPPATH) AND is_file(APPPATH.'config/config'.EXT)): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The <tt>application</tt> directory does not exist or does not contain required files.</td>
<?php endif ?>
</tr>
<tr>
<th>PCRE UTF-8</th>
<?php if (@preg_match('/^.$/u', 'ñ')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
<?php endif ?>
</tr>
<tr>
<th>Iconv Extension Loaded</th>
<?php if (extension_loaded('iconv')): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
<?php endif ?>
<tr>
<?php if (extension_loaded('mbstring')): ?>
<th>Mbstring Not Overloaded</th>
<?php if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING): $failed = TRUE ?>
<td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
<?php else: ?>
<td class="pass">Pass</td>
<?php endif ?>
</tr>
<?php endif ?>
</tr>
<tr>
<th>Determine URI</th>
<?php if (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF'])): ?>
<td class="pass">Pass</td>
<?php else: $failed = TRUE ?>
<td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code> or <code>$_SERVER['PHP_SELF']</code> is set.</td>
<?php endif ?>
</tr>
<tr class="results">
<th>Results</th>
<?php if ($failed === TRUE): ?>
<td class="fail">Your environment may not work with Kohana.</td>
<?php else: ?>
<td class="pass">Your environment passed all requirements. Remove or rename the <tt>install<?php echo EXT ?></tt> file now.</td>
<?php endif ?>
</table>

</div>

</body>
</html>