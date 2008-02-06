<h2><span>Total Downloads: <?php echo $counter ?></span>Download Kohana v<?php echo $release_version ?></h2>

<?php echo form::open('download', array('method' => 'get')) ?> 

<p style="font-size:1.2em">All Kohana libraries, helpers, and views are included in this download, but you may select your modules, vendor tools, and languages below.</p>

<p style="font-size:0.8em;font-style:italic;">This version was released on <?php echo date('F jS, Y', $release_date) ?>. It's codename is "<?php echo $release_codename ?>".</p>

<fieldset><span class="legend">Include the following modules in my download:</span>
<ul>
<?php

foreach($modules as $name => $description):

	$key = strtolower($name);

?>
<li><label><?php echo form::checkbox('modules['.$key.']', $name, isset($this->validation->modules[$key])) ?> <strong><?php echo $name ?></strong></label> &ndash; <?php echo $description ?></li>
<?php

endforeach;

?>
</ul>
</fieldset>

<fieldset><span class="legend">Include the following vendor tools in my download:</span>
<ul>
<?php

foreach($vendors as $name => $data):

	$key = strtolower($name);

?>
<li><label><?php echo form::checkbox('vendor['.$key.']', $name, isset($this->validation->vendor[$key])) ?> <strong><?php echo $name ?></strong></label> &ndash; <?php echo $data['description'] ?> <?php echo html::anchor($data['link'], 'More Information') ?></li>
<?php

endforeach;

?>
</ul>
</fieldset>

<fieldset><span class="legend">Include the following languages in my download:</span>
<?php echo ($this->validation->languages_error ? '<p class="error">You must select at least one language.</p>' : '') ?>
<ul>
<?php

foreach ($languages as $code => $lang):

?>
<li><label><?php echo form::checkbox('languages['.$code.']', $code, isset($this->validation->languages[$code])) ?> <?php echo $lang ?></label></li>
<?php

endforeach;

?>
</ul>
</fieldset>

<fieldset><span class="legend">Compress my download using:</span>
<?php echo $this->validation->format_error ?> 
<ul>
<?php

foreach ($formats as $ext => $format):

?>
<li><label><?php echo form::radio('format', $ext, ($this->validation->format == $ext)) ?> <?php echo $format ?></label></li>
<?php

endforeach;

?>
</ul>
</fieldset>

<?php echo form::button(array('type' => 'submit'), 'Download Kohana!') ?> 
<?php echo form::close() ?> 