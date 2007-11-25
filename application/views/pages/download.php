<h2><span>Total Downloads: <?php echo $counter ?></span>Download Kohana</h2>

<p>You are downloading Kohana v<?php echo KOHANA_VERSION ?>. Please use <?php echo html::anchor('http://trac.kohanaphp.com/newticket', 'Trac tickets') ?> to report any bugs you experience. <span style="font-size:0.8em;font-style:italic;">Download files were last synced on <?php echo date('F jS, Y', $sync_date) ?>.</span></p>

<p><strong>This is the final release of version 2.0.</strong> Barring severe bugs, this will be the last release before version 2.1. There is no current release date for 2.1, but we expect it to take less time than version 2.0.</p>

<?php echo form::open('download', array('method' => 'get')) ?> 

<fieldset><span class="legend">Choose your download type:</span>
<ul>
<li><label><?php echo form::radio('group', 'minimal', ($this->validation->group == 'minimal')) ?> <strong>Minimal</strong></label> &ndash; Included libraries: Session, Validation. Included helpers: array, cookie, form, html, security, url, and validation.</li>
<li><label><?php echo form::radio('group', 'standard', ($this->validation->group == 'standard')) ?> <strong>Standard</strong></label> &ndash; Additional libraries: Archive, Database, Encryption, Pagination, and Profiler. Additional helpers: date, download, feed, inflector, and text.</li>
</ul>
</fieldset>

<fieldset><span class="legend">Include the following vendor tools in my download:</span>
<ul>
<?php

foreach($vendors as $name => $data):

	$key = strtolower($name);

?>
<li><label><?php echo form::checkbox('vendor['.$key.']', $name, isset($this->validation->vendor[$key])) ?> <strong><?php echo $name ?></strong></label> &ndash; <?php echo $data['description'] ?> <?php echo html::anchor($data['link'], 'More Information') ?>.</li>
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

<fieldset><span class="legend">Choose your download format:</span>
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