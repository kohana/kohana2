<?php

foreach(Kodoc::get_files() as $group => $files):

?>
<h4><?php echo ucfirst($group) ?></h4>
<ul>
<?php

foreach($files as $name => $drivers):

?>
<li><?php echo html::anchor('kodoc/'.$group.'/'.$name, $name) ?>
<?php

if (is_array($drivers)):

?>
<ul>
<?php

foreach($drivers as $driver):

	$file = ($name === $driver) ? $driver : $name.'_'.$driver;

?>
<li><?php echo html::anchor('kodoc/'.$group.'/drivers/'.$file, $driver) ?></li>
<?php

endforeach;

?>
</ul>
<?php

endif;

?>
</li>
<?php

endforeach;

?>
</ul>
<?php

endforeach;

?>