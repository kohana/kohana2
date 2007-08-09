<?php

define('EXT', preg_quote(pathinfo(__FILE__, PATHINFO_EXTENSION)));
define('GLOB_SEARCH', GLOB_BRACE+GLOB_NOSORT+GLOB_NOESCAPE);

$t1_start = microtime(TRUE);
for($n=10000;$n>0;$n--)
{
	foreach(array('modules', 'system') as $path)
	{
		foreach(scandir($path.'/libraries/') as $file)
		{
			if (preg_match('/'.EXT.'$/', $file))
			{
				$out[] = $file;
			}
		}
	}
}
$t1_stop = microtime(TRUE);
$t2_start = microtime(TRUE);
$out = array();

for($n=10000;$n>0;$n--)
{
	$out = glob("{modules,system}/libraries/*.php", GLOB_SEARCH);
}
$t2_stop = microtime(TRUE);

print "scandir took ".number_format($t1_stop-$t1_start, 6)." seconds\n";
print "glob took ".number_format($t2_stop-$t2_start, 6)." seconds\n";
